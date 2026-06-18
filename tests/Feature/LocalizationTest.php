<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kiểm tra hệ thống đa ngôn ngữ (Localization) hoạt động đúng.
 *
 * Bao phủ:
 * - Happy path: truy cập với locale hợp lệ
 * - Edge case: locale không hợp lệ → redirect
 * - Route gốc "/" → redirect về locale mặc định
 * - Session ghi nhớ locale
 * - Middleware SetLocale thiết lập đúng app locale
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Truy cập "/" phải redirect về locale mặc định (en).
     */
    public function test_root_redirects_to_default_locale(): void
    {
        $defaultLocale = config('localization.default_locale');

        $response = $this->get('/');

        $response->assertRedirect("/{$defaultLocale}");
    }

    /**
     * Truy cập /en/ phải trả về 200 và thiết lập locale = en.
     */
    public function test_english_locale_returns_200(): void
    {
        $response = $this->get('/en');

        $response->assertStatus(200);
    }

    /**
     * Truy cập /vi/ phải trả về 200 và thiết lập locale = vi.
     */
    public function test_vietnamese_locale_returns_200(): void
    {
        $response = $this->get('/vi');

        $response->assertStatus(200);
    }

    /**
     * Truy cập locale không hợp lệ phải redirect về locale mặc định.
     */
    public function test_invalid_locale_redirects_to_default(): void
    {
        $defaultLocale = config('localization.default_locale');

        $response = $this->get('/fr');

        $response->assertRedirect("/{$defaultLocale}/");
    }

    /**
     * Auth route /en/login phải trả về 200.
     */
    public function test_login_page_accessible_with_locale(): void
    {
        $response = $this->get('/en/login');

        $response->assertStatus(200);
    }

    /**
     * Auth route /vi/login phải trả về 200.
     */
    public function test_login_page_accessible_with_vi_locale(): void
    {
        $response = $this->get('/vi/login');

        $response->assertStatus(200);
    }

    /**
     * Auth route /en/register phải trả về 200.
     */
    public function test_register_page_accessible_with_locale(): void
    {
        $response = $this->get('/en/register');

        $response->assertStatus(200);
    }

    /**
     * Truy cập /login (không có locale) phải redirect về /en/login.
     * Route này không tồn tại nữa → sẽ trả về 404 hoặc redirect.
     */
    public function test_login_without_locale_is_not_accessible(): void
    {
        $response = $this->get('/login');

        // Route /login không tồn tại nữa, sẽ trả 404
        // hoặc nếu có catch-all thì redirect
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 404], true),
            'Truy cập /login không có locale phải redirect hoặc 404'
        );
    }

    /**
     * Middleware SetLocale phải lưu locale vào session.
     */
    public function test_locale_saved_in_session(): void
    {
        $response = $this->get('/vi');

        $response->assertSessionHas('locale', 'vi');
    }

    /**
     * Middleware SetLocale phải thiết lập đúng app locale cho tiếng Anh.
     */
    public function test_app_locale_set_correctly_for_english(): void
    {
        $this->get('/en');

        $this->assertEquals('en', app()->getLocale());
    }

    /**
     * Middleware SetLocale phải thiết lập đúng app locale cho tiếng Việt.
     */
    public function test_app_locale_set_correctly_for_vietnamese(): void
    {
        $this->get('/vi');

        $this->assertEquals('vi', app()->getLocale());
    }

    /**
     * Route helper phải sinh URL đúng với locale prefix.
     */
    public function test_route_helper_generates_localized_urls(): void
    {
        // Truy cập /en để middleware inject URL defaults
        $this->get('/en');

        $url = route('home', ['locale' => 'en']);

        $this->assertStringContainsString('/en', $url);
    }

    /**
     * Route helper cho login phải sinh URL đúng với locale prefix.
     */
    public function test_login_route_includes_locale(): void
    {
        $this->get('/vi');

        $url = route('login', ['locale' => 'vi']);

        $this->assertStringContainsString('/vi/login', $url);
    }

    /**
     * Locale chỉ chấp nhận 2 ký tự chữ cái thường — chặn injection.
     */
    public function test_locale_rejects_special_characters(): void
    {
        $response = $this->get('/a1');

        // Locale a1 không match pattern [a-z]{2} → 404
        $this->assertTrue(
            in_array($response->getStatusCode(), [301, 302, 404], true),
            'Locale chứa số phải bị từ chối'
        );
    }

    /**
     * Locale không chấp nhận chuỗi dài hơn 2 ký tự.
     */
    public function test_locale_rejects_long_strings(): void
    {
        $response = $this->get('/eng');

        // 'eng' không match [a-z]{2} → 404
        $response->assertStatus(404);
    }
}
