@php
    $user = Auth::user();
    $levelService = app(\App\Services\UserLevelService::class);
    $progress = $levelService->calculateProgress($user);
    $discount = $levelService->getDiscountForUser($user);
    $adPercent = $levelService->getAdPercentForUser($user);
    $currentTier = $user->current_tier;
    $tierConfig = $levelService->getTierBenefits($currentTier);
    $initialXpTransactions = $user->xpTransactions()->orderByDesc('created_at')->orderByDesc('id')->paginate(5);
    $referralsCount = $user->referrals()->count();
    $configuredTiers = config('levels.tiers', []);
    $xpStats = $levelService->getXpEarningStats($user);
@endphp

<div class="flex flex-col gap-6" x-data="{
    selectedTier: null,
    currentTier: '{{ $currentTier }}',
    openTierDetail(key, label, icon, minXp, discount, adPercent, color) {
        this.selectedTier = { key, label, icon, minXp, discount, adPercent, color };
        $dispatch('open-modal', 'tier-detail-modal');
    }
}">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')"
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">{{ __('Level & Affiliate') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">
                {{ __('Track your account level benefits and refer friends to earn rewards') }}
            </p>
        </div>
    </div>

    {{-- Alert trạng thái tài khoản bị đóng băng (Frozen) --}}
    @if($user->is_tier_frozen)
        <div
            class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-start gap-3 animate-pulse">
            <span class="material-symbols-outlined text-[24px] shrink-0">ac_unit</span>
            <div class="text-sm">
                <span class="font-bold">{{ __('Your account tier is currently frozen!') }}</span>
                <p class="mt-1 leading-relaxed">
                    {{ __('Due to inactivity for over 60 days, your privileges have been suspended. Earn any XP today (by making a deposit or completing tasks) to reactivate your benefits.') }}
                </p>
            </div>
        </div>
    @endif

    {{-- Content Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- 1. Thẻ Cấp Bậc (Tier Card) --}}
        <div
            class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col justify-between gap-6 shadow-sm">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span
                        class="text-xs font-semibold text-app-muted uppercase tracking-wider">{{ __('Current Tier') }}</span>
                    <button @click="$dispatch('open-modal', 'xp-missions-modal')"
                        class="px-2.5 py-1.5 rounded-xl text-[10px] font-bold uppercase tracking-wider hover:scale-[1.03] transition-all cursor-pointer flex items-center gap-1 bg-app-main border border-app-border group"
                        style="color: {{ $tierConfig['color'] }}; border-color: {{ $tierConfig['color'] }}40"
                        title="Xem cách tăng XP">
                        <span class="material-symbols-outlined text-[12px] group-hover:animate-bounce">add_circle</span>
                        {{ $tierConfig['label'] ?? $currentTier }}
                    </button>
                </div>

                {{-- Visual Tier Info --}}
                <div class="flex items-center gap-4">
                    <span class="text-5xl select-none"
                        style="filter: drop-shadow(0 4px 6px {{ $tierConfig['color'] }}40)">
                        {{ $tierConfig['icon'] ?? '🥉' }}
                    </span>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-app-text">{{ $tierConfig['label'] }}</span>
                        <span class="text-xs text-app-muted mt-0.5">{{ __('Accumulated XP:') }} <strong
                                class="text-app-text">{{ number_format($user->current_xp) }} XP</strong></span>
                    </div>
                </div>

                {{-- XP Progress Bar --}}
                <div class="flex flex-col gap-1.5 mt-2">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-app-muted">{{ __('Progress to Next Level') }}</span>
                        @if($progress['is_max'])
                            <span class="text-green-500 font-bold uppercase">{{ __('MAX LEVEL') }}</span>
                        @else
                            <span class="text-app-text">{{ number_format($progress['current_xp']) }} /
                                {{ number_format($progress['next_tier_xp']) }} XP</span>
                        @endif
                    </div>
                    <div class="w-full h-3 bg-app-main border border-app-border rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-400 rounded-full transition-all duration-500 ease-out"
                            style="width: {{ $progress['percent'] }}%"></div>
                    </div>
                    @if(!$progress['is_max'])
                        <span class="text-[11px] text-app-muted">
                            {{ __('Need') }}
                            <strong>{{ number_format($progress['next_tier_xp'] - $progress['current_xp']) }} XP</strong>
                            {{ __('more to reach') }} {{ $progress['next_tier_icon'] }} {{ $progress['next_tier_label'] }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Quyền lợi ưu đãi --}}
            <div class="border-t border-app-border pt-4 flex flex-col gap-3">
                <h4 class="text-xs font-bold text-app-muted uppercase tracking-wider">{{ __('Your Tier Benefits') }}
                </h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 bg-app-main border border-app-border rounded-xl flex items-center gap-3">
                        <span class="material-symbols-outlined text-[24px] text-orange-500">sell</span>
                        <div class="flex flex-col">
                            <span class="text-xs text-app-muted">{{ __('Discount') }}</span>
                            <span class="text-sm font-bold text-app-text">{{ $discount }}%</span>
                        </div>
                    </div>
                    <div class="p-3 bg-app-main border border-app-border rounded-xl flex items-center gap-3">
                        <span class="material-symbols-outlined text-[24px] text-blue-500">ads_click</span>
                        <div class="flex flex-col">
                            <span class="text-xs text-app-muted">{{ __('Ad Density') }}</span>
                            <span class="text-sm font-bold text-app-text">{{ $adPercent }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Thẻ Giới Thiệu (Affiliate Card) --}}
        <div
            class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col justify-between gap-6 shadow-sm">
            <div class="flex flex-col gap-4">
                <span
                    class="text-xs font-semibold text-app-muted uppercase tracking-wider">{{ __('Affiliate System') }}</span>

                <div>
                    <h3 class="text-base font-bold text-app-text">{{ __('Invite Friends & Earn Rewards') }}</h3>
                    <p class="text-xs text-app-muted mt-1 leading-relaxed">
                        {{ __('Share your referral link with friends. When they register and perform transactions, both of you will receive valuable rewards and XP.') }}
                    </p>
                </div>

                {{-- Copy Link Area --}}
                <div class="flex flex-col gap-1.5 mt-2" x-data="{ 
                    copied: false,
                    copyLink() {
                        navigator.clipboard.writeText('{{ $user->affiliate_link }}');
                        this.copied = true;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', title: '{{ __('Success') }}', message: '{{ __('Referral link copied successfully!') }}' } }));
                        setTimeout(() => this.copied = false, 2500);
                    }
                }">
                    <label class="text-xs font-semibold text-app-muted">{{ __('Your Referral Link') }}</label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-app-main border border-app-border rounded-xl px-3 py-2.5 text-xs text-app-text font-medium select-all truncate select-none pointer-events-none"
                            translate="no">
                            {{ $user->affiliate_link }}
                        </div>
                        <button @click="copyLink"
                            class="h-[38px] px-4 bg-primary hover:bg-primary/95 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-orange-500/15 active:scale-[0.97] flex items-center justify-center gap-1 shrink-0">
                            <span class="material-symbols-outlined text-[16px]"
                                x-text="copied ? 'check' : 'content_copy'"></span>
                            <span x-text="copied ? '{{ __('Copied') }}' : '{{ __('Copy') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Thống kê mời --}}
            <div class="border-t border-app-border pt-4 flex items-center justify-between">
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-app-muted">{{ __('Total Referred Members (F1)') }}</span>
                    <span class="text-lg font-bold text-app-text">{{ number_format($referralsCount) }}
                        {{ __('members') }}</span>
                </div>
                <div class="size-10 rounded-full bg-primary/10 flex items-center justify-center text-primary">
                    <span class="material-symbols-outlined text-[22px]">group</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2 Grid: Tiers List & XP History --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- 3. Danh sách cấp bậc & Yêu cầu --}}
        <div class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col gap-4 shadow-sm">
            <h3 class="text-sm font-bold text-app-text border-b border-app-border pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-orange-500 text-[20px]">military_tech</span>
                {{ __('Tier Privilege Table') }}
            </h3>
            <div class="flex flex-col gap-3">
                @foreach($configuredTiers as $key => $tier)
                    <div
                        @click="openTierDetail(
                            '{{ $key }}',
                            '{{ $tier['label'] }}',
                            '{{ $tier['icon'] }}',
                            {{ $tier['min_xp'] }},
                            {{ $tier['discount'] }},
                            {{ $tier['ad_percent'] }},
                            '{{ $tier['color'] }}'
                        )"
                        class="flex items-center justify-between p-3 rounded-xl cursor-pointer hover:bg-primary/5 hover:border-primary/30 transition-all active:scale-[0.98] {{ $currentTier === $key ? 'bg-primary/5 border border-primary/20 ring-1 ring-primary/30' : 'bg-app-main/40 border border-app-border' }}">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl select-none">{{ $tier['icon'] }}</span>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-app-text">{{ $tier['label'] }}</span>
                                <span class="text-[10px] text-app-muted mt-0.5">{{ __('Require:') }}
                                    {{ number_format($tier['min_xp']) }} XP</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end text-right">
                            <span class="text-[11px] font-bold text-orange-500">Giảm {{ $tier['discount'] }}%</span>
                            <span class="text-[10px] text-app-muted mt-0.5">Ads: {{ $tier['ad_percent'] }}%</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- 4. Lịch sử nhận XP (XP Log) --}}
        <div x-data="{
            transactions: @js($initialXpTransactions->items()),
            currentPage: 1,
            lastPage: @js($initialXpTransactions->lastPage()),
            total: @js($initialXpTransactions->total()),
            isLoading: false,
            async fetchPage(page) {
                if (page < 1 || page > this.lastPage || this.isLoading) return;
                this.isLoading = true;
                try {
                    const response = await fetch(`/api/v1/profile/xp-transactions?page=${page}`);
                    const res = await response.json();
                    if (res.success) {
                        this.transactions = res.data;
                        this.currentPage = res.current_page;
                        this.lastPage = res.last_page;
                        this.total = res.total;
                    }
                } catch (e) {
                    console.error('Error fetching XP transactions:', e);
                } finally {
                    this.isLoading = false;
                }
            },
            formatDate(dateString) {
                if (!dateString) return '';
                const date = new Date(dateString);
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const year = date.getFullYear();
                return `${hours}:${minutes} ${day}/${month}/${year}`;
            }
        }" class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col gap-4 shadow-sm">
            <h3 class="text-sm font-bold text-app-text border-b border-app-border pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500 text-[20px]">history</span>
                {{ __('XP Transaction History') }}
            </h3>

            {{-- Vùng chứa danh sách và Loading overlay --}}
            <div class="relative min-h-[150px] flex flex-col justify-between">
                <div x-show="isLoading"
                    class="absolute inset-0 bg-app-surface/60 backdrop-blur-[1px] flex items-center justify-center z-10"
                    x-cloak>
                    <div class="size-6 border-2 border-primary border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- Empty State --}}
                <template x-if="transactions.length === 0">
                    <div class="flex flex-col items-center justify-center py-10 text-center flex-1">
                        <span
                            class="material-symbols-outlined text-app-muted/30 text-5xl mb-2 select-none">database</span>
                        <p class="text-xs text-app-muted">{{ __('No XP transactions recorded yet.') }}</p>
                    </div>
                </template>
                {{-- Transaction List --}}
                <template x-if="transactions.length > 0">
                    <div class="flex flex-col divide-y divide-app-border/60 flex-1">
                        <template x-for="tx in transactions" :key="tx.id">
                            <div class="py-3 flex items-center justify-between first:pt-0 last:pb-0">
                                <div class="flex flex-col gap-0.5 min-w-0 flex-1 pr-4">
                                    <span class="text-xs font-semibold text-app-text break-words"
                                        x-text="tx.description"></span>
                                    <span class="text-[10px] text-app-muted" x-text="formatDate(tx.created_at)"></span>
                                </div>
                                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full shrink-0"
                                    :class="tx.amount > 0 ? 'text-green-500 bg-green-500/10' : (tx.amount < 0 ? 'text-red-500 bg-red-500/10' : 'text-app-muted bg-app-main border border-app-border')"
                                    x-text="tx.amount > 0 ? `+${tx.amount} XP` : `${tx.amount} XP`"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Pagination Controls --}}
            <template x-if="lastPage > 1">
                <div class="flex items-center justify-between border-t border-app-border pt-4 mt-2">
                    <button @click="fetchPage(currentPage - 1)" :disabled="currentPage === 1 || isLoading"
                        class="flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 text-xs font-semibold text-app-text disabled:opacity-50 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-all select-none">
                        <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                        <span>{{ __('Previous') }}</span>
                    </button>

                    <span class="text-[11px] font-semibold text-app-muted select-none"
                        x-text="`{{ __('Page') }} ${currentPage} / ${lastPage}`"></span>

                    <button @click="fetchPage(currentPage + 1)" :disabled="currentPage === lastPage || isLoading"
                        class="flex items-center justify-center gap-1 px-3 py-1.5 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 text-xs font-semibold text-app-text disabled:opacity-50 disabled:hover:bg-transparent disabled:cursor-not-allowed transition-all select-none">
                        <span>{{ __('Next') }}</span>
                        <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    {{-- Modal hiển thị danh sách nhiệm vụ / cách kiếm XP --}}
    <x-shared.ui.modal name="xp-missions-modal" maxWidth="lg">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-app-border flex items-center justify-between bg-app-main/20">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[22px]">rocket_launch</span>
                <h3 class="text-base font-bold text-app-text">Nhiệm Vụ Kiếm Điểm XP</h3>
            </div>
            <button @click="$dispatch('close-modal', 'xp-missions-modal')"
                class="size-8 rounded-lg flex items-center justify-center text-app-muted hover:text-app-text hover:bg-app-main border border-transparent hover:border-app-border transition-all">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 overflow-y-auto flex flex-col gap-4 max-h-[70vh]">
            <p class="text-xs text-app-muted leading-relaxed">
                Tích lũy điểm kinh nghiệm (XP) để nâng cấp tài khoản của bạn. Cấp bậc càng cao, bạn càng nhận được nhiều
                ưu đãi giảm giá và giảm mật độ quảng cáo hiển thị trên trang web.
            </p>

            <div class="flex flex-col gap-3">
                @foreach($xpStats as $stat)
                    <div
                        class="p-4 bg-app-main border border-app-border rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 transition-all hover:border-primary/20 hover:bg-primary/5">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <div
                                class="size-9 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[18px]">
                                    @if($stat['key'] === 'topup')
                                        payments
                                    @elseif($stat['key'] === 'daily_checkin')
                                        calendar_month
                                    @elseif($stat['key'] === 'gift_create')
                                        featured_seasonal_and_gifts
                                    @elseif($stat['key'] === 'referral_signup')
                                        person_add
                                    @elseif($stat['key'] === 'referral_first_deposit')
                                        handshake
                                    @elseif($stat['key'] === 'register')
                                        waving_hand
                                    @elseif($stat['key'] === 'verify_email')
                                        verified
                                    @else
                                        stars
                                    @endif
                                </span>
                            </div>
                            <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                                <span
                                    class="text-xs font-bold text-app-text truncate sm:whitespace-normal">{{ $stat['title'] }}</span>
                                <span class="text-[11px] text-app-muted leading-relaxed">{{ $stat['description'] }}</span>

                                {{-- Hiển thị tiến trình lượt --}}
                                @if($stat['type'] === 'daily')
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div
                                            class="w-20 h-1 bg-app-surface border border-app-border rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full"
                                                style="width: {{ min(100, ($stat['completed'] / $stat['limit']) * 100) }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-[9px] font-semibold {{ $stat['completed'] >= $stat['limit'] ? 'text-green-500' : 'text-primary' }}">
                                            {{ $stat['completed'] }}/{{ $stat['limit'] }} hôm nay
                                        </span>
                                    </div>
                                @elseif($stat['type'] === 'monthly')
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div
                                            class="w-20 h-1 bg-app-surface border border-app-border rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full"
                                                style="width: {{ min(100, ($stat['completed'] / $stat['limit']) * 100) }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-[9px] font-semibold {{ $stat['completed'] >= $stat['limit'] ? 'text-green-500' : 'text-primary' }}">
                                            {{ $stat['completed'] }}/{{ $stat['limit'] }} tháng này
                                        </span>
                                    </div>
                                @elseif($stat['type'] === 'checkin')
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span
                                            class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded {{ $stat['completed'] > 0 ? 'text-green-500 bg-green-500/10' : 'text-amber-500 bg-amber-500/10' }}">
                                            {{ $stat['completed'] > 0 ? 'Đã điểm danh' : 'Chưa điểm danh' }}
                                        </span>
                                        <span class="text-[9px] font-semibold text-primary">
                                            Chuỗi: {{ $stat['streak'] }}/7 ngày
                                        </span>
                                    </div>
                                @elseif($stat['type'] === 'once')
                                    <span
                                        class="text-[9px] font-bold mt-1.5 uppercase tracking-wider px-1.5 py-0.5 rounded {{ $stat['completed'] > 0 ? 'text-green-500 bg-green-500/10' : 'text-amber-500 bg-amber-500/10' }}">
                                        {{ $stat['completed'] > 0 ? 'Đã nhận' : 'Chưa nhận' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1 shrink-0 self-start sm:self-center">
                            <span class="text-[11px] font-bold text-orange-500 bg-orange-500/10 border border-orange-500/20 px-2 py-0.5 rounded-full">
                                {{ $stat['xp'] }}
                            </span>
                            @if($stat['key'] === 'referral_first_deposit')
                                <span class="text-[10px] font-bold text-green-500 bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded-full">
                                    +10% Hoa hồng
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-4 border-t border-app-border flex justify-end bg-app-main/10">
            <button @click="$dispatch('close-modal', 'xp-missions-modal')"
                class="h-9 px-4 bg-app-surface border border-app-border hover:bg-app-main text-app-text hover:border-app-border-hover font-semibold text-xs rounded-xl transition-all active:scale-[0.98]">
                Đóng
            </button>
        </div>
    </x-shared.ui.modal>

    {{-- Modal hiển thị chi tiết cấp bậc --}}
    <x-shared.ui.modal name="tier-detail-modal" maxWidth="md">
        <template x-if="selectedTier">
            <div>
                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-app-border flex items-center justify-between bg-app-main/20"
                    :style="`border-bottom-color: ${selectedTier.color}20`">
                    <div class="flex items-center gap-2">
                        <span class="text-xl select-none" x-text="selectedTier.icon"></span>
                        <h3 class="text-base font-bold text-app-text" x-text="selectedTier.label"></h3>
                    </div>
                    <button @click="$dispatch('close-modal', 'tier-detail-modal')"
                        class="size-8 rounded-lg flex items-center justify-center text-app-muted hover:text-app-text hover:bg-app-main border border-transparent hover:border-app-border transition-all">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 flex flex-col gap-5">
                    {{-- Nếu là cấp bậc hiện tại --}}
                    <template x-if="selectedTier.key === currentTier">
                        <div class="p-3.5 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center gap-3 text-green-500 dark:text-green-400">
                            <span class="material-symbols-outlined text-[20px] animate-pulse">verified</span>
                            <div class="text-xs font-bold">
                                Đây là cấp bậc thành viên hiện tại của bạn
                            </div>
                        </div>
                    </template>

                    {{-- Thẻ thông tin lớn --}}
                    <div class="p-5 rounded-xl border border-app-border flex flex-col items-center justify-center gap-3 bg-app-main/20 relative overflow-hidden">
                        {{-- Background Glow --}}
                        <div class="absolute -right-8 -top-8 size-24 rounded-full blur-2xl opacity-20 pointer-events-none"
                            :style="`background-color: ${selectedTier.color}`"></div>
                        
                        <span class="text-5xl select-none" x-text="selectedTier.icon"
                            :style="`filter: drop-shadow(0 8px 12px ${selectedTier.color}30)`"></span>
                        <div class="flex flex-col items-center text-center mt-1">
                            <span class="text-lg font-extrabold text-app-text" x-text="selectedTier.label" :style="`color: ${selectedTier.color}`"></span>
                            <span class="text-xs text-app-muted mt-1">
                                Yêu cầu tối thiểu: <strong class="text-app-text font-bold" x-text="`${selectedTier.minXp.toLocaleString()} XP`"></strong>
                            </span>
                        </div>
                    </div>

                    {{-- Đặc quyền chi tiết --}}
                    <div class="flex flex-col gap-3">
                        <h4 class="text-xs font-bold text-app-muted uppercase tracking-wider">Đặc quyền cấp bậc</h4>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-app-main border border-app-border rounded-xl flex items-center gap-3">
                                <span class="material-symbols-outlined text-[22px] text-orange-500">sell</span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-app-muted">Giảm giá template</span>
                                    <span class="text-xs font-bold text-app-text" x-text="`Giảm ${selectedTier.discount}%`"></span>
                                </div>
                            </div>
                            <div class="p-3 bg-app-main border border-app-border rounded-xl flex items-center gap-3">
                                <span class="material-symbols-outlined text-[22px] text-blue-500">ads_click</span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-app-muted">Mật độ quảng cáo</span>
                                    <span class="text-xs font-bold text-app-text" x-text="`Còn ${selectedTier.adPercent}%`"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mô tả thêm cho từng cấp bậc để tạo cảm giác premium --}}
                    <div class="p-4 bg-app-main/30 border border-app-border/60 rounded-xl">
                        <h4 class="text-xs font-bold text-app-muted mb-1.5 uppercase tracking-wider">Chi tiết đặc quyền</h4>
                        <p class="text-xs text-app-text leading-relaxed font-medium" x-text="
                            selectedTier.key === 'bronze' ? 'Cấp bậc khởi đầu dành cho mọi thành viên mới. Bạn được sử dụng đầy đủ các tính năng cơ bản của NDHGift và bắt đầu hành trình tích lũy XP.' :
                            selectedTier.key === 'silver' ? 'Cấp bậc Bạc đánh dấu sự tiến bộ của bạn. Nhận ngay ưu đãi giảm giá 5% khi mua các template premium và giảm 30% lượng quảng cáo hiển thị.' :
                            selectedTier.key === 'gold' ? 'Trở thành thành viên Vàng để tận hưởng các ưu đãi hấp dẫn hơn. Giảm giá 10% cho mọi giao dịch template premium và giảm đến 60% quảng cáo.' :
                            selectedTier.key === 'platinum' ? 'Thành viên Bạch Kim sở hữu những đặc quyền ưu tú. Giảm giá sâu 15% cho template premium và giảm tối đa 90% tần suất xuất hiện quảng cáo.' :
                            selectedTier.key === 'diamond' ? 'Cấp bậc tối cao của NDHGift. Tận hưởng ưu đãi giảm giá cao nhất 20% cho template premium và trải nghiệm lướt web hoàn toàn không có quảng cáo (100% Ad-Free).' : ''
                        "></p>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-app-border flex justify-end bg-app-main/10">
                    <button @click="$dispatch('close-modal', 'tier-detail-modal')"
                        class="h-9 px-4 bg-app-surface border border-app-border hover:bg-app-main text-app-text hover:border-app-border-hover font-semibold text-xs rounded-xl transition-all active:scale-[0.98]">
                        Đóng
                    </button>
                </div>
            </div>
        </template>
    </x-shared.ui.modal>
</div>