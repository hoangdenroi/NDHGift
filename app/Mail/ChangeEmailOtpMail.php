<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ChangeEmailOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otp;

    /**
     * Create a new message instance.
     */
    public function __construct(string $otp)
    {
        $this->otp = $otp;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác thực đổi email - NDHGift',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 12px; background-color: #ffffff;'>
                    <h2 style='color: #f97316; text-align: center;'>Mã Xác Thực Đổi Email</h2>
                    <p>Xin chào,</p>
                    <p>Bạn đang thực hiện thay đổi địa chỉ email trên hệ thống NDHGift. Vui lòng sử dụng mã xác thực dưới đây để hoàn tất quá trình:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='display: inline-block; font-size: 32px; font-weight: bold; color: #f97316; letter-spacing: 5px; padding: 10px 20px; background-color: #fff7ed; border: 2px dashed #ffedd5; border-radius: 8px;'>{$this->otp}</span>
                    </div>
                    <p style='color: #ef4444; font-weight: bold;'>Lưu ý: Mã xác thực có hiệu lực trong vòng 5 phút và chỉ sử dụng một lần duy nhất. Tuyệt đối không cung cấp mã này cho người khác.</p>
                    <hr style='border: 0; border-top: 1px solid #e2e8f0; margin: 20px 0;' />
                    <p style='font-size: 12px; color: #64748b; text-align: center;'>Đây là email tự động, vui lòng không phản hồi email này.</p>
                </div>
            ",
        );
    }
}
