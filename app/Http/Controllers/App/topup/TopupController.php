<?php

declare(strict_types=1);

namespace App\Http\Controllers\App\topup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Topup\CreateTopupRequest;
use App\Http\Requests\Topup\SepayWebhookRequest;
use App\Models\Transaction;
use App\Services\TopupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * TopupController — Skinny Controller cho module nạp tiền.
 *
 * Mọi business logic đã delegate sang TopupService.
 * Controller chỉ nhận request, gọi service và trả response.
 */
class TopupController extends Controller
{
    public function __construct(
        private readonly TopupService $topupService
    ) {}

    /**
     * Trang nạp tiền — truyền danh sách giao dịch PENDING vào view.
     */
    public function index(): View
    {
        $pendingTransactions = $this->topupService->getPendingTransactions(Auth::user());

        return view('components.pages.app.topup.topup-index', [
            'pendingTransactions' => $pendingTransactions,
        ]);
    }

    /**
     * API: Tạo giao dịch nạp tiền PENDING + sinh QR thanh toán.
     *
     * Chỉ nhận amount từ client — server tự tính toán mọi thứ.
     */
    public function createTopup(CreateTopupRequest $request): JsonResponse
    {
        try {
            $result = $this->topupService->createPendingTransaction(
                Auth::user(),
                (int) $request->validated('amount')
            );

            return response()->json([
                'success' => true,
                'qr_url' => $result['qr_url'],
                'description' => $result['description'],
                'transaction' => [
                    'id' => $result['transaction']->id,
                    'transaction_no' => $result['transaction']->transaction_no,
                    'payment_code' => $result['transaction']->payment_code,
                    'amount' => $result['transaction']->amount,
                    'status' => $result['transaction']->status,
                    'expires_at' => $result['transaction']->expires_at?->toISOString(),
                    'created_at' => $result['transaction']->created_at?->toISOString(),
                ],
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API: Hủy giao dịch PENDING — kiểm tra IDOR trong Service.
     */
    public function cancelTopup(Transaction $transaction): JsonResponse
    {
        try {
            $this->topupService->cancelTransaction(Auth::user(), $transaction);

            return response()->json([
                'success' => true,
                'message' => 'Đã hủy giao dịch thành công.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API: Lấy danh sách giao dịch PENDING chưa hết hạn.
     */
    public function pendingTransactions(): JsonResponse
    {
        $transactions = $this->topupService->getPendingTransactions(Auth::user());

        return response()->json([
            'success' => true,
            'data' => $transactions->map(fn (Transaction $tx) => [
                'id' => $tx->id,
                'transaction_no' => $tx->transaction_no,
                'payment_code' => $tx->payment_code,
                'amount' => $tx->amount,
                'status' => $tx->status,
                'order_info' => $tx->order_info,
                'expires_at' => $tx->expires_at?->toISOString(),
                'created_at' => $tx->created_at?->toISOString(),
            ]),
        ]);
    }

    /**
     * API: Lấy lịch sử nạp tiền (tất cả trạng thái) có phân trang.
     */
    public function history(): JsonResponse
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Webhook từ SePay — xác thực API key rồi delegate sang Service.
     *
     * Giữ logic xác thực API key tại Controller vì đây là HTTP-level concern,
     * không thuộc business logic thuần túy.
     */
    public function sepayHook(SepayWebhookRequest $request): JsonResponse
    {
        // Xác thực API key từ header
        if (!$this->validateSepayApiKey($request)) {
            Log::warning('SePay Webhook: API key không hợp lệ', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid API Key.',
            ], 401);
        }

        try {
            $result = $this->topupService->processSepayWebhook($request->validated());

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('SePay Webhook Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xử lý webhook.',
            ], 500);
        }
    }

    /**
     * Xác thực API key từ header của SePay webhook.
     *
     * Hỗ trợ nhiều định dạng header (Authorization, apikey) để tương thích
     * với cấu hình đa dạng từ phía SePay.
     */
    private function validateSepayApiKey(Request $request): bool
    {
        $expectedApiKey = config('payment.sepay.api_key');
        $authHeader = $request->header('Authorization');
        $apiKeyHeader = $request->header('apikey');

        if ($authHeader && ltrim($authHeader) === 'Apikey ' . $expectedApiKey) {
            return true;
        }

        if ($apiKeyHeader) {
            // Trường hợp gửi trực tiếp giá trị api key
            if ($apiKeyHeader === $expectedApiKey) {
                return true;
            }
            // Trường hợp có prefix "Apikey "
            if (current(explode(' ', $apiKeyHeader)) === $expectedApiKey) {
                return true;
            }
            // Trường hợp "Apikey <value>" — tách prefix ra
            $stripped = ltrim(str_replace('Apikey ', '', $apiKeyHeader));
            if (current(explode(' ', $stripped)) === $expectedApiKey) {
                return true;
            }
        }

        return false;
    }
}
