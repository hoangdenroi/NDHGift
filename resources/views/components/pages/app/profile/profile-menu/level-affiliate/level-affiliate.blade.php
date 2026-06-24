@php
    $user = Auth::user();
    $levelService = app(\App\Services\UserLevelService::class);
    $progress = $levelService->calculateProgress($user);
    $discount = $levelService->getDiscountForUser($user);
    $adPercent = $levelService->getAdPercentForUser($user);
    $currentTier = $user->current_tier;
    $tierConfig = $levelService->getTierBenefits($currentTier);
    $xpTransactions = $user->xpTransactions()->orderBy('created_at', 'desc')->take(8)->get();
    $referralsCount = $user->referrals()->count();
    $configuredTiers = config('levels.tiers', []);
    $xpStats = $levelService->getXpEarningStats($user);
@endphp

<div class="flex flex-col gap-6">
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
                        class="flex items-center justify-between p-3 rounded-xl transition-colors {{ $currentTier === $key ? 'bg-primary/5 border border-primary/20' : 'bg-app-main/40 border border-app-border' }}">
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
        <div class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col gap-4 shadow-sm">
            <h3 class="text-sm font-bold text-app-text border-b border-app-border pb-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-500 text-[20px]">history</span>
                {{ __('XP Transaction History') }}
            </h3>

            @if($xpTransactions->isEmpty())
                <div class="flex flex-col items-center justify-center py-10 text-center">
                    <span class="material-symbols-outlined text-app-muted/30 text-5xl mb-2 select-none">database</span>
                    <p class="text-xs text-app-muted">{{ __('No XP transactions recorded yet.') }}</p>
                </div>
            @else
                <div class="flex flex-col divide-y divide-app-border/60">
                    @foreach($xpTransactions as $tx)
                        <div class="py-3 flex items-center justify-between first:pt-0 last:pb-0">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-semibold text-app-text">{{ $tx->description }}</span>
                                <span class="text-[10px] text-app-muted">{{ $tx->created_at->format('H:i d/m/Y') }}</span>
                            </div>
                            <span class="text-xs font-bold text-green-500 bg-green-500/10 px-2 py-0.5 rounded-full shrink-0">
                                +{{ $tx->amount }} XP
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
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
                Tích lũy điểm kinh nghiệm (XP) để nâng cấp tài khoản của bạn. Cấp bậc càng cao, bạn càng nhận được nhiều ưu đãi giảm giá và giảm mật độ quảng cáo hiển thị trên trang web.
            </p>
            
            <div class="flex flex-col gap-3">
                @foreach($xpStats as $stat)
                    <div class="p-4 bg-app-main border border-app-border rounded-xl flex items-start justify-between gap-4 transition-all hover:border-primary/20 hover:bg-primary/5">
                        <div class="flex items-start gap-3">
                            <div class="size-9 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[18px]">
                                    @if($stat['key'] === 'topup')
                                        payments
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
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs font-bold text-app-text">{{ $stat['title'] }}</span>
                                <span class="text-[11px] text-app-muted leading-relaxed">{{ $stat['description'] }}</span>
                                
                                {{-- Hiển thị tiến trình lượt --}}
                                @if($stat['type'] === 'daily')
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div class="w-20 h-1 bg-app-surface border border-app-border rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full" style="width: {{ min(100, ($stat['completed'] / $stat['limit']) * 100) }}%"></div>
                                        </div>
                                        <span class="text-[9px] font-semibold {{ $stat['completed'] >= $stat['limit'] ? 'text-green-500' : 'text-primary' }}">
                                            {{ $stat['completed'] }}/{{ $stat['limit'] }} hôm nay
                                        </span>
                                    </div>
                                @elseif($stat['type'] === 'monthly')
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div class="w-20 h-1 bg-app-surface border border-app-border rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full" style="width: {{ min(100, ($stat['completed'] / $stat['limit']) * 100) }}%"></div>
                                        </div>
                                        <span class="text-[9px] font-semibold {{ $stat['completed'] >= $stat['limit'] ? 'text-green-500' : 'text-primary' }}">
                                            {{ $stat['completed'] }}/{{ $stat['limit'] }} tháng này
                                        </span>
                                    </div>
                                @elseif($stat['type'] === 'once')
                                    <span class="text-[9px] font-bold mt-1.5 uppercase tracking-wider px-1.5 py-0.5 rounded {{ $stat['completed'] > 0 ? 'text-green-500 bg-green-500/10' : 'text-amber-500 bg-amber-500/10' }}">
                                        {{ $stat['completed'] > 0 ? 'Đã nhận' : 'Chưa nhận' }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <span class="text-[11px] font-bold text-orange-500 bg-orange-500/10 border border-orange-500/20 px-2 py-0.5 rounded-full shrink-0">
                            {{ $stat['xp'] }}
                        </span>
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
</div>