<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Service ghi log thao tác nhạy cảm trong hệ thống.
 *
 * Tự động:
 * - Truncate chuỗi dài > 200 ký tự để tránh DB phình to
 * - Ẩn các field nhạy cảm (password, token) nếu vô tình truyền vào
 * - Ghi IP + User Agent cho truy vết bảo mật
 */
class AuditLogService
{
    /**
     * Ghi log thao tác.
     *
     * @param  string      $action     Hành động (vd: 'login', 'purchased_item', 'balance_deducted')
     * @param  Model|null  $model      Model liên quan (vd: $order, $user)
     * @param  array|null  $oldValues  Giá trị cũ (trước khi thay đổi)
     * @param  array|null  $newValues  Giá trị mới (sau khi thay đổi)
     * @param  int|null    $userId     ID người thực hiện (mặc định Auth::id())
     */
    public static function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): ?AuditLog {
        try {
            // request() có thể không tồn tại trong context CLI/Job
            $ipAddress = request()?->ip();
            $userAgent = request()?->userAgent();

            // Làm sạch dữ liệu trước khi lưu
            $oldValues = self::sanitizeLogData($oldValues);
            $newValues = self::sanitizeLogData($newValues);

            return AuditLog::create([
                'user_id' => $userId ?: Auth::id(),
                'action' => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model?->id,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } catch (\Throwable $e) {
            // Audit log không được phép làm crash luồng chính
            Log::error('AuditLogService::log() thất bại', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Làm sạch dữ liệu log:
     * - Truncate chuỗi dài > maxLength để tránh DB phình to
     * - Ẩn các field nhạy cảm (password, token, secret)
     */
    protected static function sanitizeLogData(?array $data, int $maxLength = 200): ?array
    {
        if (empty($data)) {
            return $data;
        }

        // Danh sách field nhạy cảm cần ẩn — tuyệt đối không lưu vào DB
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'api_key'];

        foreach ($data as $key => $value) {
            // Ẩn field nhạy cảm
            if (in_array(strtolower((string) $key), $sensitiveFields, true)) {
                $data[$key] = '********';
                continue;
            }

            if (is_string($value) && mb_strlen($value) > $maxLength) {
                $data[$key] = mb_substr($value, 0, $maxLength) . '... [truncated]';
            } elseif (is_array($value)) {
                // Đệ quy cho mảng lồng nhau
                $data[$key] = self::sanitizeLogData($value, $maxLength);
            }
        }

        return $data;
    }
}
