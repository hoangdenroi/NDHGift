<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Stichoza\GoogleTranslate\GoogleTranslate;

/**
 * Artisan Command tự động quét các chuỗi cần dịch trong ứng dụng
 * và dịch tự động qua Google Translate API (Miễn phí).
 */
class TranslateLang extends Command
{
    /**
     * Tên và chữ ký của command.
     *
     * @var string
     */
    protected $signature = 'lang:translate 
                            {--locales=vi : Các ngôn ngữ đích cần dịch, phân tách bằng dấu phẩy (ví dụ: vi,ja,ko)}
                            {--force : Dịch lại toàn bộ kể cả các từ đã có bản dịch}';

    /**
     * Mô tả command.
     *
     * @var string
     */
    protected $description = 'Tự động quét toàn bộ mã nguồn và dịch các chuỗi ngôn ngữ tĩnh sang ngôn ngữ đích';

    /**
     * Thư mục chứa các file ngôn ngữ.
     */
    protected string $langPath;

    /**
     * Khởi tạo command.
     */
    public function __construct()
    {
        parent::__construct();
        $this->langPath = base_path('lang');
    }

    /**
     * Thực thi command.
     */
    public function handle(): int
    {
        $this->info('🔍 Bắt đầu quét mã nguồn tìm chuỗi cần dịch...');

        // 1. Quét các chuỗi ngôn ngữ tĩnh từ code
        $keys = $this->scanSourceFiles();
        $totalKeysFound = count($keys);

        if ($totalKeysFound === 0) {
            $this->warn('⚠️ Không tìm thấy chuỗi tĩnh nào cần dịch trong mã nguồn.');
            return self::SUCCESS;
        }

        $this->info("✨ Đã tìm thấy {$totalKeysFound} chuỗi tĩnh cần quản lý.");

        // Tạo thư mục lang nếu chưa tồn tại
        if (!File::isDirectory($this->langPath)) {
            File::makeDirectory($this->langPath, 0755, true);
        }

        // 2. Xử lý file tiếng Anh gốc (en.json)
        $enPath = "{$this->langPath}/en.json";
        $enTranslations = File::exists($enPath) ? json_decode(File::get($enPath), true) ?: [] : [];

        // Đồng bộ các key mới vào en.json
        $enAdded = 0;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $enTranslations)) {
                $enTranslations[$key] = $key;
                $enAdded++;
            }
        }

        if ($enAdded > 0) {
            ksort($enTranslations);
            File::put($enPath, json_encode($enTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info("📝 Đã thêm {$enAdded} key mới vào en.json");
        }

        // 3. Xử lý dịch thuật cho từng ngôn ngữ đích
        $targetLocales = explode(',', $this->option('locales'));
        $force = (bool) $this->option('force');

        foreach ($targetLocales as $locale) {
            $locale = trim($locale);
            if ($locale === 'en') {
                continue;
            }

            $this->info("\n🚀 Bắt đầu tiến trình dịch sang ngôn ngữ: [{$locale}]");

            $localePath = "{$this->langPath}/{$locale}.json";
            $localeTranslations = File::exists($localePath) ? json_decode(File::get($localePath), true) ?: [] : [];

            // Lọc các key cần dịch
            $keysToTranslate = [];
            foreach ($keys as $key) {
                if ($force || !array_key_exists($key, $localeTranslations) || empty($localeTranslations[$key])) {
                    $keysToTranslate[] = $key;
                }
            }

            $totalToTranslate = count($keysToTranslate);

            if ($totalToTranslate === 0) {
                $this->info("✅ Ngôn ngữ [{$locale}] đã được dịch đầy đủ. Không có từ mới.");
                continue;
            }

            $this->info("💬 Tìm thấy {$totalToTranslate} từ cần dịch sang [{$locale}]. Đang kết nối Google Translate...");

            // Khởi tạo thư viện dịch
            $tr = new GoogleTranslate();
            $tr->setSource('en'); // Ngôn ngữ nguồn mặc định trong code là tiếng Anh
            $tr->setTarget($locale);

            $bar = $this->output->createProgressBar($totalToTranslate);
            $bar->start();

            $translatedCount = 0;
            $failedCount = 0;

            foreach ($keysToTranslate as $key) {
                try {
                    // Dịch chuỗi tĩnh
                    // Nếu key quá dài hoặc có ký tự đặc biệt, Google Translate vẫn xử lý tốt
                    $translation = $tr->translate($key);

                    if ($translation) {
                        $localeTranslations[$key] = $translation;
                        $translatedCount++;
                    } else {
                        $localeTranslations[$key] = $key; // Fallback
                        $failedCount++;
                    }

                    // Tránh bị Google chặn IP bằng cách thêm một khoảng sleep nhỏ (150ms)
                    usleep(150000);
                } catch (\Throwable $e) {
                    $localeTranslations[$key] = $key; // Fallback khi lỗi
                    $failedCount++;
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            // Sắp xếp theo bảng chữ cái và ghi lại file JSON
            ksort($localeTranslations);
            File::put($localePath, json_encode($localeTranslations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $this->info("💾 Đã lưu dữ liệu dịch vào {$locale}.json");
            $this->info("📈 Kết quả dịch: Thành công {$translatedCount} từ, thất bại/fallback {$failedCount} từ.");
        }

        $this->info("\n🎉 Hoàn thành tiến trình dịch thuật thành công!");

        return self::SUCCESS;
    }

    /**
     * Quét các file nguồn và tìm các chuỗi trong hàm dịch __(), trans(), @lang().
     *
     * @return array<string>
     */
    protected function scanSourceFiles(): array
    {
        $keys = [];

        // Các thư mục cần quét
        $directories = [
            app_path(),
            resource_path('views'),
        ];

        // Biểu thức chính quy tìm hàm dịch tĩnh: __(), trans(), @lang()
        // Chỉ bắt các chuỗi tĩnh được bao trong nháy đơn hoặc nháy kép, không có biến số
        $patterns = [
            '/(?:__|trans|@lang)\(\s*([\'"])(.*?)\1\s*[\),]/u',
            '/trans_choice\(\s*([\'"])(.*?)\1\s*,\s*\d+/u',
        ];

        foreach ($directories as $dir) {
            if (!File::isDirectory($dir)) {
                continue;
            }

            $files = File::allFiles($dir);

            foreach ($files as $file) {
                // Chỉ quét file PHP và Blade
                if (!in_array($file->getExtension(), ['php'], true)) {
                    continue;
                }

                $content = File::get($file->getPathname());

                foreach ($patterns as $pattern) {
                    if (preg_match_all($pattern, $content, $matches)) {
                        // $matches[2] chứa các chuỗi tĩnh tìm được
                        foreach ($matches[2] as $match) {
                            $match = trim($match);
                            
                            // Loại bỏ các chuỗi rỗng hoặc các key có cấu trúc biến (không phải chuỗi tĩnh sạch)
                            if ($match !== '' && !preg_match('/^\$[a-zA-Z0-9_]+$/', $match)) {
                                $keys[$match] = true;
                            }
                        }
                    }
                }
            }
        }

        return array_keys($keys);
    }
}
