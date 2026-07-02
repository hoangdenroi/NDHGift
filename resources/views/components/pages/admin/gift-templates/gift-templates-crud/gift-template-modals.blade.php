{{-- declare(strict_types=1); --}}

{{-- ==========================================
     MODAL THÊM MẪU QUÀ TẶNG MỚI
     ========================================== --}}
<x-shared.ui.modal name="create-template" maxWidth="2xl">
    <div>
        <form method="POST" action="{{ route('admin.gift-templates.store') }}"
            x-data="{ 
                activeTab: 'general',
                formSchema: '',
                jsonError: null,
                validateJSON() {
                    if (!this.formSchema.trim()) {
                        this.jsonError = null;
                        return;
                    }
                    try {
                        JSON.parse(this.formSchema);
                        this.jsonError = null;
                    } catch (e) {
                        this.jsonError = 'Lỗi cú pháp JSON: ' + e.message;
                    }
                }
            }"
            @submit="if (jsonError) { $event.preventDefault(); alert('Vui lòng sửa lỗi JSON trước khi lưu!'); }">
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

                {{-- THANH CHUYỂN TAB --}}
                <div class="border-b border-slate-200 dark:border-border-dark mb-6">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-1.5 focus:outline-none">
                            <span class="material-symbols-outlined text-[18px]">info</span>
                            Thông tin chung
                        </button>
                        <button type="button" @click="activeTab = 'config'"
                            :class="activeTab === 'config' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-1.5 focus:outline-none">
                            <span class="material-symbols-outlined text-[18px]">settings_3d</span>
                            Cấu hình 3D & Form
                        </button>
                        <button type="button" @click="activeTab = 'seo'"
                            :class="activeTab === 'seo' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-1.5 focus:outline-none">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                            Cấu hình SEO
                        </button>
                    </nav>
                </div>

                {{-- NỘI DUNG CÁC TAB --}}
                <div>
                    {{-- TAB 1: THÔNG TIN CHUNG --}}
                    <div x-show="activeTab === 'general'" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                            </div>

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

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mô tả mẫu quà tặng</label>
                            <textarea name="description" rows="3"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Mô tả tóm tắt tính năng, hiệu ứng của template..."></textarea>
                        </div>
                    </div>

                    {{-- TAB 2: CẤU HÌNH 3D & FORM --}}
                    <div x-show="activeTab === 'config'" class="space-y-5" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kiểu mở quà (Opening Type) <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <select name="opening_type" required
                                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                            <option value="auto_load" selected>Auto Load (Chạy thanh tiến trình tự động)</option>
                                            <option value="press_hold">Press & Hold (Nhấn giữ tương tác để mở)</option>
                                        </select>
                                        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Cấu hình cách thức người nhận tương tác mở quà lúc ban đầu.</p>
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
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cấu hình Form nhập liệu (JSON Schema)</label>
                                <textarea name="form_schema" rows="10" x-model="formSchema" @input="validateJSON"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder='{
  "fields": [
    {
      "name": "receiver_name",
      "type": "text",
      "label": "Tên người nhận",
      "default": "Người Nhận"
    }
  ]
}'></textarea>
                                <template x-if="jsonError">
                                    <div class="text-[11px] text-rose-500 font-medium flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">error</span>
                                        <span x-text="jsonError"></span>
                                    </div>
                                </template>
                                <template x-if="!jsonError && formSchema.trim() !== ''">
                                    <div class="text-[11px] text-emerald-500 font-medium flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        Cú pháp JSON hoàn toàn hợp lệ.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: CẤU HÌNH SEO --}}
                    <div x-show="activeTab === 'seo'" class="space-y-4" x-cloak>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ tiêu đề SEO (Meta Title)</label>
                            <input type="text" name="meta_title"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Tiêu đề hiển thị trên Google Search...">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ mô tả SEO (Meta Description)</label>
                            <textarea name="meta_description" rows="3"
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
                <button type="submit" :disabled="jsonError !== null"
                    :class="jsonError !== null ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-primary hover:bg-primary/90 shadow-primary/25'"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors shadow-sm">
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
        activeTab: 'general',
        tmpl: { id: null, category_id: '', code: '', name: '', description: '', price: 0, discount: 0, is_hot: false, is_active: true, demo_url: '', guide_url: '', video_url: '', meta_title: '', meta_description: '', meta_keywords: '', form_schema: null, opening_type: 'auto_load' },
        formSchemaStr: '',
        jsonError: null,
        initEdit(detail) {
            this.tmpl = detail;
            this.activeTab = 'general';
            this.jsonError = null;
            if (detail.form_schema) {
                if (typeof detail.form_schema === 'object') {
                    this.formSchemaStr = JSON.stringify(detail.form_schema, null, 2);
                } else {
                    try {
                        this.formSchemaStr = JSON.stringify(JSON.parse(detail.form_schema), null, 2);
                    } catch(e) {
                        this.formSchemaStr = detail.form_schema;
                    }
                }
            } else {
                this.formSchemaStr = '';
            }
        },
        validateJSON() {
            if (!this.formSchemaStr.trim()) {
                this.jsonError = null;
                return;
            }
            try {
                JSON.parse(this.formSchemaStr);
                this.jsonError = null;
            } catch (e) {
                this.jsonError = 'Lỗi cú pháp JSON: ' + e.message;
            }
        }
    }"
    @open-edit-template.window="initEdit($event.detail); $dispatch('open-modal', 'edit-template')">

    <x-shared.ui.modal name="edit-template" maxWidth="2xl">
        <form :action="'{{ route('admin.gift-templates.index') }}/' + tmpl.id" method="POST"
            @submit="if (jsonError) { $event.preventDefault(); alert('Vui lòng sửa lỗi JSON trước khi lưu!'); }">
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

                {{-- THANH CHUYỂN TAB --}}
                <div class="border-b border-slate-200 dark:border-border-dark mb-6">
                    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                        <button type="button" @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-1.5 focus:outline-none">
                            <span class="material-symbols-outlined text-[18px]">info</span>
                            Thông tin chung
                        </button>
                        <button type="button" @click="activeTab = 'config'"
                            :class="activeTab === 'config' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-1.5 focus:outline-none">
                            <span class="material-symbols-outlined text-[18px]">settings_3d</span>
                            Cấu hình 3D & Form
                        </button>
                        <button type="button" @click="activeTab = 'seo'"
                            :class="activeTab === 'seo' ? 'border-primary text-primary font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-all flex items-center gap-1.5 focus:outline-none">
                            <span class="material-symbols-outlined text-[18px]">search</span>
                            Cấu hình SEO
                        </button>
                    </nav>
                </div>

                {{-- NỘI DUNG CÁC TAB --}}
                <div>
                    {{-- TAB 1: THÔNG TIN CHUNG --}}
                    <div x-show="activeTab === 'general'" class="space-y-5">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
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
                            </div>

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

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mô tả mẫu quà tặng</label>
                            <textarea name="description" x-model="tmpl.description" rows="3"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                        </div>
                    </div>

                    {{-- TAB 2: CẤU HÌNH 3D & FORM --}}
                    <div x-show="activeTab === 'config'" class="space-y-5" x-cloak>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="flex flex-col gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Kiểu mở quà (Opening Type) <span class="text-rose-500">*</span></label>
                                    <div class="relative">
                                        <select name="opening_type" x-model="tmpl.opening_type" required
                                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                            <option value="auto_load">Auto Load (Chạy thanh tiến trình tự động)</option>
                                            <option value="press_hold">Press & Hold (Nhấn giữ tương tác để mở)</option>
                                        </select>
                                        <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Cấu hình cách thức người nhận tương tác mở quà lúc ban đầu.</p>
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
                            </div>

                            <div class="flex flex-col gap-2">
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cấu hình Form nhập liệu (JSON Schema)</label>
                                <textarea name="form_schema" rows="10" x-model="formSchemaStr" @input="validateJSON"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                                <template x-if="jsonError">
                                    <div class="text-[11px] text-rose-500 font-medium flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">error</span>
                                        <span x-text="jsonError"></span>
                                    </div>
                                </template>
                                <template x-if="!jsonError && formSchemaStr.trim() !== ''">
                                    <div class="text-[11px] text-emerald-500 font-medium flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                        Cú pháp JSON hoàn toàn hợp lệ.
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- TAB 3: CẤU HÌNH SEO --}}
                    <div x-show="activeTab === 'seo'" class="space-y-4" x-cloak>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ tiêu đề SEO (Meta Title)</label>
                            <input type="text" name="meta_title" x-model="tmpl.meta_title"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thẻ mô tả SEO (Meta Description)</label>
                            <textarea name="meta_description" x-model="tmpl.meta_description" rows="3"
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
                <button type="submit" :disabled="jsonError !== null"
                    :class="jsonError !== null ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-primary hover:bg-primary/90 shadow-primary/25'"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors shadow-sm">
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
