<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\UserReferred;
use App\Events\UserTopupSucceeded;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserLevel;
use App\Models\XpTransaction;
use App\Services\UserLevelService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class UserLevelSystemTest
 *
 * Feature test kiểm kiểm thử hệ thống User Level, XP Rules, Affiliate và Decay.
 */
class UserLevelSystemTest extends TestCase
{
    use RefreshDatabase;

    private UserLevelService $levelService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->levelService = app(UserLevelService::class);
    }

    /**
     * Test case: Đăng ký thành viên mới được cộng XP chào mừng.
     */
    public function test_new_user_registration_awards_welcome_xp(): void
    {
        $response = $this->post('/vi/register', [
            'fullname' => 'Nguyen Van Test',
            'email' => 'vantest@ndhgift.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'vantest@ndhgift.com')->first();
        $this->assertNotNull($user);

        // Kiểm tra XP chào mừng (mặc định trong config là 50 XP)
        $userLevel = UserLevel::where('user_id', $user->id)->first();
        $this->assertNotNull($userLevel);
        $this->assertEquals(50, $userLevel->total_xp);
        $this->assertEquals('bronze', $userLevel->tier);

        // Kiểm tra audit log XP transaction
        $this->assertDatabaseHas('xp_transactions', [
            'user_id' => $user->id,
            'source' => 'register',
            'amount' => 50,
        ]);
    }

    /**
     * Test case: Đăng ký qua link giới thiệu (Affiliate) được cộng XP cho cả 2.
     */
    public function test_affiliate_signup_awards_xp_to_both_users(): void
    {
        // Tạo referrer trước
        $referrer = User::factory()->create([
            'username' => 'referrer',
            'email' => 'referrer@ndhgift.com',
        ]);

        // Đảm bảo referrer có code và bản ghi UserLevel
        $this->assertNotNull($referrer->affiliate_code);
        UserLevel::create([
            'user_id' => $referrer->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
            'last_xp_earned_at' => now(),
        ]);

        // Mock session để lưu affiliate_ref
        session(['affiliate_ref' => $referrer->affiliate_code]);

        // Thực hiện đăng ký
        $response = $this->post('/vi/register', [
            'fullname' => 'Referee F1',
            'email' => 'f1@ndhgift.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect();

        $referee = User::where('email', 'f1@ndhgift.com')->first();
        $this->assertNotNull($referee);

        // Kiểm tra referee được link tới referrer
        $this->assertEquals($referrer->id, $referee->referred_by);

        // Làm mới dữ liệu từ Database
        $referee->refresh();
        $referrer->refresh();

        // Kiểm tra referee được cộng 50 XP register + 50 XP referee = 100 XP
        $refereeLevel = UserLevel::where('user_id', $referee->id)->first();
        $this->assertEquals(100, $refereeLevel->total_xp);

        // Kiểm tra referrer được cộng 100 XP giới thiệu
        $referrerLevel = UserLevel::where('user_id', $referrer->id)->first();
        $this->assertEquals(100, $referrerLevel->total_xp);
    }

    /**
     * Test case: F1 nạp tiền lần đầu, người giới thiệu nhận 10% hoa hồng và XP.
     */
    public function test_f1_first_deposit_awards_commission_and_xp_to_referrer(): void
    {
        // 1. Tạo 2 users có liên kết giới thiệu
        $referrer = User::factory()->create([
            'username' => 'referrer2',
            'email' => 'referrer2@ndhgift.com',
            'balance' => 0,
        ]);

        $referee = User::factory()->create([
            'username' => 'referee2',
            'email' => 'referee2@ndhgift.com',
            'referred_by' => $referrer->id,
            'balance' => 0,
        ]);

        // Tạo bản ghi UserLevel ban đầu
        UserLevel::create([
            'user_id' => $referrer->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
            'last_xp_earned_at' => now(),
        ]);

        UserLevel::create([
            'user_id' => $referee->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'is_frozen' => false,
            'last_xp_earned_at' => now(),
        ]);

        // 2. Giả lập F1 nạp tiền thành công (Topup SUCCESS)
        $transaction = Transaction::create([
            'user_id' => $referee->id,
            'amount' => 200000, // Nạp 200.000đ
            'fee' => 0,
            'net_amount' => 200000,
            'currency' => 'VND',
            'transaction_no' => 'TXN' . Str::ulid()->toString(),
            'status' => 'SUCCESS',
            'payment_method' => 'SEPAY',
            'pay_date' => now(),
        ]);

        // Phát sự kiện nạp tiền
        event(new UserTopupSucceeded($referee, $transaction));

        // Refresh model để lấy số liệu mới
        $referee->refresh();
        $referrer->refresh();

        // 3. Kiểm tra Referee được cộng XP từ việc nạp tiền (1 XP/1,000đ => nạp 200.000đ nhận 200 XP)
        $refereeLevel = UserLevel::where('user_id', $referee->id)->first();
        $this->assertEquals(200, $refereeLevel->total_xp);

        // 4. Kiểm tra Referrer nhận được 10% hoa hồng (10% của 200.000đ = 20.000đ)
        $this->assertEquals(20000, $referrer->balance);

        // Kiểm tra Referrer được tạo Transaction hoa hồng affiliate
        $this->assertDatabaseHas('transactions', [
            'user_id' => $referrer->id,
            'amount' => 20000,
            'payment_method' => 'AFFILIATE',
            'status' => 'SUCCESS',
        ]);

        // Kiểm tra Referrer nhận thêm 100 XP nạp đầu của F1
        $referrerLevel = UserLevel::where('user_id', $referrer->id)->first();
        $this->assertEquals(100, $referrerLevel->total_xp);

        // A. Giả lập giao dịch nạp tiền thứ hai của F1 để kiểm tra Edge Case (chỉ thưởng nạp tiền lần đầu)
        $transaction2 = Transaction::create([
            'user_id' => $referee->id,
            'amount' => 100000,
            'fee' => 0,
            'net_amount' => 100000,
            'currency' => 'VND',
            'transaction_no' => 'TXN' . Str::ulid()->toString(),
            'status' => 'SUCCESS',
            'payment_method' => 'SEPAY',
            'pay_date' => now(),
        ]);

        event(new UserTopupSucceeded($referee, $transaction2));

        // Refresh lại model
        $referee->refresh();
        $referrer->refresh();

        // Số dư của Referrer vẫn giữ nguyên (không được nhận hoa hồng lần 2)
        $this->assertEquals(20000, $referrer->balance);
        
        // XP của Referrer vẫn là 100 (không được nhận XP nạp đầu lần 2)
        $this->assertEquals(100, $referrerLevel->fresh()->total_xp);

        // Tuy nhiên, Referee vẫn được cộng thêm XP nạp tiền bình thường (200 + 100 = 300 XP)
        $refereeLevel = UserLevel::where('user_id', $referee->id)->first();
        $this->assertEquals(300, $refereeLevel->total_xp);
    }

    /**
     * Test case: Kiểm tra cơ chế đóng băng (Decay) và phục hồi khi nhận XP mới.
     */
    public function test_decay_command_freezes_inactive_users_and_award_xp_reactivates(): void
    {
        // 1. Tạo user ở cấp độ Gold hoạt động cách đây 61 ngày
        $user = User::factory()->create();
        $userLevel = UserLevel::create([
            'user_id' => $user->id,
            'total_xp' => 2500, // Cấp Gold (ngưỡng 2000 XP)
            'tier' => 'gold',
            'is_frozen' => false,
            'last_xp_earned_at' => Carbon::now()->subDays(61), // Quá 60 ngày không hoạt động
            'tier_achieved_at' => Carbon::now()->subDays(61),
        ]);

        // Chạy command decay
        Artisan::call('app:decay-user-levels');

        // Refresh để xóa cache relations của model user
        $user->refresh();
        $userLevel->refresh();

        // Kiểm tra userLevel bị đóng băng và hạ xuống Silver (Gold -> Silver)
        $this->assertTrue($userLevel->is_frozen);
        $this->assertEquals('silver', $userLevel->tier);

        // Kiểm tra discount và ad percent khi bị đóng băng (đều phải bị reset về 0/100%)
        $this->assertEquals(0, $this->levelService->getDiscountForUser($user));
        $this->assertEquals(100, $this->levelService->getAdPercentForUser($user));

        // 2. Giả lập nhận XP bất kỳ (Ví dụ: tạo trang quà tặng được tặng 20 XP)
        $this->levelService->awardXp($user, 'gift_create', 20);

        // Refresh lại model
        $user->refresh();
        $userLevel->refresh();

        // Kiểm tra userLevel đã được kích hoạt lại (mở băng)
        $this->assertFalse($userLevel->is_frozen);

        // Quyền lợi của Gold (10% discount, 40% ad percent) được khôi phục do tổng XP là 2520 (Gold)
        $this->assertEquals(10, $this->levelService->getDiscountForUser($user));
        $this->assertEquals(40, $this->levelService->getAdPercentForUser($user));
    }

    /**
     * Test case: Kiểm tra hệ thống điểm danh hàng ngày chuỗi 7 ngày và thưởng XP.
     */
    public function test_daily_checkin_streak_and_awards(): void
    {
        $user = User::factory()->create();
        
        // 1. Điểm danh lần đầu (Ngày 1)
        $result = $this->levelService->checkin($user);
        $this->assertNotNull($result);
        $this->assertEquals(1, $result['streak']);
        $this->assertEquals(10, $result['xp_awarded']);
        $this->assertEquals(0, $result['bonus_awarded']);
        
        // Đảm bảo total_xp cập nhật
        $userLevel = UserLevel::where('user_id', $user->id)->first();
        $this->assertEquals(10, $userLevel->total_xp);
        
        // 2. Điểm danh lại trong cùng một ngày -> Trả về null
        $resultRepeat = $this->levelService->checkin($user);
        $this->assertNull($resultRepeat);
        $this->assertEquals(10, $userLevel->fresh()->total_xp);

        // 3. Điểm danh tiếp ngày thứ 2 (giả lập tua thời gian sang ngày mai)
        Carbon::setTestNow(Carbon::tomorrow());
        $resultDay2 = $this->levelService->checkin($user);
        $this->assertNotNull($resultDay2);
        $this->assertEquals(2, $resultDay2['streak']);
        $this->assertEquals(10, $resultDay2['xp_awarded']);
        $this->assertEquals(20, $userLevel->fresh()->total_xp);

        // 4. Giả lập tiếp tục điểm danh đến ngày thứ 6 (streak = 6, total_xp = 60)
        for ($i = 3; $i <= 6; $i++) {
            Carbon::setTestNow(Carbon::now()->addDay());
            $this->levelService->checkin($user);
        }
        $this->assertEquals(6, $userLevel->fresh()->checkin_streak);
        $this->assertEquals(60, $userLevel->fresh()->total_xp);

        // 5. Điểm danh ngày thứ 7 (Nhận 10 + 30 bonus = 40 XP)
        Carbon::setTestNow(Carbon::now()->addDay());
        $resultDay7 = $this->levelService->checkin($user);
        $this->assertNotNull($resultDay7);
        $this->assertEquals(7, $resultDay7['streak']);
        $this->assertEquals(40, $resultDay7['xp_awarded']);
        $this->assertEquals(30, $resultDay7['bonus_awarded']);
        $this->assertEquals(100, $userLevel->fresh()->total_xp);

        // 6. Điểm danh ngày thứ 8 (streak reset về 1, nhận lại 10 XP)
        Carbon::setTestNow(Carbon::now()->addDay());
        $resultDay8 = $this->levelService->checkin($user);
        $this->assertNotNull($resultDay8);
        $this->assertEquals(1, $resultDay8['streak']);
        $this->assertEquals(10, $resultDay8['xp_awarded']);
        $this->assertEquals(110, $userLevel->fresh()->total_xp);

        // 7. Điểm danh ngắt quãng (Bỏ qua 1 ngày, chênh lệch 2 ngày, streak reset về 1)
        Carbon::setTestNow(Carbon::now()->addDays(2)); // Chênh lệch 2 ngày từ lần checkin cuối
        $resultGap = $this->levelService->checkin($user);
        $this->assertNotNull($resultGap);
        $this->assertEquals(1, $resultGap['streak']);
        $this->assertEquals(10, $resultGap['xp_awarded']);
        $this->assertEquals(120, $userLevel->fresh()->total_xp);

        // Reset thời gian giả lập
        Carbon::setTestNow();
    }

    /**
     * Test case: Kiểm tra Middleware tự động điểm danh khi user truy cập.
     */
    public function test_middleware_auto_checkin(): void
    {
        $user = User::factory()->create();
        
        // Điền trước thông tin UserLevel
        UserLevel::create([
            'user_id' => $user->id,
            'total_xp' => 0,
            'tier' => 'bronze',
            'checkin_streak' => 0,
            'last_checked_in_at' => null,
            'is_frozen' => false,
            'last_xp_earned_at' => now()->subDays(5),
        ]);

        // Gửi request truy cập trang profile (có áp dụng InjectAdConfig qua web auth)
        $response = $this->actingAs($user)->get('/vi/apps/profile');

        // Kiểm tra xem đã có flash checkin_success trong session
        $response->assertSessionHas('checkin_success');
        $checkinData = session('checkin_success');
        $this->assertEquals(1, $checkinData['streak']);
        $this->assertEquals(10, $checkinData['xp_awarded']);
        
        // Cờ checkin_success_shown phải được thiết lập thành true sau khi render view
        $this->assertTrue(session('checkin_success_shown'));

        // Gửi tiếp request thứ 2 trong cùng ngày
        $response2 = $this->actingAs($user)->get('/vi/apps/profile');
        // Không được có flash checkin_success nữa
        $response2->assertSessionMissing('checkin_success');
    }
}
