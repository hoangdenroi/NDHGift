<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Kiểm tra route share lưu session, cookie và redirect thành công.
     */
    public function test_share_route_saves_cookie_and_session_and_redirects(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'TESTCODE',
        ]);

        $response = $this->get('/en/share?ref=TESTCODE');

        $response->assertRedirect('/en');
        $response->assertSessionHas('affiliate_ref', 'TESTCODE');
        $response->assertCookie('affiliate_ref', 'TESTCODE');
    }

    /**
     * Kiểm tra đăng ký qua link giới thiệu hợp lệ sẽ gán chính xác người giới thiệu.
     */
    public function test_registration_with_valid_referral_associates_referrer(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'VALIDREF',
        ]);

        $response = $this->withSession(['affiliate_ref' => 'VALIDREF'])
            ->withCookie('affiliate_ref', 'VALIDREF')
            ->post('/en/register', [
                'username' => 'newuser',
                'fullname' => 'New User',
                'email' => 'new@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('app.home.index', ['locale' => 'en'], false));

        $newUser = User::where('email', 'new@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals($referrer->id, $newUser->referred_by);
    }

    /**
     * Kiểm tra chặn tự giới thiệu bằng Cookie thiết bị (ref_tracker) và áp dụng phạt cả 2 bên.
     */
    public function test_registration_self_referral_via_cookie_blocked(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'SELFREF',
        ]);

        // Cấp 100 XP ban đầu cho referrer
        app(\App\Services\UserLevelService::class)->awardXp($referrer, 'register', 100);

        // Giả lập trình duyệt đã lưu cookie ref_tracker của chính người giới thiệu
        $response = $this->withSession(['affiliate_ref' => 'SELFREF'])
            ->withCookie('ref_tracker', 'SELFREF')
            ->post('/en/register', [
                'username' => 'cheatuser',
                'fullname' => 'Cheat User',
                'email' => 'cheat@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $newUser = User::where('email', 'cheat@example.com')->first();
        $this->assertNotNull($newUser);
        // 1. Không gán referred_by
        $this->assertNull($newUser->referred_by);

        // 2. Tài khoản clone mới tạo không nhận XP chào mừng (XP = 0 hoặc null)
        $newUserLevel = \App\Models\UserLevel::where('user_id', $newUser->id)->first();
        $this->assertTrue(!$newUserLevel || $newUserLevel->total_xp === 0);

        // 3. Tài khoản chính bị phạt trừ 50 XP (còn 100 - 50 = 50 XP)
        $referrerLevel = \App\Models\UserLevel::where('user_id', $referrer->id)->first();
        $this->assertEquals(50, $referrerLevel->total_xp);

        // 4. Có bản ghi giao dịch phạt cho tài khoản chính
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $referrer->id,
            'amount' => -50,
            'source' => 'referral_fraud_penalty'
        ]);

        // 5. Có bản ghi giao dịch 0 XP cho tài khoản clone mới
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $newUser->id,
            'amount' => 0,
            'source' => 'register_fraud_blocked'
        ]);
    }

    /**
     * Kiểm tra chặn tự giới thiệu bằng IP thông qua truy vấn cột metadata và áp dụng phạt cả 2 bên.
     */
    public function test_registration_self_referral_via_ip_blocked(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'IPREF',
            'metadata' => [
                'recent_ips' => ['1.2.3.4']
            ]
        ]);

        // Cấp 100 XP ban đầu cho referrer
        app(\App\Services\UserLevelService::class)->awardXp($referrer, 'register', 100);

        // Đăng ký từ chính IP 1.2.3.4 đó
        $response = $this->withSession(['affiliate_ref' => 'IPREF'])
            ->withServerVariables(['REMOTE_ADDR' => '1.2.3.4'])
            ->post('/en/register', [
                'username' => 'cheatipuser',
                'fullname' => 'Cheat IP User',
                'email' => 'cheatip@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

        $newUser = User::where('email', 'cheatip@example.com')->first();
        $this->assertNotNull($newUser);
        // 1. Không gán referred_by
        $this->assertNull($newUser->referred_by);

        // 2. Tài khoản clone mới tạo không nhận XP chào mừng
        $newUserLevel = \App\Models\UserLevel::where('user_id', $newUser->id)->first();
        $this->assertTrue(!$newUserLevel || $newUserLevel->total_xp === 0);

        // 3. Tài khoản chính bị phạt trừ 50 XP (còn 100 - 50 = 50 XP)
        $referrerLevel = \App\Models\UserLevel::where('user_id', $referrer->id)->first();
        $this->assertEquals(50, $referrerLevel->total_xp);

        // 4. Có bản ghi giao dịch phạt cho tài khoản chính
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $referrer->id,
            'amount' => -50,
            'source' => 'referral_fraud_penalty'
        ]);

        // 5. Có bản ghi giao dịch 0 XP cho tài khoản clone mới
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $newUser->id,
            'amount' => 0,
            'source' => 'register_fraud_blocked'
        ]);
    }

    /**
     * Kiểm tra sự kiện Login tự động lưu IP hiện tại của user vào metadata['recent_ips'] và set cookie khi đăng nhập qua HTTP.
     */
    public function test_user_login_saves_ip_to_metadata_and_sets_cookie(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
            'metadata' => []
        ]);

        $response = $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])
            ->post('/en/login', [
                'email' => $user->email,
                'password' => 'password',
            ]);

        $this->assertAuthenticated();

        $user->refresh();
        $metadata = $user->metadata;

        $this->assertIsArray($metadata);
        $this->assertArrayHasKey('recent_ips', $metadata);
        $this->assertContains('9.9.9.9', $metadata['recent_ips']);
        
        // Xác minh cookie ref_tracker được gán đúng
        $response->assertCookie('ref_tracker', $user->affiliate_code);
    }
}
