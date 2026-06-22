<x-app-layout :title="__('Billing & Wallet - NDHGift')">
    <div class="w-full" 
         x-data="{ 
            activeTab: '{{ request()->query('tab', 'overview') }}',
            switchTab(tabName) {
                this.activeTab = tabName;
                const url = new URL(window.location);
                url.searchParams.set('tab', tabName);
                window.history.replaceState(null, '', url.toString());
            }
         }">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-2">
                <h1 class="text-xl sm:text-2xl font-bold text-app-text flex items-center gap-2">
                    {{ __('Wallet & Billing') }}
                    <span class="material-symbols-outlined text-app-muted text-base sm:text-lg cursor-help"
                        title="{{ __('Manage your wallet balance, redeem gift codes and view your transaction history.') }}">help</span>
                </h1>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('app.topup') }}"
                    class="h-9 px-4 rounded-lg bg-primary hover:bg-primary/90 text-white font-medium text-sm flex items-center gap-1.5 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>{{ __('Top Up Wallet') }}</span>
                </a>
            </div>
        </div>

        {{-- Grid Thống kê --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
            {{-- Số dư hiện tại --}}
            <div class="bg-app-surface border border-app-border rounded-xl p-5 shadow-sm">
                <p class="text-xs sm:text-sm text-app-muted mb-1 font-medium">{{ __('Current Balance') }}</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-app-text">
                    {{ number_format(auth()->user()->balance ?? 0, 0, ',', '.') }} <span class="text-lg">VND</span>
                </h2>
            </div>

            {{-- Mã quà tặng khả dụng --}}
            <div class="bg-app-surface border border-app-border rounded-xl p-5 shadow-sm">
                <p class="text-xs sm:text-sm text-app-muted mb-1 font-medium">{{ __('Coupons/Gift Codes') }}</p>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-app-text">
                    {{ $publicCouponsCount }} <span class="text-lg">{{ __('codes available') }}</span>
                </h2>
            </div>

            {{-- Mã tài khoản định danh --}}
            <div class="bg-app-surface border border-app-border rounded-xl p-5 shadow-sm">
                <p class="text-xs sm:text-sm text-app-muted mb-1 font-medium font-mono">{{ __('Identifier Unit Code') }}</p>
                <h2 class="text-xl sm:text-2xl font-extrabold text-app-text font-mono truncate">
                    {{ auth()->user()->unitcode ?? 'N/A' }}
                </h2>
            </div>
        </div>

        {{-- Tabs Area --}}
        <div class="bg-app-main border border-app-border rounded-xl overflow-hidden shadow-sm">
            {{-- Tab Menu --}}
            <div class="flex items-center gap-1 sm:gap-2 px-2 sm:px-4 pt-2 border-b border-app-border overflow-x-auto custom-scrollbar">
                <button type="button" @click="switchTab('overview')"
                    class="px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-colors whitespace-nowrap flex items-center gap-2"
                    :class="activeTab === 'overview' ? 'border-primary text-primary' : 'border-transparent text-app-muted hover:text-app-text'">
                    <span class="material-symbols-outlined text-[18px]">grid_view</span> {{ __('Wallet Overview') }}
                </button>
                <button type="button" @click="switchTab('transactions')"
                    class="px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-colors whitespace-nowrap flex items-center gap-2"
                    :class="activeTab === 'transactions' ? 'border-primary text-primary' : 'border-transparent text-app-muted hover:text-app-text'">
                    <span class="material-symbols-outlined text-[18px]">swap_horiz</span> {{ __('Transaction History') }}
                </button>
                <button type="button" @click="switchTab('coupons')"
                    class="px-4 py-3 text-xs sm:text-sm font-bold border-b-2 transition-colors whitespace-nowrap flex items-center gap-2"
                    :class="activeTab === 'coupons' ? 'border-primary text-primary' : 'border-transparent text-app-muted hover:text-app-text'">
                    <span class="material-symbols-outlined text-[18px]">card_giftcard</span> {{ __('Redeem Gift Code') }}
                </button>
            </div>

            {{-- Tab Content Panels --}}
            <div class="p-4 sm:p-6 min-h-[300px]">
                <div x-show="activeTab === 'overview'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    @include('components.pages.app.billing.billing-tab.tab-overview', ['chartData' => $chartData])
                </div>

                <div x-show="activeTab === 'transactions'" style="display: none;"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">
                    @include('components.pages.app.billing.billing-tab.tab-transactions', ['transactions' => $transactions])
                </div>

                <div x-show="activeTab === 'coupons'" style="display: none;"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100">
                    @include('components.pages.app.billing.billing-tab.tab-coupons', ['publicCoupons' => $publicCoupons])
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
