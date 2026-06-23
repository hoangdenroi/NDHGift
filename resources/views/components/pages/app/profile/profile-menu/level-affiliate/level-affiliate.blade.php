@php
    $user = Auth::user();
    $progress = app(\App\Services\UserLevelService::class)->calculateProgress($user);
    $discount = app(\App\Services\UserLevelService::class)->getDiscountForUser($user);
    $adPercent = app(\App\Services\UserLevelService::class)->getAdPercentForUser($user);
    $currentTier = $user->current_tier;
    $tierConfig = app(\App\Services\UserLevelService::class)->getTierBenefits($currentTier);
    $xpTransactions = $user->xpTransactions()->orderBy('created_at', 'desc')->take(8)->get();
    $referralsCount = $user->referrals()->count();
    $configuredTiers = config('levels.tiers', []);
@endphp

<div class="flex flex-col gap-6">
    {{-- Header --}}
    <div class="flex items-center justify-between border-b border-app-border pb-4">
        <div>
            <h2 class="text-lg font-bold text-app-text">{{ __('Level & Affiliate') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">{{ __('Track your account level benefits and refer friends to earn rewards') }}</p>
        </div>
        <button @click="setActiveAction('menu')" class="h-9 px-4 bg-app-main border border-app-border text-app-text hover:bg-primary/5 hover:border-primary/30 font-semibold text-xs rounded-xl transition-all active:scale-[0.98] flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[16px]">arrow_back</span>
            {{ __('Back to Menu') }}
        </button>
    </div>

    {{-- Alert trạng thái tài khoản bị đóng băng (Frozen) --}}
    @if($user->is_tier_frozen)
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 flex items-start gap-3 animate-pulse">
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
        <div class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col justify-between gap-6 shadow-sm">
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-app-muted uppercase tracking-wider">{{ __('Current Tier') }}</span>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider" 
                          style="background-color: {{ $tierConfig['color'] }}15; color: {{ $tierConfig['color'] }}">
                        {{ $tierConfig['label'] ?? $currentTier }}
                    </span>
                </div>

                {{-- Visual Tier Info --}}
                <div class="flex items-center gap-4">
                    <span class="text-5xl select-none" style="filter: drop-shadow(0 4px 6px {{ $tierConfig['color'] }}40)">
                        {{ $tierConfig['icon'] ?? '🥉' }}
                    </span>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-app-text">{{ $tierConfig['label'] }}</span>
                        <span class="text-xs text-app-muted mt-0.5">{{ __('Accumulated XP:') }} <strong class="text-app-text">{{ number_format($user->current_xp) }} XP</strong></span>
                    </div>
                </div>

                {{-- XP Progress Bar --}}
                <div class="flex flex-col gap-1.5 mt-2">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-app-muted">{{ __('Progress to Next Level') }}</span>
                        @if($progress['is_max'])
                            <span class="text-green-500 font-bold uppercase">{{ __('MAX LEVEL') }}</span>
                        @else
                            <span class="text-app-text">{{ number_format($progress['current_xp']) }} / {{ number_format($progress['next_tier_xp']) }} XP</span>
                        @endif
                    </div>
                    <div class="w-full h-3 bg-app-main border border-app-border rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-400 rounded-full transition-all duration-500 ease-out" 
                             style="width: {{ $progress['percent'] }}%"></div>
                    </div>
                    @if(!$progress['is_max'])
                        <span class="text-[11px] text-app-muted">
                            {{ __('Need') }} <strong>{{ number_format($progress['next_tier_xp'] - $progress['current_xp']) }} XP</strong> {{ __('more to reach') }} {{ $progress['next_tier_icon'] }} {{ $progress['next_tier_label'] }}
                        </span>
                    @endif
                </div>
            </div>

            {{-- Quyền lợi ưu đãi --}}
            <div class="border-t border-app-border pt-4 flex flex-col gap-3">
                <h4 class="text-xs font-bold text-app-muted uppercase tracking-wider">{{ __('Your Tier Benefits') }}</h4>
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
        <div class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col justify-between gap-6 shadow-sm">
            <div class="flex flex-col gap-4">
                <span class="text-xs font-semibold text-app-muted uppercase tracking-wider">{{ __('Affiliate System') }}</span>
                
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
                        <div class="flex-1 bg-app-main border border-app-border rounded-xl px-3 py-2.5 text-xs text-app-text font-medium select-all truncate select-none pointer-events-none" translate="no">
                            {{ $user->affiliate_link }}
                        </div>
                        <button @click="copyLink" 
                                class="h-[38px] px-4 bg-primary hover:bg-primary/95 text-white font-bold text-xs rounded-xl transition-all shadow-md shadow-orange-500/15 active:scale-[0.97] flex items-center justify-center gap-1 shrink-0">
                            <span class="material-symbols-outlined text-[16px]" x-text="copied ? 'check' : 'content_copy'"></span>
                            <span x-text="copied ? '{{ __('Copied') }}' : '{{ __('Copy') }}'"></span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Thống kê mời --}}
            <div class="border-t border-app-border pt-4 flex items-center justify-between">
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs text-app-muted">{{ __('Total Referred Members (F1)') }}</span>
                    <span class="text-lg font-bold text-app-text">{{ number_format($referralsCount) }} {{ __('members') }}</span>
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
                    <div class="flex items-center justify-between p-3 rounded-xl transition-colors {{ $currentTier === $key ? 'bg-primary/5 border border-primary/20' : 'bg-app-main/40 border border-app-border' }}">
                        <div class="flex items-center gap-2.5">
                            <span class="text-2xl select-none">{{ $tier['icon'] }}</span>
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-app-text">{{ $tier['label'] }}</span>
                                <span class="text-[10px] text-app-muted mt-0.5">{{ __('Require:') }} {{ number_format($tier['min_xp']) }} XP</span>
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
</div>
