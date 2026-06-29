<x-admin-layout title="NDHGift - Quản lý danh mục quà tặng">
    <div class="flex flex-col gap-6">

        {{-- 1. Thẻ thống kê --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng danh mục</p>
                    <span class="material-symbols-outlined text-primary text-[20px]">category</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['totalCategories']) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">danh mục</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Đang hoạt động</p>
                    <span class="material-symbols-outlined text-emerald-500 text-[20px]">check_circle</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['activeCategories']) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">đang bật</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Đang tạm tắt</p>
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">visibility_off</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['inactiveCategories']) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">đang ẩn</span>
                </div>
            </div>
        </div>

        {{-- 2. Thanh lọc & công cụ --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white/80 dark:bg-surface-dark border border-slate-200 dark:border-border-dark p-4 rounded-xl backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row flex-wrap items-center gap-2 w-full xl:w-auto flex-1">
                {{-- Lọc theo trạng thái --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-48">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">toggle_on</span>
                    <select id="filterActive"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('is_active') === null || request('is_active') === '' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Đã tắt</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Ô tìm kiếm --}}
                <div class="relative flex-1 w-full sm:min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">search</span>
                    <input type="text" id="searchInput" placeholder="Tìm theo tên hoặc slug..."
                        value="{{ request('search') }}"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        oninput="debounceSearch()">
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button x-data x-on:click="$dispatch('open-modal', 'create-category')"
                    class="flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm shadow-primary/25 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Thêm danh mục
                </button>
            </div>
        </div>

        {{-- 3. Bảng danh sách --}}
        <div class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark overflow-hidden flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-background-dark/50 border-b border-slate-200 dark:border-border-dark">
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16 text-center">STT</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-16 text-center">Icon</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tên danh mục</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Đường dẫn slug</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider max-w-[280px]">Mô tả</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Sắp xếp</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Trạng thái</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-24">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-border-dark">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors group">
                                <td class="p-4 text-sm text-slate-500 text-center">
                                    {{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}
                                </td>
                                <td class="p-4 text-center">
                                    <div class="inline-flex size-9 items-center justify-center rounded-lg bg-slate-100 dark:bg-background-dark text-slate-700 dark:text-slate-300">
                                        <span class="material-symbols-outlined text-[20px]">{{ $cat->icon ?? 'category' }}</span>
                                    </div>
                                </td>
                                <td class="p-4 text-sm font-semibold text-slate-900 dark:text-white">
                                    {{ __($cat->name) }}
                                    @if(__($cat->name) !== $cat->name)
                                        <span class="block text-[10px] text-slate-400 font-normal">Key: {{ $cat->name }}</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm font-mono text-slate-500">
                                    {{ $cat->slug }}
                                </td>
                                <td class="p-4 text-xs text-slate-400 max-w-[280px] truncate" title="{{ $cat->description }}">
                                    {{ $cat->description ?? '--' }}
                                </td>
                                <td class="p-4 text-sm text-slate-700 dark:text-slate-300 text-center font-medium">
                                    {{ $cat->sort_order }}
                                </td>
                                <td class="p-4">
                                    {{-- Toggle active --}}
                                    <form action="{{ route('admin.gift-categories.toggle-active', $cat) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $cat->is_active ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-600' }}"
                                            title="{{ $cat->is_active ? 'Đang bật — nhấn để tắt' : 'Đang tắt — nhấn để bật' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm {{ $cat->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="p-4 text-right">
                                    <div x-data="{ open: false }" class="relative inline-block text-left">
                                        <button @click="open = !open" @click.away="open = false"
                                            class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 mt-1 w-44 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-xl shadow-xl z-30 py-1 origin-top-right"
                                            x-cloak>
                                            <button
                                                @click="open = false; $dispatch('open-edit-category', {{ json_encode(['id' => $cat->id, 'name' => $cat->name, 'slug' => $cat->slug, 'description' => $cat->description, 'icon' => $cat->icon, 'sort_order' => $cat->sort_order, 'is_active' => $cat->is_active, 'meta_title' => $cat->meta_title, 'meta_description' => $cat->meta_description, 'meta_keywords' => $cat->meta_keywords]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-background-dark/50 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px] text-slate-400">edit</span>
                                                Chỉnh sửa
                                            </button>
                                            <div class="border-t border-slate-200 dark:border-border-dark my-1"></div>
                                            <button
                                                @click="open = false; $dispatch('open-delete-category', {{ json_encode(['id' => $cat->id, 'name' => __($cat->name)]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Xóa danh mục
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="p-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="size-16 rounded-full bg-slate-100 dark:bg-background-dark flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[32px] text-slate-300 dark:text-slate-600">category</span>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-sm font-medium">Chưa có danh mục nào</p>
                                            <p class="text-slate-400 text-xs mt-1">Tạo danh mục đầu tiên để tổ chức các templates quà tặng.</p>
                                        </div>
                                        <button x-data x-on:click="$dispatch('open-modal', 'create-category')"
                                            class="mt-2 flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            Thêm danh mục
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-background-dark/30 gap-4">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Hiển thị <span class="font-bold text-slate-900 dark:text-white">{{ $categories->firstItem() }}</span>
                        đến <span class="font-bold text-slate-900 dark:text-white">{{ $categories->lastItem() }}</span>
                        trong <span class="font-bold text-slate-900 dark:text-white">{{ $categories->total() }}</span> danh mục
                    </div>
                    <div>
                        {{ $categories->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            let searchTimeout = null;

            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('searchInput');
                if (searchInput && searchInput.value) {
                    searchInput.focus();
                    const val = searchInput.value;
                    searchInput.value = '';
                    searchInput.value = val;
                }
            });

            function debounceSearch() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => { applyFilters(); }, 500);
            }

            function applyFilters() {
                const isActive = document.getElementById('filterActive').value;
                const search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
                const params = new URLSearchParams(window.location.search);

                isActive !== '' ? params.set('is_active', isActive) : params.delete('is_active');
                search ? params.set('search', search) : params.delete('search');
                params.delete('page');

                window.location.href = '{{ route("admin.gift-categories.index") }}?' + params.toString();
            }
        </script>
    @endpush

    @include('components.pages.admin.gift-categories.gift-categories-crud.gift-category-modals')
</x-admin-layout>
