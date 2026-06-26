<x-app-layout :title="__('Profile - NDHGift')">
    {{-- Trigger thông báo từ session thông qua sự kiện toast của hệ thống --}}
    @if(session('success'))
        <div x-data
            x-init="window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: '{{ __('Success') }}', message: '{{ session('success') }}' } }))">
        </div>
    @endif
    @if($errors->any())
        <div x-data
            x-init="window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: '{{ __('Error') }}', message: '{{ $errors->first() }}' } }))">
        </div>
    @endif

    <div x-data="{
        activeAction: new URLSearchParams(window.location.search).get('action') || 'menu',
        setActiveAction(action) {
            this.activeAction = action;
            const url = new URL(window.location);
            if (action === 'menu') {
                url.searchParams.delete('action');
            } else {
                url.searchParams.set('action', action);
            }
            window.history.pushState({}, '', url);
        },
        init() {
            window.addEventListener('popstate', () => {
                this.activeAction = new URLSearchParams(window.location.search).get('action') || 'menu';
            });
        }
    }" class="flex flex-col gap-6">

        {{-- Header tĩnh chuẩn của trang Profile --}}
        <div class="flex items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold text-app-text">{{ __('Profile') }}</h1>
                <p class="text-app-muted text-sm">{{ __('Update personal information and display settings') }}</p>
            </div>
            <!-- Breadcrumbs chỉ hiển thị trên desktop -->
            <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
                <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                    class="hover:text-primary transition-colors">
                    NDHGift
                </a>
                <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
                <span class="text-app-text">{{ __('Profile') }}</span>
            </nav>
        </div>

        @auth
            {{-- Tab Hồ sơ: Avatar + Thông tin cơ bản + Form chức năng --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Cột trái: Avatar + Thông tin cơ bản --}}
                <div class="lg:col-span-1 flex flex-col gap-4">
                    <div class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col items-center gap-4">
                        <div class="relative group cursor-pointer" x-data="{
                                                                                                        avatarPreview: '{{ $user->avatar_url }}',
                                                                                                        loading: false,
                                                                                                        handleFileChange(event) {
                                                                                                            const file = event.target.files[0];
                                                                                                            if (file) {
                                                                                                                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                                                                                                                if (!validTypes.includes(file.type)) {
                                                                                                                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: '{{ __('Error') }}', message: '{{ __('Please select a valid image file (jpeg, png, jpg, gif).') }}' } }));
                                                                                                                    event.target.value = '';
                                                                                                                    return;
                                                                                                                }
                                                                                                                if (file.size > 2 * 1024 * 1024) {
                                                                                                                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: '{{ __('Error') }}', message: '{{ __('Image size must not exceed 2MB.') }}' } }));
                                                                                                                    event.target.value = '';
                                                                                                                    return;
                                                                                                                }

                                                                                                                this.loading = true;
                                                                                                                this.avatarPreview = URL.createObjectURL(file);
                                                                                                                // Tự động submit form sau khi chọn ảnh
                                                                                                                document.getElementById('profile-form').submit();
                                                                                                            }
                                                                                                        }
                                                                                                    }" x-init="() => {
                                                                                                        @error('avatar_file')
                                                                                                            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: '{{ __('Upload Error') }}', message: @js($message) } }));
                                                                                                        @enderror
                                                                                                    }">
                            <div @click="$dispatch('open-modal', 'avatar-modal')"
                                class="size-24 rounded-full overflow-hidden border-4 border-app-border relative hover:border-primary/50 transition-colors">
                                <template x-if="avatarPreview">
                                    <img :src="avatarPreview" alt="Avatar" class="size-full object-cover"
                                        :class="{'opacity-50': loading}">
                                </template>
                                <template x-if="!avatarPreview">
                                    <div class="size-full bg-primary/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-primary text-4xl">person</span>
                                    </div>
                                </template>

                                {{-- Loading Spinner --}}
                                <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-black/20">
                                    <span class="material-symbols-outlined text-white animate-spin">refresh</span>
                                </div>
                            </div>

                            <input type="file" name="avatar_file" form="profile-form" accept="image/*" class="hidden"
                                x-ref="avatarInput" @change="handleFileChange">

                            {{-- Modal phóng to ảnh đại diện sử dụng component hệ thống --}}
                            <x-shared.ui.modal name="avatar-modal" maxWidth="lg">
                                <div class="relative p-6 flex flex-col items-center gap-6">
                                    <h4 class="text-xl font-bold text-app-text">{{ __('Profile Picture') }}</h4>
                                    {{-- Nút Đóng (dấu X) ở bên phải --}}
                                    <button type="button" @click="$dispatch('close-modal', 'avatar-modal')"
                                        class="absolute top-5 right-5 text-gray-400 hover:text-white hover:scale-110 active:scale-95 transition-all flex items-center justify-center size-9">
                                        <span class="material-symbols-outlined text-[24px]">close</span>
                                    </button>

                                    {{-- Khung ảnh --}}
                                    <div
                                        class="w-full rounded-2xl overflow-hidden border border-gray-700/60 bg-[#0f172a] flex items-center justify-center aspect-square max-h-[40vh]">
                                        <template x-if="avatarPreview">
                                            <img :src="avatarPreview" alt="Avatar Full" class="size-full object-contain">
                                        </template>
                                        <template x-if="!avatarPreview">
                                            <div class="size-full flex items-center justify-center bg-[#0f172a]">
                                                <span
                                                    class="material-symbols-outlined text-[#f97316] text-7xl">person</span>
                                            </div>
                                        </template>
                                    </div>

                                    {{-- Nút thay đổi ở dưới cùng ảnh --}}
                                    <button type="button"
                                        @click="$refs.avatarInput.click(); $dispatch('close-modal', 'avatar-modal')"
                                        class="h-11 px-8 bg-primary hover:bg-primary/90 text-white font-bold text-sm rounded-xl transition-all shadow-lg shadow-orange-500/20 active:scale-[0.97] flex items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                                        {{ __('Change avatar') }}
                                    </button>
                                </div>
                            </x-shared.ui.modal>
                        </div>
                        <div class="text-center">
                            <h3 class="text-base font-bold text-app-text">{{ $user->fullname }}</h3>
                            <p class="text-sm text-app-muted mt-0.5">{{ $user->email }}</p>
                        </div>
                        @php
                            $leftProgress = app(\App\Services\UserLevelService::class)->calculateProgress($user);
                            $leftTierConfig = app(\App\Services\UserLevelService::class)->getTierBenefits($user->current_tier);
                        @endphp
                        <div class="flex flex-col items-center gap-1.5 w-full">
                            <span
                                class="px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider flex items-center gap-1"
                                style="background-color: {{ $leftTierConfig['color'] }}15; color: {{ $leftTierConfig['color'] }}">
                                <span>{{ $leftTierConfig['icon'] }}</span>
                                <span>{{ $leftTierConfig['label'] }}</span>
                                @if($user->is_tier_frozen)
                                    <span class="material-symbols-outlined text-[12px] animate-pulse"
                                        title="Tài khoản bị đóng băng">ac_unit</span>
                                @endif
                            </span>

                            {{-- Thanh XP nhỏ gọn --}}
                            @if(!$leftProgress['is_max'])
                                <div class="w-2/3 flex flex-col gap-1 mt-1">
                                    <div class="w-full h-1.5 bg-app-main border border-app-border rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-400 rounded-full"
                                            style="width: {{ $leftProgress['percent'] }}%"></div>
                                    </div>
                                    <span class="text-[9px] text-app-muted text-center">{{ number_format($user->current_xp) }} /
                                        {{ number_format($leftProgress['next_tier_xp']) }} XP</span>
                                </div>
                            @else
                                <span class="text-[9px] text-green-500 font-bold tracking-wider uppercase mt-1">MAX LEVEL</span>
                            @endif
                        </div>
                        <div class="w-full flex flex-col gap-2 pt-2 border-t border-app-border">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-app-muted">{{ __('Balance') }}</span>
                                <span
                                    class="text-app-text font-semibold">{{ number_format($user->balance, 0, ',', '.') }}đ</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-app-muted">{{ __('Account ID') }}</span>
                                <div class="flex items-center gap-1.5 notranslate" translate="no" x-data="{ 
                                                                                    copied: false,
                                                                                    copyId() {
                                                                                        navigator.clipboard.writeText('{{ $user->unitcode }}');
                                                                                        this.copied = true;
                                                                                        setTimeout(() => this.copied = false, 2000);
                                                                                    }
                                                                                }">
                                    <span
                                        class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary select-all">{{ $user->unitcode }}</span>
                                    <button @click="copyId"
                                        class="text-app-muted hover:text-primary transition-colors flex items-center justify-center outline-none focus:outline-none shrink-0"
                                        title="{{ __('Copy Account ID') }}">
                                        <span class="material-symbols-outlined text-[16px] select-none"
                                            x-text="copied ? 'check' : 'content_copy'"></span>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-app-muted">{{ __('Created Date') }}</span>
                                <span class="text-app-text">{{ $user->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- 3 Button chức năng nhanh dưới Avatar --}}
                    <div class="grid grid-cols-3 gap-4">
                        {{-- Nút đổi quà--}}
                        <a href="{{ route('app.billing', ['tab' => 'coupons']) }}"
                            class="flex flex-col items-center justify-center p-4 bg-app-surface border border-app-border rounded-xl hover:border-primary/50 hover:bg-primary/5 transition-all duration-300 group shadow-sm">
                            <span
                                class="material-symbols-outlined text-[26px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                redeem
                            </span>
                            <span
                                class="text-[11px] font-semibold text-app-text mt-1.5 group-hover:text-primary transition-colors duration-300">
                                {{ __('Redeem Voucher') }}
                            </span>
                        </a>

                        {{-- Nút lịch sử --}}
                        <a href="{{ route('app.history.index', ['locale' => app()->getLocale()]) }}"
                            class="flex flex-col items-center justify-center p-4 bg-app-surface border border-app-border rounded-xl hover:border-primary/50 hover:bg-primary/5 transition-all duration-300 group shadow-sm">
                            <span
                                class="material-symbols-outlined text-[26px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                history
                            </span>
                            <span
                                class="text-[11px] font-semibold text-app-text mt-1.5 group-hover:text-primary transition-colors duration-300">
                                {{ __('History') }}
                            </span>
                        </a>

                        {{-- Nút đăng xuất --}}
                        <button type="button" @click="$dispatch('open-modal', 'logout-confirm-modal')"
                            class="w-full flex flex-col items-center justify-center p-4 bg-app-surface border border-app-border rounded-xl hover:border-red-500/50 hover:bg-red-500/5 transition-all duration-300 group shadow-sm outline-none">
                            <span
                                class="material-symbols-outlined text-[26px] text-app-muted group-hover:text-red-500 transition-colors duration-300">
                                logout
                            </span>
                            <span
                                class="text-[11px] font-semibold text-app-text mt-1.5 group-hover:text-red-500 transition-colors duration-300">
                                {{ __('Log Out') }}
                            </span>
                        </button>

                        {{-- Form đăng xuất ẩn --}}
                        <form id="logout-form" method="POST"
                            action="{{ route('logout', ['locale' => app()->getLocale()]) }}" class="hidden">
                            @csrf
                        </form>

                        {{-- Modal xác nhận đăng xuất --}}
                        <x-shared.ui.modal name="logout-confirm-modal" maxWidth="md">
                            <div class="relative p-6 flex flex-col items-center gap-4 text-center">
                                {{-- Icon cảnh báo --}}
                                <div
                                    class="size-12 rounded-full bg-red-500/10 flex items-center justify-center text-red-500">
                                    <span class="material-symbols-outlined text-[28px]">logout</span>
                                </div>

                                <div>
                                    <h4 class="text-lg font-bold text-app-text">{{ __('Confirm Log Out') }}</h4>
                                    <p class="text-sm text-app-muted mt-1.5">
                                        {{ __('Are you sure you want to log out of your current NDHGift account?') }}
                                    </p>
                                </div>

                                {{-- Nút hành động --}}
                                <div class="flex items-center gap-3 w-full mt-2">
                                    <button type="button" @click="$dispatch('close-modal', 'logout-confirm-modal')"
                                        class="flex-1 h-10 px-4 bg-app-main border border-app-border text-app-text hover:bg-primary/5 hover:border-primary/30 font-semibold text-sm rounded-xl transition-all active:scale-[0.98]">
                                        {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" form="logout-form"
                                        class="flex-1 h-10 px-4 bg-red-500 hover:bg-red-600 text-white font-semibold text-sm rounded-xl transition-all shadow-lg shadow-red-500/15 active:scale-[0.98]">
                                        {{ __('Log Out') }}
                                    </button>
                                </div>
                            </div>
                        </x-shared.ui.modal>

                    </div>
                </div>

                {{-- Cột phải: Menu chức năng --}}
                <div class="lg:col-span-2">
                    {{-- Menu chính --}}
                    <div x-show="activeAction === 'menu'"
                        class="bg-app-surface border border-app-border rounded-xl overflow-hidden divide-y divide-app-border shadow-sm animate-fade-in">
                        <div class="px-6 py-4">
                            <h1 class="text-lg font-bold text-app-text">{{ __('Function List') }}</h1>
                            <p class="text-sm text-app-muted mt-0.5">{{ __('Summary of functions related to account') }}</p>
                        </div>

                        {{-- Chỉnh sửa thông tin --}}
                        <a href="#" @click.prevent="setActiveAction('edit-profile')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    manage_accounts
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Edit Profile') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>

                        {{-- Hóa đơn --}}
                        <a href="#" @click.prevent="setActiveAction('billing')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    receipt_long
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Billing') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>

                        {{-- Cấp bậc & Affiliate --}}
                        <a href="#" @click.prevent="setActiveAction('level-affiliate')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    military_tech
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Level & Affiliate') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>

                        {{-- Cài đặt --}}
                        <a href="#" @click.prevent="setActiveAction('setting')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    settings
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Settings') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>

                        {{-- Điều khoản và dịch vụ --}}
                        <a href="#" @click.prevent="setActiveAction('term-and-service')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    menu_book
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Terms & Services') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>

                        {{-- Chính sách quyền riêng tư --}}
                        <a href="#" @click.prevent="setActiveAction('privacy-policy')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    description
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Privacy Policy') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>

                        {{-- Trợ giúp --}}
                        <a href="#" @click.prevent="setActiveAction('help')"
                            class="flex items-center justify-between px-6 py-5 hover:bg-primary/5 transition-all duration-300 group">
                            <div class="flex items-center gap-4">
                                <span
                                    class="material-symbols-outlined text-[24px] text-app-muted group-hover:text-primary transition-colors duration-300">
                                    help
                                </span>
                                <span
                                    class="text-[15px] font-semibold text-app-text group-hover:text-primary transition-colors duration-300">
                                    {{ __('Help') }}
                                </span>
                            </div>
                            <span
                                class="material-symbols-outlined text-[22px] text-app-muted group-hover:text-primary group-hover:translate-x-1 transition-all duration-300">
                                chevron_right
                            </span>
                        </a>
                    </div>

                    {{-- Form Chỉnh sửa thông tin --}}
                    <div x-show="activeAction === 'edit-profile'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.edit-profile.edit-profile')
                    </div>

                    {{-- Hóa đơn --}}
                    <div x-show="activeAction === 'billing'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.billing.billing')
                    </div>

                    {{-- Cấp bậc & Affiliate --}}
                    <div x-show="activeAction === 'level-affiliate'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.level-affiliate.level-affiliate')
                    </div>

                    {{-- Cài đặt --}}
                    <div x-show="activeAction === 'setting'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.setting.setting')
                    </div>

                    {{-- Điều khoản và dịch vụ --}}
                    <div x-show="activeAction === 'term-and-service'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.term-and-service.term-and-service')
                    </div>

                    {{-- Chính sách quyền riêng tư --}}
                    <div x-show="activeAction === 'privacy-policy'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.privacy-policy.privacy-policy')
                    </div>

                    {{-- Trợ giúp --}}
                    <div x-show="activeAction === 'help'" x-cloak class="animate-fade-in">
                        @include('components.pages.app.profile.profile-menu.help.help')
                    </div>
                </div>
            </div>
        @else
            {{-- Hộp mời đăng nhập dành cho khách chưa xác thực --}}
            <div
                class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col items-center gap-5 text-center shadow-sm animate-fade-in max-w-md mx-auto my-8">
                <div class="size-20 rounded-full bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-4xl">account_circle</span>
                </div>
                <div class="space-y-1.5">
                    <h3 class="text-lg font-bold text-app-text">{{ __('Welcome Customer') }}</h3>
                    <p class="text-sm text-app-muted leading-relaxed">
                        {{ __('Please log in or register a new account to view your personal profile and use NDHGift services.') }}
                    </p>
                </div>
                <div class="flex items-center gap-4 w-full mt-2">
                    {{-- Nút Đăng nhập --}}
                    <a href="{{ route('login', ['locale' => app()->getLocale()]) }}"
                        class="flex-1 h-11 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 flex items-center justify-center gap-2 active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">login</span>
                        {{ __('Log in') }}
                    </a>

                    {{-- Nút Đăng ký --}}
                    <a href="{{ route('register', ['locale' => app()->getLocale()]) }}"
                        class="flex-1 h-11 bg-app-surface border border-app-border hover:border-primary/50 hover:bg-primary/5 text-app-text font-semibold text-sm rounded-xl transition-all shadow-sm flex items-center justify-center gap-2 active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">person_add</span>
                        {{ __('Register') }}
                    </a>
                </div>
            </div>
        @endauth
    </div>
</x-app-layout>