<x-admin-layout title="NDHGift - Quản lý người dùng">
    <div class="flex flex-col gap-6">

        {{-- 1. Thẻ thống kê — Thiết kế phẳng đồng bộ với dashboard --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Tổng thành viên --}}
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng thành viên</p>
                    <span class="material-symbols-outlined text-primary text-[20px]">group</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">{{ number_format($stats['totalUsers']) }}</h3>
                    <span class="text-slate-400 text-xs font-medium">người</span>
                </div>
            </div>
            {{-- Tổng số dư --}}
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng số dư ví</p>
                    <span class="material-symbols-outlined text-emerald-500 text-[20px]">account_balance_wallet</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">{{ number_format($stats['totalBalance'], 0, ',', '.') }}</h3>
                    <span class="text-slate-400 text-xs font-medium">VND</span>
                </div>
            </div>
            {{-- Admin vs User --}}
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Phân bổ vai trò</p>
                    <span class="material-symbols-outlined text-purple-500 text-[20px]">shield_person</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">{{ $stats['adminCount'] }}</h3>
                    <span class="text-slate-400 text-xs font-medium">admin</span>
                    <span class="text-slate-300 dark:text-slate-600 text-sm">/</span>
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">{{ $stats['userCount'] }}</h3>
                    <span class="text-slate-400 text-xs font-medium">user</span>
                </div>
            </div>
            {{-- Tài khoản bị khóa --}}
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tài khoản bị khóa</p>
                    <span class="material-symbols-outlined text-rose-500 text-[20px]">lock</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">{{ number_format($stats['suspendedCount']) }}</h3>
                    <span class="text-slate-400 text-xs font-medium">tài khoản</span>
                </div>
            </div>
        </div>

        {{-- 2. Thanh lọc & công cụ --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white/80 dark:bg-surface-dark border border-slate-200 dark:border-border-dark p-4 rounded-xl backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row flex-wrap items-center gap-2 w-full xl:w-auto flex-1">
                {{-- Lọc theo trạng thái --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-44">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">filter_list</span>
                    <select id="filterStatus"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Bị khóa</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Lọc theo vai trò --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-44">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">badge</span>
                    <select id="filterRole"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors"
                        onchange="applyFilters()">
                        <option value="" {{ request('role') == '' ? 'selected' : '' }}>Tất cả vai trò</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Người dùng</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Ô tìm kiếm --}}
                <div class="relative flex-1 w-full sm:min-w-[200px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">search</span>
                    <input type="text" id="searchInput" placeholder="Tìm theo tên, email, mã..."
                        value="{{ request('search') }}"
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors"
                        oninput="debounceSearch()">
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button x-data x-on:click="$dispatch('open-modal', 'create-user')"
                    class="flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm shadow-primary/25 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Thêm mới
                </button>
                <button
                    class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors whitespace-nowrap cursor-default opacity-60"
                    title="Sắp ra mắt" disabled>
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Xuất dữ liệu
                </button>
            </div>
        </div>

        {{-- 3. Bảng danh sách Users --}}
        <div class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark overflow-hidden flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-background-dark/50 border-b border-slate-200 dark:border-border-dark">
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">STT</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Người dùng</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Email</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Trạng thái</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Vai trò</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Số dư</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cấp bậc</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Đăng nhập cuối</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-border-dark">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors group relative">
                                {{-- Thanh accent khi hover --}}
                                <td class="p-4 text-sm text-slate-500 text-center">
                                    {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        @if($user->getRawOriginal('avatar_url'))
                                            <div class="size-9 rounded-full bg-slate-200 dark:bg-slate-700 bg-center bg-cover border border-slate-200 dark:border-border-dark flex-shrink-0"
                                                style="background-image: url('{{ $user->avatar_url }}');">
                                            </div>
                                        @else
                                            <div class="size-9 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">
                                                {{ strtoupper(mb_substr($user->fullname, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-slate-900 dark:text-white text-sm font-medium truncate">{{ $user->fullname }}</p>
                                            <p class="text-slate-400 text-xs truncate">{{ '@' . $user->username }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-400">{{ $user->email }}</td>
                                <td class="p-4">
                                    @php
                                        $statusConfig = match ($user->status) {
                                            'active' => [
                                                'badge' => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
                                                'dot' => 'bg-emerald-500',
                                                'label' => 'Hoạt động',
                                            ],
                                            'suspended' => [
                                                'badge' => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
                                                'dot' => 'bg-rose-500',
                                                'label' => 'Bị khóa',
                                            ],
                                            default => [
                                                'badge' => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                                'dot' => 'bg-slate-500',
                                                'label' => ucfirst($user->status ?? 'Không xác định'),
                                            ],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusConfig['badge'] }} border">
                                        <span class="size-1.5 rounded-full {{ $statusConfig['dot'] }}"></span>
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        @if($user->role === 'admin')
                                            <span class="material-symbols-outlined text-purple-400 text-[18px]">shield_person</span>
                                            <span class="text-sm text-slate-900 dark:text-white font-medium">Admin</span>
                                        @else
                                            <span class="material-symbols-outlined text-blue-400 text-[18px]">person</span>
                                            <span class="text-sm text-slate-600 dark:text-slate-400">User</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-4 text-sm text-slate-600 dark:text-slate-400 font-mono">
                                    {{ number_format((float) $user->balance, 0, ',', '.') }}đ
                                </td>
                                <td class="p-4">
                                    @php
                                        $tierConfig = match ($user->current_tier) {
                                            'bronze' => ['color' => 'text-amber-700 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-400', 'label' => 'Đồng'],
                                            'silver' => ['color' => 'text-slate-500 bg-slate-100 dark:bg-slate-700/50 dark:text-slate-300', 'label' => 'Bạc'],
                                            'gold' => ['color' => 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/30 dark:text-yellow-400', 'label' => 'Vàng'],
                                            'platinum' => ['color' => 'text-cyan-600 bg-cyan-100 dark:bg-cyan-900/30 dark:text-cyan-400', 'label' => 'Bạch Kim'],
                                            'diamond' => ['color' => 'text-violet-600 bg-violet-100 dark:bg-violet-900/30 dark:text-violet-400', 'label' => 'Kim Cương'],
                                            default => ['color' => 'text-slate-400 bg-slate-100 dark:bg-slate-800 dark:text-slate-500', 'label' => 'Đồng'],
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $tierConfig['color'] }}">
                                        {{ $tierConfig['label'] }}
                                    </span>
                                </td>
                                <td class="p-4 text-sm text-slate-500">
                                    {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Chưa đăng nhập' }}
                                </td>
                                <td class="p-4 text-right">
                                    {{-- Action Dropdown --}}
                                    <div x-data="{ open: false }" class="relative inline-block">
                                        <button @click="open = !open" @click.away="open = false"
                                            class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                        </button>
                                        <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute right-0 mt-1 w-48 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-xl shadow-xl z-30 py-1 origin-top-right" x-cloak>
                                            <button @click="open = false; $dispatch('open-edit-user', {{ json_encode(['id' => $user->id, 'username' => $user->username, 'fullname' => $user->fullname, 'email' => $user->email, 'phone' => $user->phone, 'status' => $user->status, 'role' => $user->role, 'balance' => $user->balance]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-background-dark/50 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px] text-slate-400">edit</span>
                                                Chỉnh sửa
                                            </button>
                                            <button @click="open = false; $dispatch('open-toggle-status', {{ json_encode(['id' => $user->id, 'fullname' => $user->fullname, 'status' => $user->status]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-background-dark/50 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px] text-slate-400">{{ $user->status === 'active' ? 'lock' : 'lock_open' }}</span>
                                                {{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa' }}
                                            </button>
                                            <div class="border-t border-slate-200 dark:border-border-dark my-1"></div>
                                            <button @click="open = false; $dispatch('open-delete-user', {{ json_encode(['id' => $user->id, 'fullname' => $user->fullname]) }})"
                                                class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition-colors text-left">
                                                <span class="material-symbols-outlined text-[18px]">delete</span>
                                                Xóa người dùng
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
                                            <span class="material-symbols-outlined text-[32px] text-slate-300 dark:text-slate-600">group_off</span>
                                        </div>
                                        <div>
                                            <p class="text-slate-500 text-sm font-medium">Không tìm thấy người dùng nào</p>
                                            <p class="text-slate-400 text-xs mt-1">Thử thay đổi bộ lọc hoặc thêm người dùng mới.</p>
                                        </div>
                                        <button x-data x-on:click="$dispatch('open-modal', 'create-user')"
                                            class="mt-2 flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            Thêm người dùng
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($users->hasPages())
                <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-background-dark/30 gap-4">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        Hiển thị <span class="font-bold text-slate-900 dark:text-white">{{ $users->firstItem() }}</span>
                        đến <span class="font-bold text-slate-900 dark:text-white">{{ $users->lastItem() }}</span>
                        trong <span class="font-bold text-slate-900 dark:text-white">{{ $users->total() }}</span> người dùng
                    </div>
                    <div>
                        {{ $users->links('pagination::tailwind') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Script xử lý filter --}}
    @push('scripts')
    <script>
        let searchTimeout = null;

        // Giữ focus vào ô search sau khi reload trang
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
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        }

        function applyFilters() {
            const status = document.getElementById('filterStatus').value;
            const role = document.getElementById('filterRole').value;
            const search = document.getElementById('searchInput') ? document.getElementById('searchInput').value : '';
            const params = new URLSearchParams(window.location.search);

            status ? params.set('status', status) : params.delete('status');
            role ? params.set('role', role) : params.delete('role');
            search ? params.set('search', search) : params.delete('search');
            params.delete('page');

            window.location.href = '{{ route("admin.users.index") }}?' + params.toString();
        }
    </script>
    @endpush

    {{-- Modal CRUD --}}
    @include('components.pages.admin.users.users-crud.user-modals')
</x-admin-layout>
