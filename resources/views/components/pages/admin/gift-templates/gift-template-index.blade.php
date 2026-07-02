<x-admin-layout title="NDHGift - Quản lý mẫu quà tặng">
    <div class="flex flex-col gap-6">

        {{-- 1. Thẻ thống kê --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng số mẫu</p>
                    <span class="material-symbols-outlined text-primary text-[20px]">redeem</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['total_templates'] ?? 0) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">giao diện</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Đang hoạt động</p>
                    <span class="material-symbols-outlined text-emerald-500 text-[20px]">check_circle</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['active_templates'] ?? 0) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">đang bật</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Mẫu bán chạy (HOT)</p>
                    <span class="material-symbols-outlined text-rose-500 text-[20px]">local_fire_department</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['hot_templates'] ?? 0) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">nổi bật</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng lượt bán</p>
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">shopping_cart</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['total_sold'] ?? 0) }}
                    </h3>
                    <span class="text-slate-400 text-xs font-medium">giao dịch</span>
                </div>
            </div>
        </div>

        {{-- 2. Thanh lọc & công cụ --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white/80 dark:bg-surface-dark border border-slate-200 dark:border-border-dark p-4 rounded-xl backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row flex-wrap items-center gap-2 w-full xl:w-auto flex-1">
                {{-- Lọc theo danh mục --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-48">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">category</span>
                    <select id="filterCategory"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="">Tất cả danh mục</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ __($cat->name) }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>

                {{-- Lọc theo trạng thái --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-44">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">toggle_on</span>
                    <select id="filterActive"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('is_active') === null || request('is_active') === '' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang bật</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Đang tắt</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>

                {{-- Lọc theo HOT --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-36">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">local_fire_department</span>
                    <select id="filterHot"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('is_hot') === null || request('is_hot') === '' ? 'selected' : '' }}>Tất cả mẫu</option>
                        <option value="1" {{ request('is_hot') === '1' ? 'selected' : '' }}>Mẫu HOT</option>
                        <option value="0" {{ request('is_hot') === '0' ? 'selected' : '' }}>Thường</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>

                {{-- Ô tìm kiếm --}}
                <div class="relative flex-1 w-full sm:min-w-[240px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">search</span>
                    <input type="text" id="searchInput" placeholder="Tìm theo tên hoặc mã code..."
                        value="{{ request('search') }}"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        oninput="debounceSearch()">
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0 w-full xl:w-auto">
                <button x-data x-on:click="$dispatch('open-modal', 'create-template')"
                    class="flex items-center justify-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm shadow-primary/25 whitespace-nowrap w-full sm:w-auto">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Thêm mẫu quà
                </button>
            </div>
        </div>

        {{-- 3. Bảng danh sách --}}
        <div class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark overflow-hidden flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-background-dark/50 border-b border-slate-200 dark:border-border-dark">
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-14 text-center">STT</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mã code</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tên mẫu quà tặng</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Danh mục</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Giá & Chiết khấu</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Thống kê</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Hot</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-center">Trạng thái</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right w-24">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-border-dark">
                        @forelse($templates as $tmpl)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors group">
                                <td class="p-4 text-sm text-slate-500 text-center">
                                    {{ $loop->iteration + ($templates->currentPage() - 1) * $templates->perPage() }}
                                </td>
                                <td class="p-4 text-sm font-mono text-slate-500 dark:text-slate-400">
                                    {{ $tmpl->code }}
                                </td>
                                <td class="p-4 text-sm font-semibold text-slate-900 dark:text-white max-w-[250px]">
                                    <div class="flex flex-col">
                                        <span>{{ $tmpl->name }}</span>
                                        @if($tmpl->demo_url)
                                            <a href="{{ $tmpl->demo_url }}" target="_blank" class="text-[11px] text-primary hover:underline flex items-center gap-1 mt-0.5">
                                                <span class="material-symbols-outlined text-[12px]">open_in_new</span> Xem thử Demo
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($tmpl->category)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-background-dark text-slate-800 dark:text-slate-200 border border-slate-200 dark:border-border-dark">
                                            <span class="material-symbols-outlined text-[14px]">{{ $tmpl->category->icon ?? 'category' }}</span>
                                            {{ __($tmpl->category->name) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Không có</span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm">
                                    <div class="flex flex-col">
                                        @if($tmpl->discount > 0)
                                            <span class="font-bold text-slate-900 dark:text-white">
                                                {{ number_format($tmpl->price * (1 - $tmpl->discount / 100)) }} đ
                                            </span>
                                            <span class="text-xs text-slate-400 line-through">
                                                {{ number_format($tmpl->price) }} đ
                                            </span>
                                            <span class="text-[10px] text-rose-500 font-semibold mt-0.5">
                                                Giảm {{ $tmpl->discount }}%
                                            </span>
                                        @else
                                            <span class="font-bold text-slate-900 dark:text-white">
                                                {{ number_format($tmpl->price) }} đ
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-center">
                                    <div class="flex flex-col items-center">
                                        <span class="text-slate-700 dark:text-slate-300 font-medium">Bán: <strong>{{ number_format($tmpl->sold) }}</strong></span>
                                        <span class="text-[11px] text-amber-500 flex items-center gap-0.5 mt-0.5">
                                            <span class="material-symbols-outlined text-[12px] fill-current">star</span>
                                            {{ number_format($tmpl->stars) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 text-center">
                                    @if($tmpl->is_hot)
                                        <span class="inline-flex items-center justify-center p-1 rounded-full bg-rose-500/10 dark:bg-rose-500/20 text-rose-500" title="Mẫu nổi bật">
                                            <span class="material-symbols-outlined text-[18px] fill-current">local_fire_department</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">--</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    {{-- Toggle active --}}
                                    <form action="{{ route('admin.gift-templates.toggle-active', $tmpl) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $tmpl->is_active ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-600' }}"
                                            title="{{ $tmpl->is_active ? 'Đang bật — nhấn để tắt' : 'Đang tắt — nhấn để bật' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm {{ $tmpl->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
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
                                                @click="open = false; $dispatch('open-edit-template', {{ json_encode([
                                                    'id' => $tmpl->id,
                                                    'category_id' => $tmpl->category_id,
                                                    'code' => $tmpl->code,
                                                    'name' => $tmpl->name,
                                                    'description' => $tmpl->description,
                                                    'price' => $tmpl->price,
                                                    'discount' => $tmpl->discount,
                                                    'is_hot' => $tmpl->is_hot,
                                                    'is_active' => $tmpl->is_active,
                                                    'opening_type' => $tmpl->opening_type ?? 'auto_load',
                                                    'form_schema' => $tmpl->form_schema,
                                                    'demo_url' => $tmpl->getRawOriginal('demo_url'),
                                                    'guide_url' => $tmpl->guide_url,
                                                    'video_url' => $tmpl->video_url,
                                                    'meta_title' => $tmpl->meta_title,
                                                    'meta_description' => $tmpl->meta_description,
                                                    'meta_keywords' => $tmpl->meta_keywords
                                                ]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-background-dark/50 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px] text-slate-400">edit</span>
                                                Chỉnh sửa
                                            </button>
                                            <div class="border-t border-slate-200 dark:border-border-dark my-1"></div>
                                            <button
                                                @click="open = false; $dispatch('open-delete-template', {{ json_encode(['id' => $tmpl->id, 'name' => $tmpl->name]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Xóa mẫu quà
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="size-16 rounded-full bg-slate-100 dark:bg-background-dark flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[32px] text-slate-300 dark:text-slate-600">redeem</span>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-sm font-medium">Chưa có mẫu quà tặng nào</p>
                                            <p class="text-slate-400 text-xs mt-1">Hãy thêm các mẫu 3D tương tác tuyệt đẹp đầu tiên.</p>
                                        </div>
                                        <button x-data x-on:click="$dispatch('open-modal', 'create-template')"
                                            class="mt-2 flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            Thêm mẫu quà
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($templates->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-background-dark/30 gap-4">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Hiển thị <span class="font-bold text-slate-900 dark:text-white">{{ $templates->firstItem() }}</span>
                        đến <span class="font-bold text-slate-900 dark:text-white">{{ $templates->lastItem() }}</span>
                        trong <span class="font-bold text-slate-900 dark:text-white">{{ $templates->total() }}</span> mẫu quà tặng
                    </div>
                    <div>
                        {{ $templates->links('pagination::tailwind') }}
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
                const categoryId = document.getElementById('filterCategory').value;
                const isActive = document.getElementById('filterActive').value;
                const isHot = document.getElementById('filterHot').value;
                const search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
                const params = new URLSearchParams(window.location.search);

                categoryId ? params.set('category_id', categoryId) : params.delete('category_id');
                isActive !== '' ? params.set('is_active', isActive) : params.delete('is_active');
                isHot !== '' ? params.set('is_hot', isHot) : params.delete('is_hot');
                search ? params.set('search', search) : params.delete('search');
                params.delete('page');

                window.location.href = '{{ route("admin.gift-templates.index") }}?' + params.toString();
            }
        </script>
    @endpush

    @include('components.pages.admin.gift-templates.gift-templates-crud.gift-template-modals')
</x-admin-layout>
