<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
     * Kiểm tra chặn tự giới thiệu bằng Cookie thiết bị (ref_tracker).
     */
    public function test_registration_self_referral_via_cookie_blocked(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'SELFREF',
        ]);

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
        // Không được gán referred_by do trùng thiết bị
        $this->assertNull($newUser->referred_by);
    }

    /**
     * Kiểm tra chặn tự giới thiệu bằng IP thông qua truy vấn bảng sessions.
     */
    public function test_registration_self_referral_via_ip_blocked(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'IPREF',
        ]);

        // Ghi nhận IP hoạt động của người giới thiệu trong bảng sessions
        DB::table('sessions')->insert([
            'id' => 'referrer_session_id',
            'user_id' => $referrer->id,
            'ip_address' => '1.2.3.4',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'payload',
            'last_activity' => time(),
        ]);

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
        // Không được gán referred_by do trùng IP hoạt động của referrer
        $this->assertNull($newUser->referred_by);
    }
}
