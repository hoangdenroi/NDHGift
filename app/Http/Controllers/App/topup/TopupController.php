<?php

namespace App\Http\Controllers\App\topup;

use App\Events\BalanceUpdated;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TopupController extends Controller
{
    public function index()
    {
        return view('components.pages.app.topup.topup-index');
    }

    /**
     * Lấy thông tin & link mã QR thanh toán
     */
    public function getPaymentQr(Request $request)
    {
        if (! Auth::check()) {
            Log::warning('Nạp tiền thất bại: Nỗ lực tạo mã QR khi chưa đăng nhập', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập hệ thống để thực hiện nạp tiền!',
            ], 401);
        }

        // Require amount and must be integer >= 20000
        $request->validate([
            'amount' => 'required|integer|min:20000|max:100000000',
        ]);

        $amount = $request->input('amount');

        // Cấu hình ngân hàng (Bạn có thể chuyển các giá trị này sang .env)
        $bankId = config('payment.vietqr.bin', '970423');
        $accountNo = config('payment.vietqr.account', '10003179213');
        $accountName = config('payment.vietqr.name', 'NGUYEN DUC HOANG');
        $template = config('payment.vietqr.template', 'compact');
        $prefix = config('payment.vietqr.prefix', 'SEVQR ');

        // Nội dung CK tuỳ theo User đang login
        $description = $prefix.Auth::user()->unitcode;

        // Sinh link QR từ img.vietqr.io
        $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$template}.png?amount={$amount}&addInfo=".urlencode($description).'&accountName='.urlencode($accountName);

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    /**
     * Lấy lịch sử nạp tiền của user hiện tại
     */
    public function history(Request $request)
    {
        $unitcode = Auth::user()->unitcode;

        $transactions = Transaction::where(function ($query) use ($unitcode) {
            $query->where('user_id', Auth::id())
                ->orWhere('order_info', 'like', "%{$unitcode}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    public function sepayHook(Request $request)
    {
        // Sử dụng config thay thế cho env trực tiếp
        $expectedApiKey = config('payment.sepay.api_key');
        $authHeader = $request->header('Authorization');
        $apiKeyHeader = $request->header('apikey');

        // Kiểm tra xem header nào được gửi (Authorization hoặc apikey trực tiếp)
        $isValid = false;
        if ($authHeader && ltrim($authHeader) === 'Apikey '.$expectedApiKey) {
            $isValid = true;
        } elseif ($apiKeyHeader && current(explode(' ', $apiKeyHeader)) === $expectedApiKey) { // Trường hợp chỉ truyền giá trị api key
            $isValid = true;
        } elseif ($apiKeyHeader && current(explode(' ', ltrim(str_replace('Apikey ', '', $apiKeyHeader)))) === $expectedApiKey) {
            $isValid = true;
        } elseif ($apiKeyHeader === $expectedApiKey) {
            $isValid = true;
        }

        if (! $isValid) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Invalid API Key.',
            ], 401);
        }

        // Validate dữ liệu từ SePay webhook dựa theo DTO
        $validated = $request->validate([
            'id' => 'required|integer', // ID giao dịch trên SePay
            'gateway' => 'required|string', // Brand name của ngân hàng
            'transactionDate' => 'required|string', // Thời gian xảy ra giao dịch phía ngân hàng
            'accountNumber' => 'required|string', // Số tài khoản ngân hàng
            'code' => 'nullable', // Mã code thanh toán
            'content' => 'required|string', // Nội dung chuyển khoản
            'transferType' => 'required|string|in:in,out', // Loại giao dịch
            'transferAmount' => 'required|integer|min:1', // Số tiền giao dịch
            'accumulated' => 'required|integer', // Số dư tài khoản (lũy kế)
            'subAccount' => 'nullable', // Tài khoản ngân hàng phụ
            'referenceCode' => 'nullable|string', // Mã tham chiếu của tin nhắn sms
            'description' => 'nullable|string', // Toàn bộ nội dung tin nhắn sms
        ]);

        try {
            DB::beginTransaction();

            $gatewayTransactionId = (string) $validated['id'];

            // Kiểm tra xem transaction này đã được xử lý chưa (dựa vào id của SePay)
            $existingTx = Transaction::where('gateway_transaction_id', $gatewayTransactionId)->first();

            if ($existingTx) {
                DB::rollBack();

                return response()->json([
                    'success' => true,
                    'message' => 'Giao dịch đã được thực hiện trước đó.',
                ]);
            }

            $userId = null;
            $prefix = config('payment.vietqr.prefix', 'SEVQR ');
            $transferContent = strtoupper(trim($validated['content']));
            $txStatus = 'SUCCESS';
            $failureReason = null;

            // Nếu là tiền vào và đúng cú pháp
            $prefixPattern = preg_quote(strtoupper(trim($prefix)), '/');
            if ($validated['transferType'] === 'in' && preg_match('/'.$prefixPattern.'\s+([A-Z0-9]+)/', $transferContent, $matches)) {
                $unitcode = trim($matches[1]);
                $user = User::where('unitcode', $unitcode)->first();

                if ($user) {
                    $userId = $user->id;
                    $oldBalance = $user->balance ?? 0;
                    // Cộng tiền cho user
                    $user->balance = $oldBalance + $validated['transferAmount'];
                    $user->save();

                    // Ghi log nạp tiền
                    AuditLogService::log(
                        'topup_bank_transfer',
                        $user,
                        ['balance' => (float) $oldBalance],
                        [
                            'balance' => (float) $user->balance,
                            'amount' => (float) $validated['transferAmount'],
                            'gateway' => $validated['gateway'],
                            'transaction_no' => $gatewayTransactionId,
                        ],
                        $userId
                    );

                    // Tạo thông báo lưu DB
                    Notification::create([
                        'user_id' => $userId,
                        'scope' => 'user',
                        'title' => 'Nạp tiền thành công',
                        'message' => 'Bạn vừa nạp thành công '.number_format($validated['transferAmount']).'đ vào tài khoản.',
                        'type' => 'success',
                        'data' => [
                            'action' => 'update_balance',
                        ],
                    ]);

                    // Phát sự kiện WebSocket realtime để Frontend tự cập nhật số dư
                    event(new BalanceUpdated(
                        userId: $userId,
                        newBalance: (float) $user->balance,
                        amount: (float) $validated['transferAmount'],
                        message: 'Nạp thành công '.number_format($validated['transferAmount']).'đ',
                    ));
                } else {
                    $txStatus = 'FAILED';
                    $failureReason = 'Không tìm thấy user với mã unitcode: '.$unitcode;
                }
            } else {
                $txStatus = 'FAILED';
                $failureReason = 'Sai cú pháp nạp tiền hoặc không phải giao dịch chuyển vào (in)';
            }

            // Xử lý metadata json string nếu có
            $metadataInput = $validated['metadata'] ?? null;
            $metadata = is_string($metadataInput) ? json_decode($metadataInput, true) : $metadataInput;
            if (! $metadata) {
                $metadata = ['raw_sepay_data' => $validated];
            }

            Transaction::create([
                'user_id' => $userId,
                'user_identifier' => null,
                'amount' => $validated['transferAmount'],
                'fee' => 0, // SePay không trả về fee, gán mặc định 0
                'net_amount' => $validated['transferAmount'],
                'currency' => 'VND',
                'transaction_no' => 'TXN'.Str::ulid()->toString(),
                'gateway_transaction_id' => $gatewayTransactionId,
                'bank_code' => $validated['gateway'],
                'status' => $txStatus, // SUCCESS hoặc FAILED
                'payment_method' => 'SEPAY',
                'response_code' => $txStatus === 'SUCCESS' ? '00' : '99',
                'order_info' => $validated['content'].' - '.($validated['description'] ?? ''),
                'pay_date' => Carbon::parse($validated['transactionDate']),
                'account_number' => $validated['subAccount'] ?? $validated['accountNumber'],
                'metadata' => $metadata,
                'failure_reason' => $failureReason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Giao dịch thành công',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SePay Webhook Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xử lý webhook',
            ], 500);
        }
    }
}
