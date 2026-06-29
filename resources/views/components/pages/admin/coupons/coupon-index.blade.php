<x-admin-layout title="NDHGift - Quản lý mã giảm giá">
    <div class="flex flex-col gap-6">

        {{-- 1. Thẻ thống kê --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng mã giảm giá</p>
                    <span class="material-symbols-outlined text-primary text-[20px]">confirmation_number</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['totalCoupons']) }}</h3>
                    <span class="text-slate-400 text-xs font-medium">mã</span>
                </div>
            </div>
            <div
                class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Đang hoạt động</p>
                    <span class="material-symbols-outlined text-emerald-500 text-[20px]">toggle_on</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['activeCoupons']) }}</h3>
                    <span class="text-slate-400 text-xs font-medium">mã</span>
                </div>
            </div>
            <div
                class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Đã hết hạn</p>
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">schedule</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['expiredCoupons']) }}</h3>
                    <span class="text-slate-400 text-xs font-medium">mã</span>
                </div>
            </div>
            <div
                class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng lượt sử dụng</p>
                    <span class="material-symbols-outlined text-purple-500 text-[20px]">trending_up</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">
                        {{ number_format($stats['totalUsed']) }}</h3>
                    <span class="text-slate-400 text-xs font-medium">lượt</span>
                </div>
            </div>
        </div>

        {{-- 2. Thanh lọc & công cụ --}}
        <div
            class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white/80 dark:bg-surface-dark border border-slate-200 dark:border-border-dark p-4 rounded-xl backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row flex-wrap items-center gap-2 w-full xl:w-auto flex-1">
                {{-- Lọc theo loại --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-40">
                    <span
                        class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">category</span>
                    <select id="filterType"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('type') == '' ? 'selected' : '' }}>Tất cả loại</option>
                        <option value="percent" {{ request('type') == 'percent' ? 'selected' : '' }}>Phần trăm</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Cố định</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Lọc theo trạng thái --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-44">
                    <span
                        class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">toggle_on</span>
                    <select id="filterActive"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('is_active') === null || request('is_active') === '' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Đã tắt</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Lọc theo phạm vi --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-40">
                    <span
                        class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">visibility</span>
                    <select id="filterStatus"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>Tất cả phạm vi</option>
                        <option value="public" {{ request('status') == 'public' ? 'selected' : '' }}>Công khai</option>
                        <option value="private" {{ request('status') == 'private' ? 'selected' : '' }}>Riêng tư</option>
                    </select>
                    <span
                        class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Ô tìm kiếm --}}
                <div class="relative flex-1 w-full sm:min-w-[180px]">
                    <span
                        class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">search</span>
                    <input type="text" id="searchInput" placeholder="Tìm theo mã code..."
                        value="{{ request('search') }}"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        oninput="debounceSearch()">
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button x-data x-on:click="$dispatch('open-modal', 'create-coupon')"
                    class="flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm shadow-primary/25 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tạo mã mới
                </button>
            </div>
        </div>

        {{-- 3. Bảng danh sách Coupons --}}
        <div
            class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark overflow-hidden flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-slate-50 dark:bg-background-dark/50 border-b border-slate-200 dark:border-border-dark">
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">
                                STT</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Mã code</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Loại</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Giá trị</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Lượt sử dụng</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Hiệu lực</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Phạm vi</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                Trạng thái</th>
                            <th
                                class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">
                                Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-border-dark">
                        @forelse($coupons as $coupon)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors group">
                                <td class="p-4 text-sm text-slate-500 text-center">
                                    {{ $loop->iteration + ($coupons->currentPage() - 1) * $coupons->perPage() }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2" x-data="{ copied: false }">
                                        <code
                                            class="text-sm font-mono font-bold text-primary bg-primary/5 px-2 py-0.5 rounded">{{ $coupon->code }}</code>
                                        <button
                                            @click="navigator.clipboard.writeText('{{ $coupon->code }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                            class="p-1 text-slate-400 hover:text-primary transition-colors"
                                            title="Sao chép">
                                            <span class="material-symbols-outlined text-[16px]"
                                                x-text="copied ? 'check' : 'content_copy'"></span>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($coupon->type === 'percent')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                            <span class="material-symbols-outlined text-[14px]">percent</span> Phần trăm
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                            <span class="material-symbols-outlined text-[14px]">payments</span> Cố định
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4 text-sm text-slate-900 dark:text-white font-medium">
                                    @if($coupon->type === 'percent')
                                        {{ number_format((float) $coupon->value, 0) }}%
                                        @if($coupon->max_discount)
                                            <span class="text-slate-400 text-xs">(tối đa
                                                {{ number_format((float) $coupon->max_discount, 0, ',', '.') }}đ)</span>
                                        @endif
                                    @else
                                        {{ number_format((float) $coupon->value, 0, ',', '.') }}đ
                                    @endif
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-sm text-slate-700 dark:text-slate-300 font-medium">{{ $coupon->used_count }}</span>
                                            <span class="text-slate-400 text-xs">/</span>
                                            <span class="text-sm text-slate-400">{{ $coupon->max_uses ?? '∞' }}</span>
                                        </div>
                                        @if($coupon->max_uses)
                                            @php $usagePercent = min(100, ($coupon->used_count / $coupon->max_uses) * 100); @endphp
                                            <div
                                                class="w-20 bg-slate-200 dark:bg-background-dark h-1.5 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all {{ $usagePercent >= 90 ? 'bg-rose-500' : ($usagePercent >= 50 ? 'bg-amber-500' : 'bg-primary') }}"
                                                    style="width: {{ $usagePercent }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-500">
                                    <div class="flex flex-col gap-0.5">
                                        @if($coupon->starts_at)
                                            <span class="text-xs">{{ $coupon->starts_at->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-xs text-slate-300">--</span>
                                        @endif
                                        <span class="text-slate-300 dark:text-slate-600 text-[10px]">→</span>
                                        @if($coupon->expires_at)
                                            <span
                                                class="text-xs {{ $coupon->expires_at->isPast() ? 'text-rose-500 font-medium' : '' }}">
                                                {{ $coupon->expires_at->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-300">Vĩnh viễn</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4">
                                    @if($coupon->status === 'public')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-sky-500/10 text-sky-500">
                                            <span class="material-symbols-outlined text-[14px]">public</span> Công khai
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-slate-500/10 text-slate-400">
                                            <span class="material-symbols-outlined text-[14px]">lock</span> Riêng tư
                                        </span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    {{-- Toggle switch --}}
                                    <form action="{{ route('admin.coupons.toggle-active', $coupon) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $coupon->is_active ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-600' }}"
                                            title="{{ $coupon->is_active ? 'Đang bật — nhấn để tắt' : 'Đang tắt — nhấn để bật' }}">
                                            <span
                                                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform shadow-sm {{ $coupon->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="p-4 text-right">
                                    <div x-data="{ open: false }" class="relative inline-block">
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
                                                @click="open = false; $dispatch('open-edit-coupon', {{ json_encode(['id' => $coupon->id, 'code' => $coupon->code, 'type' => $coupon->type, 'value' => $coupon->value, 'max_discount' => $coupon->max_discount, 'min_order' => $coupon->min_order, 'max_uses' => $coupon->max_uses, 'starts_at' => $coupon->starts_at?->format('Y-m-d\TH:i'), 'expires_at' => $coupon->expires_at?->format('Y-m-d\TH:i'), 'is_active' => $coupon->is_active, 'status' => $coupon->status]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-background-dark/50 transition-colors text-left">
                                                <span
                                                    class="material-symbols-outlined text-[18px] text-slate-400">edit</span>
                                                Chỉnh sửa
                                            </button>
                                            <div class="border-t border-slate-200 dark:border-border-dark my-1"></div>
                                            <button
                                                @click="open = false; $dispatch('open-delete-coupon', {{ json_encode(['id' => $coupon->id, 'code' => $coupon->code]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Xóa mã
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="p-12 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div
                                            class="size-16 rounded-full bg-slate-100 dark:bg-background-dark flex items-center justify-center">
                                            <span
                                                class="material-symbols-outlined text-[32px] text-slate-300 dark:text-slate-600">confirmation_number</span>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-sm font-medium">Chưa có mã giảm giá nào</p>
                                            <p class="text-slate-400 text-xs mt-1">Tạo mã giảm giá đầu tiên cho hệ thống.
                                            </p>
                                        </div>
                                        <button x-data x-on:click="$dispatch('open-modal', 'create-coupon')"
                                            class="mt-2 flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            Tạo mã mới
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($coupons->hasPages())
                <div
                    class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-background-dark/30 gap-4">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Hiển thị <span class="font-bold text-slate-900 dark:text-white">{{ $coupons->firstItem() }}</span>
                        đến <span class="font-bold text-slate-900 dark:text-white">{{ $coupons->lastItem() }}</span>
                        trong <span class="font-bold text-slate-900 dark:text-white">{{ $coupons->total() }}</span> mã
                    </div>
                    <div>
                        {{ $coupons->links('pagination::tailwind') }}
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
                const type = document.getElementById('filterType').value;
                const isActive = document.getElementById('filterActive').value;
                const status = document.getElementById('filterStatus').value;
                const search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
                const params = new URLSearchParams(window.location.search);

                type ? params.set('type', type) : params.delete('type');
                isActive !== '' ? params.set('is_active', isActive) : params.delete('is_active');
                status ? params.set('status', status) : params.delete('status');
                search ? params.set('search', search) : params.delete('search');
                params.delete('page');

                window.location.href = '{{ route("admin.coupons.index") }}?' + params.toString();
            }
        </script>
    @endpush

    @include('components.pages.admin.coupons.coupons-crud.coupon-modals')
</x-admin-layout>