{{-- declare(strict_types=1); --}}

{{-- ==========================================
     MODAL THÊM MẪU QUÀ TẶNG MỚI
     ========================================== --}}
<x-shared.ui.modal name="create-template" maxWidth="2xl">
    <div>
        <form method="POST" action="{{ route('admin.gift-templates.store') }}">
            @csrf
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">add_to_photos</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Thêm mẫu quà tặng mới</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tạo và cấu hình giao diện mẫu quà tặng 3D mới</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'create-template')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Form chia làm 2 cột --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- CỘT 1: Thông tin cơ bản --}}
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tên mẫu quà tặng <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: Web Trái Tim 3D Tình Yêu">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mã code thư mục (Slug/Folder) <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: heart_3d">
                            <p class="text-[10px] text-slate-400 mt-1">Trùng với tên thư mục chứa template trong public/templates/</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Danh mục quà tặng <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="category_id" required
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="" disabled selected>Chọn danh mục...</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ __($cat->name) }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mô tả mẫu quà tặng</label>
                            <textarea name="description" rows="4"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Mô tả tóm tắt tính năng, hiệu ứng của template..."></textarea>
                        </div>
                    </div>

                    {{-- CỘT 2: Thương mại & Kỹ thuật --}}
                    <div class="flex flex-col gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Giá bán (đ) <span class="text-rose-500">*</span></label>
                                <input type="number" name="price" required min="0" step="1000" value="0"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="0">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Chiết khấu (%)</label>
                                <input type="number" name="discount" min="0" max="100" value="0"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="0">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL xem thử Demo</label>
                            <input type="text" name="demo_url"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: /templates/heart_3d/index.html">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL bài viết hướng dẫn</label>
                            <input type="text" name="guide_url"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: https://docs.ndhgift.com/guide/heart_3d">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL video review (Youtube)</label>
                            <input type="text" name="video_url"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: https://youtube.com/watch?v=...">
                        </div>

                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100 dark:border-border-dark/50">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                <span class="material-symbols-outlined text-rose-500 text-[18px]">local_fire_department</span>
                                Đánh dấu nổi bật (HOT)
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_hot" value="1" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Trạng thái hoạt động</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- COLLAPSIBLE SEO CONFIG (Gom nhóm SEO bằng Alpine.js) --}}
                <div x-data="{ showSEO: false }" class="mt-6 border-t border-slate-200 dark:border-border-dark pt-4">
                    <button type="button" @click="showSEO = !showSEO"
                        class="flex items-center justify-between w-full py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors focus:outline-none">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                            Cấu hình SEO nâng cao
                        </span>
                        <span class="material-symbols-outlined transform transition-transform" :class="showSEO ? 'rotate-180' : ''">keyboard_arrow_down</span>
                    </button>

                    <div x-show="showSEO" x-collapse x-cloak class="mt-4 flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ tiêu đề SEO (Meta Title)</label>
                            <input type="text" name="meta_title"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Tiêu đề hiển thị trên Google Search...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ mô tả SEO (Meta Description)</label>
                            <textarea name="meta_description" rows="2"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Mô tả tóm tắt hiển thị trên Google Search..."></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Từ khóa SEO (Meta Keywords)</label>
                            <input type="text" name="meta_keywords"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Cách nhau bởi dấu phẩy (VD: trai tim 3d, valentine, thiep 3d...)">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-template')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Lưu lại
                </button>
            </div>
        </form>
    </div>
</x-shared.ui.modal>


{{-- ==========================================
     MODAL CHỈNH SỬA MẪU QUÀ TẶNG
     ========================================== --}}
<div x-data="{
        tmpl: { id: null, category_id: '', code: '', name: '', description: '', price: 0, discount: 0, is_hot: false, is_active: true, demo_url: '', guide_url: '', video_url: '', meta_title: '', meta_description: '', meta_keywords: '' },
    }"
    @open-edit-template.window="tmpl = $event.detail; $dispatch('open-modal', 'edit-template')">

    <x-shared.ui.modal name="edit-template" maxWidth="2xl">
        <form :action="'{{ route('admin.gift-templates.index') }}/' + tmpl.id" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">edit_square</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Chỉnh sửa mẫu quà tặng</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Chỉnh sửa và cập nhật chi tiết cấu hình template</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-template')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Form 2 cột chỉnh sửa --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    
                    {{-- CỘT 1: Thông tin cơ bản --}}
                    <div class="flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tên mẫu quà tặng <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="tmpl.name" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mã code thư mục <span class="text-rose-500">*</span></label>
                            <input type="text" name="code" x-model="tmpl.code" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Danh mục quà tặng <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="category_id" x-model="tmpl.category_id" required
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ __($cat->name) }}</option>
                                    @endforeach
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mô tả mẫu quà tặng</label>
                            <textarea name="description" x-model="tmpl.description" rows="4"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                        </div>
                    </div>

                    {{-- CỘT 2: Thương mại & Kỹ thuật --}}
                    <div class="flex flex-col gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Giá bán (đ) <span class="text-rose-500">*</span></label>
                                <input type="number" name="price" x-model="tmpl.price" required min="0" step="1000"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Chiết khấu (%)</label>
                                <input type="number" name="discount" x-model="tmpl.discount" min="0" max="100"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL xem thử Demo</label>
                            <input type="text" name="demo_url" x-model="tmpl.demo_url"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL bài viết hướng dẫn</label>
                            <input type="text" name="guide_url" x-model="tmpl.guide_url"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">URL video review (Youtube)</label>
                            <input type="text" name="video_url" x-model="tmpl.video_url"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-100 dark:border-border-dark/50">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                <span class="material-symbols-outlined text-rose-500 text-[18px]">local_fire_department</span>
                                Đánh dấu nổi bật (HOT)
                            </span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_hot" value="1" :checked="tmpl.is_hot" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Trạng thái hoạt động</span>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" :checked="tmpl.is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- COLLAPSIBLE SEO CONFIG FOR EDIT (Alpine.js) --}}
                <div x-data="{ showSEO: false }" class="mt-6 border-t border-slate-200 dark:border-border-dark pt-4">
                    <button type="button" @click="showSEO = !showSEO"
                        class="flex items-center justify-between w-full py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 hover:text-primary dark:hover:text-primary transition-colors focus:outline-none">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">search</span>
                            Cấu hình SEO nâng cao
                        </span>
                        <span class="material-symbols-outlined transform transition-transform" :class="showSEO ? 'rotate-180' : ''">keyboard_arrow_down</span>
                    </button>

                    <div x-show="showSEO" x-collapse x-cloak class="mt-4 flex flex-col gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ tiêu đề SEO (Meta Title)</label>
                            <input type="text" name="meta_title" x-model="tmpl.meta_title"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ mô tả SEO (Meta Description)</label>
                            <textarea name="meta_description" x-model="tmpl.meta_description" rows="2"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Từ khóa SEO (Meta Keywords)</label>
                            <input type="text" name="meta_keywords" x-model="tmpl.meta_keywords"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-template')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Cập nhật
                </button>
            </div>
        </form>
    </x-shared.ui.modal>
</div>


{{-- ==========================================
     MODAL XÓA MẪU QUÀ TẶNG
     ========================================== --}}
<div x-data="{
        deleteTmpl: { id: null, name: '' },
    }"
    @open-delete-template.window="deleteTmpl = $event.detail; $dispatch('open-modal', 'delete-template')">

    <x-shared.ui.modal name="delete-template" maxWidth="md">
        <form :action="'{{ route('admin.gift-templates.index') }}/' + deleteTmpl.id" method="POST">
            @csrf
            @method('DELETE')
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-rose-500 text-[20px]">delete_forever</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Xóa mẫu quà tặng</h3>
                        <p class="text-sm text-slate-500">
                            Bạn có chắc muốn xóa mẫu <span class="font-bold text-primary" x-text="deleteTmpl.name"></span>?
                        </p>
                    </div>
                </div>
                <div class="bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800/30 rounded-lg p-3">
                    <p class="text-xs text-rose-700 dark:text-rose-400">
                        <span class="material-symbols-outlined text-[14px] align-text-bottom mr-1">warning</span>
                        Hành động này sẽ ẩn giao diện này khỏi khách hàng (xóa mềm). Bạn có thể khôi phục lại từ database nếu cần.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'delete-template')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-rose-500 hover:bg-rose-600 rounded-lg transition-colors shadow-sm shadow-rose-500/25">
                    Xác nhận xóa
                </button>
            </div>
        </form>
    </x-shared.ui.modal>
</div>
