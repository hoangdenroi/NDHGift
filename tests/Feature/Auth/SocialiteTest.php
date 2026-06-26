<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\XpTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialiteTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test chuyển hướng sang OAuth Provider.
     */
    public function test_socialite_redirect_works(): void
    {
        $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('redirect')->andReturn(redirect('https://google.com/oauth-mock'));

        Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

        $response = $this->get('/auth/google');

        $response->assertRedirect('https://google.com/oauth-mock');
    }

    /**
     * Test chuyển hướng với nhà cung cấp không được hỗ trợ -> 404.
     */
    public function test_socialite_redirect_invalid_provider_returns_404(): void
    {
        $response = $this->get('/auth/github');

        $response->assertStatus(404);
    }

    /**
     * Test callback với tài khoản đã tồn tại trong DB.
     */
    public function test_socialite_callback_logs_in_existing_user(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'google_id' => null,
        ]);

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn('existing@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Existing User');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
        $socialUser->shouldReceive('getId')->andReturn('google-id-123456');

        $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

        $response = $this->get('/auth/google/callback');

        $this->assertAuthenticatedAs($user);
        $this->assertEquals('google-id-123456', $user->fresh()->google_id);
        $response->assertRedirect(route('app.home.index', ['locale' => app()->getLocale()], false));
    }

    /**
     * Test callback tạo người dùng mới khi chưa tồn tại email trong DB.
     */
    public function test_socialite_callback_creates_new_user_and_logs_in(): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn('newuser@example.com');
        $socialUser->shouldReceive('getName')->andReturn('New Social User');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://avatar.url/avatar.png');
        $socialUser->shouldReceive('getId')->andReturn('fb-id-78910');

        $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($providerMock);

        $response = $this->get('/auth/facebook/callback');

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('newuser', $user->username);
        $this->assertEquals('New Social User', $user->fullname);
        $this->assertEquals('fb-id-78910', $user->facebook_id);
        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals('https://avatar.url/avatar.png', $user->avatar_url);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('app.home.index', ['locale' => app()->getLocale()], false));
    }

    /**
     * Test callback tạo người dùng mới có áp dụng affiliate giới thiệu.
     */
    public function test_socialite_callback_creates_new_user_with_affiliate(): void
    {
        $referrer = User::factory()->create([
            'affiliate_code' => 'REFER123',
        ]);

        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn('referred@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Referred User');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
        $socialUser->shouldReceive('getId')->andReturn('google-id-ref');

        $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

        // Giả lập session chứa mã affiliate
        $response = $this->withSession(['affiliate_ref' => 'REFER123'])
            ->get('/auth/google/callback');

        $user = User::where('email', 'referred@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($referrer->id, $user->referred_by);

        // Kiểm tra đã cộng XP chào mừng cho user mới
        $xpTx = XpTransaction::where('user_id', $user->id)->first();
        $this->assertNotNull($xpTx);
        $this->assertEquals('register', $xpTx->source);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('app.home.index', ['locale' => app()->getLocale()], false));
    }

    /**
     * Test callback tự giới thiệu gian lận qua Social login.
     */
    public function test_socialite_callback_detects_self_referral_fraud(): void
    {
        // Tạo một user đã tồn tại, và cũng là referrer
        $referrer = User::factory()->create([
            'email' => 'referrer@example.com',
            'affiliate_code' => 'REFER123',
        ]);

        // Mock Socialite trả về email trùng với referrer (tự đăng ký qua link của mình)
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn('referrer@example.com');
        $socialUser->shouldReceive('getName')->andReturn('Referrer User');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
        $socialUser->shouldReceive('getId')->andReturn('google-id-self');

        $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

        $response = $this->withSession(['affiliate_ref' => 'REFER123'])
            ->get('/auth/google/callback');

        $user = User::where('email', 'referrer@example.com')->first();
        $this->assertNotNull($user);
        
        // Không tự liên kết giới thiệu với chính mình
        $this->assertNull($user->referred_by);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test callback thất bại khi OAuth Provider không trả về email.
     */
    public function test_socialite_callback_fails_when_email_missing(): void
    {
        $socialUser = Mockery::mock(SocialiteUser::class);
        $socialUser->shouldReceive('getEmail')->andReturn(null);
        $socialUser->shouldReceive('getName')->andReturn('No Email User');
        $socialUser->shouldReceive('getAvatar')->andReturn('https://avatar.url');
        $socialUser->shouldReceive('getId')->andReturn('google-id-noemail');

        $providerMock = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock->shouldReceive('stateless')->andReturnSelf();
        $providerMock->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($providerMock);

        $response = $this->get('/auth/google/callback');

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test callback chỉ cập nhật avatar nếu có sự thay đổi từ MXH.
     */
    public function test_socialite_callback_updates_avatar_only_when_different(): void
    {
        // 1. Trường hợp avatar giống nhau -> Không thay đổi
        $user = User::factory()->create([
            'email' => 'avatar-test@example.com',
            'avatar_url' => 'https://existing-avatar.url',
        ]);

        $socialUser1 = Mockery::mock(SocialiteUser::class);
        $socialUser1->shouldReceive('getEmail')->andReturn('avatar-test@example.com');
        $socialUser1->shouldReceive('getName')->andReturn('Avatar Test');
        $socialUser1->shouldReceive('getAvatar')->andReturn('https://existing-avatar.url');
        $socialUser1->shouldReceive('getId')->andReturn('google-avatar-1');

        $providerMock1 = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock1->shouldReceive('stateless')->andReturnSelf();
        $providerMock1->shouldReceive('user')->andReturn($socialUser1);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($providerMock1);

        $response1 = $this->get('/auth/google/callback');
        $this->assertEquals('https://existing-avatar.url', $user->fresh()->avatar_url);

        // 2. Trường hợp avatar khác nhau -> Cập nhật avatar mới
        $socialUser2 = Mockery::mock(SocialiteUser::class);
        $socialUser2->shouldReceive('getEmail')->andReturn('avatar-test@example.com');
        $socialUser2->shouldReceive('getName')->andReturn('Avatar Test');
        $socialUser2->shouldReceive('getAvatar')->andReturn('https://new-avatar.url');
        $socialUser2->shouldReceive('getId')->andReturn('google-avatar-1');

        $providerMock2 = Mockery::mock(\Laravel\Socialite\Two\AbstractProvider::class);
        $providerMock2->shouldReceive('stateless')->andReturnSelf();
        $providerMock2->shouldReceive('user')->andReturn($socialUser2);

        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($providerMock2);

        $response2 = $this->get('/auth/google/callback');
        $this->assertEquals('https://new-avatar.url', $user->fresh()->avatar_url);
    }
}
