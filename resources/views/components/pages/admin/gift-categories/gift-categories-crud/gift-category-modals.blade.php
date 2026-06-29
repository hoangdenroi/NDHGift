{{-- Declare strict types --}}
{{-- ==========================================
     MODAL TẠO DANH MỤC MỚI
     ========================================== --}}
<x-shared.ui.modal name="create-category" maxWidth="lg">
    <div x-data="{ 
        name: '', 
        slug: '',
        isSlugEdited: false,
        updateSlug() {
            if (!this.isSlugEdited) {
                this.slug = this.name.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/[đĐ]/g, 'd')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }
        }
    }">
        <form method="POST" action="{{ route('admin.gift-categories.store') }}">
            @csrf
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">category</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Thêm danh mục quà tặng</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tạo danh mục/chủ đề quà tặng mới cho hệ thống</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'create-category')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- 1. Tên danh mục & Slug (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tên danh mục <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="name" x-on:input="updateSlug()" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: Sinh nhật 3D">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Đường dẫn Slug <span class="text-rose-500">*</span></label>
                            <input type="text" name="slug" x-model="slug" x-on:input="isSlugEdited = true" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: sinh-nhat-3d">
                        </div>
                    </div>

                    {{-- 2. Icon danh mục & Thứ tự sắp xếp (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Icon (Material Symbols) <span class="text-rose-500">*</span></label>
                            <input type="text" name="icon" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="VD: cake, favorite, celebration">
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-1">Sử dụng tên icon Google Material Symbols.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" min="0" value="0"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="0">
                        </div>
                    </div>

                    {{-- 3. Mô tả (Full width) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mô tả danh mục</label>
                        <textarea name="description" rows="3"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Mô tả tóm tắt nội dung danh mục..."></textarea>
                    </div>

                    {{-- SEO Accordion/Collapse --}}
                    <div x-data="{ openSeo: false }" class="border border-slate-100 dark:border-border-dark/50 rounded-lg overflow-hidden">
                        <button type="button" @click="openSeo = !openSeo"
                            class="flex items-center justify-between w-full px-4 py-2.5 bg-slate-50 dark:bg-background-dark/50 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-background-dark transition-colors">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">search_reveal</span>
                                Cấu hình SEO (Tùy chọn)
                            </span>
                            <span class="material-symbols-outlined text-[18px] transition-transform" :class="openSeo ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="openSeo" class="p-4 flex flex-col gap-4 border-t border-slate-100 dark:border-border-dark/50 bg-white dark:bg-surface-dark/20" x-cloak>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Meta Title</label>
                                    <input type="text" name="meta_title"
                                        class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                        placeholder="Tiêu đề SEO">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Meta Keywords</label>
                                    <input type="text" name="meta_keywords"
                                        class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                        placeholder="Từ khóa SEO">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Meta Description</label>
                                <textarea name="meta_description" rows="2"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                    placeholder="Mô tả SEO ngắn..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Trạng thái kích hoạt (Switch) --}}
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-border-dark/50 pt-4 mt-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Trạng thái kích hoạt</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-category')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Tạo mới
                </button>
            </div>
        </form>
    </div>
</x-shared.ui.modal>

{{-- ==========================================
     MODAL CHỈNH SỬA DANH MỤC
     ========================================== --}}
<div x-data="{
        editCategory: { id: null, name: '', slug: '', description: '', icon: '', sort_order: 0, is_active: true, meta_title: '', meta_description: '', meta_keywords: '' },
    }"
    @open-edit-category.window="editCategory = $event.detail; $dispatch('open-modal', 'edit-category')">

    <x-shared.ui.modal name="edit-category" maxWidth="lg">
        <form :action="'{{ route('admin.gift-categories.index') }}/' + editCategory.id" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">edit_document</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cập nhật danh mục</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Chỉnh sửa thông tin danh mục quà tặng</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-category')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- 1. Tên danh mục & Slug (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tên danh mục <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="editCategory.name" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Đường dẫn Slug <span class="text-rose-500">*</span></label>
                            <input type="text" name="slug" x-model="editCategory.slug" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>

                    {{-- 2. Icon danh mục & Thứ tự sắp xếp (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Icon (Material Symbols) <span class="text-rose-500">*</span></label>
                            <input type="text" name="icon" x-model="editCategory.icon" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Thứ tự hiển thị</label>
                            <input type="number" name="sort_order" x-model="editCategory.sort_order" min="0"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>

                    {{-- 3. Mô tả (Full width) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mô tả danh mục</label>
                        <textarea name="description" x-model="editCategory.description" rows="3"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                    </div>

                    {{-- SEO Accordion/Collapse --}}
                    <div x-data="{ openSeo: false }" class="border border-slate-100 dark:border-border-dark/50 rounded-lg overflow-hidden">
                        <button type="button" @click="openSeo = !openSeo"
                            class="flex items-center justify-between w-full px-4 py-2.5 bg-slate-50 dark:bg-background-dark/50 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-background-dark transition-colors">
                            <span class="flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[18px]">search_reveal</span>
                                Cấu hình SEO (Tùy chọn)
                            </span>
                            <span class="material-symbols-outlined text-[18px] transition-transform" :class="openSeo ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="openSeo" class="p-4 flex flex-col gap-4 border-t border-slate-100 dark:border-border-dark/50 bg-white dark:bg-surface-dark/20" x-cloak>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Meta Title</label>
                                    <input type="text" name="meta_title" x-model="editCategory.meta_title"
                                        class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Meta Keywords</label>
                                    <input type="text" name="meta_keywords" x-model="editCategory.meta_keywords"
                                        class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 dark:text-slate-400 mb-1">Meta Description</label>
                                <textarea name="meta_description" x-model="editCategory.meta_description" rows="2"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- 4. Trạng thái kích hoạt (Switch) --}}
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-border-dark/50 pt-4 mt-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Trạng thái kích hoạt</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" :checked="editCategory.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-category')"
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
     MODAL XÓA DANH MỤC
     ========================================== --}}
<div x-data="{
        deleteCategory: { id: null, name: '' },
    }"
    @open-delete-category.window="deleteCategory = $event.detail; $dispatch('open-modal', 'delete-category')">

    <x-shared.ui.modal name="delete-category" maxWidth="md">
        <form :action="'{{ route('admin.gift-categories.index') }}/' + deleteCategory.id" method="POST">
            @csrf
            @method('DELETE')
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-rose-500 text-[20px]">delete_forever</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Xóa danh mục quà tặng</h3>
                        <p class="text-sm text-slate-500">
                            Bạn có chắc muốn xóa danh mục <span class="font-bold text-slate-900 dark:text-white" x-text="deleteCategory.name"></span>?
                        </p>
                    </div>
                </div>
                <div class="bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800/30 rounded-lg p-3">
                    <p class="text-sm text-rose-700 dark:text-rose-400">
                        <span class="material-symbols-outlined text-[16px] align-text-bottom mr-1">warning</span>
                        Hành động này sẽ thực hiện **xóa mềm** danh mục. Bạn vẫn có thể khôi phục lại trong cơ sở dữ liệu nếu cần thiết.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'delete-category')"
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
