<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLevel;
use App\Models\XpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * Feature Test UserQuestAndEmailTest
 *
 * Kiểm tra toàn diện chức năng:
 * - Gửi email xác thực tài khoản & Rate limit
 * - Nhận thưởng XP nhiệm vụ (register, verify_email)
 * - Đổi email qua mã OTP 6 số
 */
class UserQuestAndEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Notification::fake();
        RateLimiter::clear('send-verification:');
        RateLimiter::clear('claim-quest:');
        RateLimiter::clear('send-email-otp:');
        RateLimiter::clear('confirm-email-otp:');
    }

    /**
     * Helper tạo user.
     */
    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'status' => 'active',
            'is_deleted' => false,
        ], $overrides));
    }

    // ===== GỬI EMAIL XÁC THỰC TÀI KHOẢN =====

    /** @test */
    public function gui_email_xac_thuc_tai_khoan_thanh_cong(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);

        $response = $this->actingAs($user)->postJson('/api/v1/profile/send-verification');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Liên kết xác thực đã được gửi tới địa chỉ email của bạn.',
            ]);

        // Kiểm tra Notification đã được gửi
        Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\VerifyEmail::class
        );
    }

    /** @test */
    public function gui_email_xac_thuc_bi_rate_limit_khi_gui_lien_tiep(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);

        // Lần 1: Thành công
        $this->actingAs($user)->postJson('/api/v1/profile/send-verification')->assertOk();

        // Lần 2: Bị Rate Limit (429)
        $response = $this->actingAs($user)->postJson('/api/v1/profile/send-verification');
        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function gui_email_xac_thuc_tra_ve_loi_neu_tai_khoan_da_xac_thuc(): void
    {
        $user = $this->createUser(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson('/api/v1/profile/send-verification');

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Tài khoản của bạn đã được xác thực trước đó.',
            ]);
    }

    // ===== NHẬN THƯỞNG XP NHIỆM VỤ =====

    /** @test */
    public function claim_quest_register_thanh_cong(): void
    {
        $user = $this->createUser();
        // Setup UserLevel ban đầu
        UserLevel::create([
            'user_id' => $user->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/profile/quests/claim', [
            'quest_key' => 'register',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Nhận thưởng thành công! +50 XP',
            ]);

        // Kiểm tra XP tăng lên 60 (50 XP quest + 10 XP tự động checkin của middleware khi actingAs)
        $user->refresh();
        $this->assertEquals(60, $user->current_xp);

        // Kiểm tra đã lưu transaction
        $this->assertTrue(XpTransaction::where('user_id', $user->id)->where('source', 'register')->exists());
    }

    /** @test */
    public function claim_quest_register_truoc_do_roi_thi_khong_cho_claim_lai(): void
    {
        $user = $this->createUser();
        UserLevel::create([
            'user_id' => $user->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
        ]);

        // Lần 1: Claim thành công
        $this->actingAs($user)->postJson('/api/v1/profile/quests/claim', ['quest_key' => 'register'])->assertOk();

        // Lần 2: Trả về lỗi 400
        $response = $this->actingAs($user)->postJson('/api/v1/profile/quests/claim', ['quest_key' => 'register']);
        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn đã nhận thưởng cho nhiệm vụ này rồi.',
            ]);
    }

    /** @test */
    public function claim_quest_verify_email_khi_chua_xac_thuc_email_se_bi_loi(): void
    {
        $user = $this->createUser(['email_verified_at' => null]);
        UserLevel::create([
            'user_id' => $user->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/profile/quests/claim', [
            'quest_key' => 'verify_email',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Bạn cần xác thực địa chỉ email trước khi nhận thưởng.',
            ]);
    }

    /** @test */
    public function claim_quest_verify_email_khi_da_xac_thuc_email_thanh_cong(): void
    {
        $user = $this->createUser(['email_verified_at' => now()]);
        UserLevel::create([
            'user_id' => $user->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/profile/quests/claim', [
            'quest_key' => 'verify_email',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Nhận thưởng thành công! +30 XP',
            ]);

        // Kiểm tra XP tăng lên 40 (30 XP quest + 10 XP tự động checkin của middleware)
        $user->refresh();
        $this->assertEquals(40, $user->current_xp);
        $this->assertTrue(XpTransaction::where('user_id', $user->id)->where('source', 'verify_email')->exists());
    }

    // ===== ĐỔI EMAIL QUA OTP =====

    /** @test */
    public function gui_ma_otp_doi_email_moi_thanh_cong(): void
    {
        $user = $this->createUser(['email' => 'old@example.com']);

        $response = $this->actingAs($user)->postJson('/api/v1/profile/email/send-otp', [
            'email' => 'new@example.com',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Mã xác thực OTP đã được gửi tới địa chỉ email mới của bạn.',
            ]);

        // Kiểm tra OTP đã lưu trong cache
        $cached = Cache::get('change-email-otp:' . $user->id);
        $this->assertNotNull($cached);
        $this->assertEquals('new@example.com', $cached['email']);
        $this->assertEquals(6, strlen($cached['otp']));

        // Kiểm tra Mail ChangeEmailOtpMail đã được gửi đi tới đúng email nhận
        Mail::assertSent(\App\Mail\ChangeEmailOtpMail::class, function ($mail) {
            return $mail->hasTo('new@example.com');
        });
    }

    /** @test */
    public function gui_ma_otp_doi_email_bi_rate_limit_sau_1_lan_gui(): void
    {
        $user = $this->createUser(['email' => 'old@example.com']);

        // Lần 1: Thành công
        $this->actingAs($user)->postJson('/api/v1/profile/email/send-otp', ['email' => 'new@example.com'])->assertOk();

        // Lần 2: Bị Rate limit
        $response = $this->actingAs($user)->postJson('/api/v1/profile/email/send-otp', ['email' => 'new2@example.com']);
        $response->assertStatus(429);
    }

    /** @test */
    public function confirm_doi_email_loi_khi_sai_otp(): void
    {
        $user = $this->createUser(['email' => 'old@example.com']);

        // Tạo sẵn OTP trong cache
        Cache::put('change-email-otp:' . $user->id, [
            'email' => 'new@example.com',
            'otp' => '123456',
        ], 300);

        // Xác nhận sai OTP
        $response = $this->actingAs($user)->postJson('/api/v1/profile/email/confirm-change', [
            'email' => 'new@example.com',
            'otp' => '654321',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Mã xác thực không chính xác hoặc email không khớp.',
            ]);
    }

    /** @test */
    public function confirm_doi_email_thanh_cong_khi_dung_otp(): void
    {
        $user = $this->createUser(['email' => 'old@example.com', 'email_verified_at' => null]);

        // Tạo sẵn OTP trong cache
        Cache::put('change-email-otp:' . $user->id, [
            'email' => 'new@example.com',
            'otp' => '123456',
        ], 300);

        // Xác nhận đúng OTP
        $response = $this->actingAs($user)->postJson('/api/v1/profile/email/confirm-change', [
            'email' => 'new@example.com',
            'otp' => '123456',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Đổi địa chỉ email thành công và đã được xác thực.',
                'email' => 'new@example.com',
            ]);

        // Kiểm tra database cập nhật và email_verified_at
        $user->refresh();
        $this->assertEquals('new@example.com', $user->email);
        $this->assertNotNull($user->email_verified_at);

        // Cache phải bị xóa
        $this->assertNull(Cache::get('change-email-otp:' . $user->id));
    }
}
