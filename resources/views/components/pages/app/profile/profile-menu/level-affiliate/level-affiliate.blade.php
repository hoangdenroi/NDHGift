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

<div class="flex flex-col gap-6" 
    @claim-quest.window="claimQuest($event.detail)"
    x-data="{
    selectedTier: null,
    currentTier: '{{ $currentTier }}',
    openTierDetail(key, label, icon, minXp, discount, adPercent, color) {
        this.selectedTier = { key, label, icon, minXp, discount, adPercent, color };
        $dispatch('open-modal', 'tier-detail-modal');
    },
    quests: @js($xpStats),
    isClaiming: { register: false, verify_email: false },
    currentXp: {{ $user->current_xp }},
    progressPercent: {{ $progress['percent'] }},
    progressText: '{{ $progress['is_max'] ? "MAX LEVEL" : number_format($progress['current_xp']) . " / " . number_format($progress['next_tier_xp']) . " XP" }}',
    progressIsMax: {{ $progress['is_max'] ? 'true' : 'false' }},
    tierLabel: '{{ $tierConfig['label'] }}',
    tierIcon: '{{ $tierConfig['icon'] }}',
    tierColor: '{{ $tierConfig['color'] }}',

    claimQuest(questKey) {
        if (this.isClaiming[questKey]) return;
        this.isClaiming = { ...this.isClaiming, [questKey]: true };
        
        fetch('{{ route('api.profile.claim_quest') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ quest_key: questKey })
        })
        .then(res => res.json())
        .then(data => {
            this.isClaiming = { ...this.isClaiming, [questKey]: false };
            if (data.success) {
                const quest = this.quests.find(q => q.key === questKey);
                if (quest) {
                    quest.completed = 1;
                }
                
                this.currentXp = data.data.total_xp;
                this.currentTier = data.data.tier;
                this.tierLabel = data.data.tier_label;
                this.tierIcon = data.data.tier_icon;
                this.tierColor = data.data.tier_color;
                this.progressPercent = data.data.progress.percent;
                this.progressIsMax = data.data.progress.is_max;
                this.progressText = data.data.progress.is_max 
                    ? 'MAX LEVEL' 
                    : (data.data.progress.current_xp.toLocaleString() + ' / ' + data.data.progress.next_tier_xp.toLocaleString() + ' XP');

                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'success', title: 'Nhận thưởng', message: data.message }
                }));

                if (typeof window.refreshXpHistory === 'function') {
                    window.refreshXpHistory();
                }
            } else {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: { type: 'error', title: 'Thất bại', message: data.message }
                }));
            }
        })
        .catch(() => {
            this.isClaiming = { ...this.isClaiming, [questKey]: false };
            window.dispatchEvent(new CustomEvent('toast', {
                detail: { type: 'error', title: 'Lỗi', message: 'Không thể kết nối đến máy chủ.' }
            }));
        });
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
                        :style="`color: ${tierColor}; border-color: ${tierColor}40`"
                        title="Xem cách tăng XP">
                        <span class="material-symbols-outlined text-[12px] group-hover:animate-bounce">add_circle</span>
                        <span x-text="tierLabel"></span>
                    </button>
                </div>

                {{-- Visual Tier Info --}}
                <div class="flex items-center gap-4">
                    <span class="text-5xl select-none"
                        x-text="tierIcon"
                        :style="`filter: drop-shadow(0 4px 6px ${tierColor}40)`">
                    </span>
                    <div class="flex flex-col">
                        <span class="text-xl font-bold text-app-text" x-text="tierLabel"></span>
                        <span class="text-xs text-app-muted mt-0.5">{{ __('Accumulated XP:') }} <strong
                                class="text-app-text" x-text="`${currentXp.toLocaleString()} XP`"></strong></span>
                    </div>
                </div>

                {{-- XP Progress Bar --}}
                <div class="flex flex-col gap-1.5 mt-2">
                    <div class="flex justify-between text-xs font-semibold">
                        <span class="text-app-muted">{{ __('Progress to Next Level') }}</span>
                        <span :class="progressIsMax ? 'text-green-500 font-bold uppercase' : 'text-app-text'" x-text="progressText"></span>
                    </div>
                    <div class="w-full h-3 bg-app-main border border-app-border rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-400 rounded-full transition-all duration-500 ease-out"
                            :style="`width: ${progressPercent}%`"></div>
                    </div>
                    @if(!$progress['is_max'])
                        <span class="text-[11px] text-app-muted" x-show="!progressIsMax">
                            {{ __('Need') }}
                            <strong class="text-app-text font-bold" x-text="`${({{ $progress['next_tier_xp'] }} - currentXp).toLocaleString()} XP`"></strong>
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
        {{-- 3. Danh sách cấp bậc & Bảng xếp hạng (Tab Switch) --}}
        <div x-data="{
            tierTab: 'privileges',
            leaderboard: [],
            totalRanked: 0,
            myRank: 0,
            myTopPercent: 100,
            myInTop10: false,
            isAnonymous: false,
            isLeaderboardLoading: false,
            isTogglingAnonymous: false,
            leaderboardLoaded: false,
            async fetchLeaderboard() {
                if (this.isLeaderboardLoading) return;
                this.isLeaderboardLoading = true;
                try {
                    const res = await fetch('/api/v1/profile/leaderboard');
                    const data = await res.json();
                    if (data.success) {
                        this.leaderboard = data.top;
                        this.totalRanked = data.total_ranked;
                        this.myRank = data.my_rank;
                        this.myTopPercent = data.my_top_percent;
                        this.myInTop10 = data.my_in_top_10;
                        this.isAnonymous = data.is_anonymous;
                        this.leaderboardLoaded = true;
                    }
                } catch (e) {
                    console.error('Lỗi tải bảng xếp hạng:', e);
                } finally {
                    this.isLeaderboardLoading = false;
                }
            },
            async toggleAnonymous() {
                if (this.isTogglingAnonymous) return;
                this.isTogglingAnonymous = true;
                try {
                    const res = await fetch('/api/v1/profile/toggle-anonymous', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                        }
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.isAnonymous = data.is_anonymous;
                        // Tải lại leaderboard để cập nhật tên hiển thị
                        this.leaderboardLoaded = false;
                        await this.fetchLeaderboard();
                        window.dispatchEvent(new CustomEvent('toast', {
                            detail: { type: 'success', title: '{{ __('Success') }}', message: data.message }
                        }));
                    }
                } catch (e) {
                    console.error('Lỗi toggle ẩn danh:', e);
                } finally {
                    this.isTogglingAnonymous = false;
                }
            },
            switchToLeaderboard() {
                this.tierTab = 'leaderboard';
                if (!this.leaderboardLoaded) {
                    this.fetchLeaderboard();
                }
            },
            getRankMedal(rank) {
                if (rank === 1) return '🥇';
                if (rank === 2) return '🥈';
                if (rank === 3) return '🥉';
                return '#' + rank;
            },
            getRankClass(rank) {
                if (rank === 1) return 'text-amber-400 bg-amber-500/10 border-amber-500/30';
                if (rank === 2) return 'text-gray-300 bg-gray-400/10 border-gray-400/30';
                if (rank === 3) return 'text-orange-400 bg-orange-500/10 border-orange-500/30';
                return 'text-app-muted bg-app-main border-app-border';
            }
        }" class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col gap-4 shadow-sm">
            {{-- Tab Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-app-border pb-3 gap-3">
                <div class="flex items-center gap-1 bg-app-main rounded-lg p-0.5 border border-app-border self-start">
                    <button @click="tierTab = 'privileges'"
                        :class="tierTab === 'privileges' ? 'bg-app-surface shadow-sm text-app-text border-app-border' : 'text-app-muted hover:text-app-text border-transparent'"
                        class="px-3 py-1.5 rounded-md text-[11px] font-bold transition-all border flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px] text-orange-500">military_tech</span>
                        Đặc quyền
                    </button>
                    <button @click="switchToLeaderboard()"
                        :class="tierTab === 'leaderboard' ? 'bg-app-surface shadow-sm text-app-text border-app-border' : 'text-app-muted hover:text-app-text border-transparent'"
                        class="px-3 py-1.5 rounded-md text-[11px] font-bold transition-all border flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[14px] text-blue-500">leaderboard</span>
                        Xếp hạng
                    </button>
                </div>

                {{-- Nút toggle ẩn danh (chỉ hiện khi ở tab xếp hạng) dạng switch --}}
                <div x-show="tierTab === 'leaderboard'" x-cloak
                    class="flex items-center gap-2 select-none cursor-pointer self-end sm:self-auto" @click="toggleAnonymous()">
                    <span class="text-[10px] font-bold text-app-muted">Ẩn danh</span>
                    <button type="button" :disabled="isTogglingAnonymous"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none items-center disabled:opacity-50"
                        :class="isAnonymous ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-700'"
                        :title="isAnonymous ? 'Đang ẩn danh — click để hiện tên' : 'Đang hiện tên — click để ẩn danh'">
                        <span class="pointer-events-none inline-block size-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                            :class="isAnonymous ? 'translate-x-5' : 'translate-x-0'"></span>
                    </button>
                </div>
            </div>

            {{-- Tab Content: Đặc quyền --}}
            <div x-show="tierTab === 'privileges'" x-cloak class="flex flex-col gap-3">
                @foreach($configuredTiers as $key => $tier)
                    <div @click="openTierDetail(
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

            {{-- Tab Content: Bảng xếp hạng --}}
            <div x-show="tierTab === 'leaderboard'" x-cloak class="flex flex-col gap-4">

                {{-- Skeleton Loading --}}
                <div x-show="isLeaderboardLoading" class="flex flex-col gap-2.5" x-cloak>
                    <template x-for="i in 10" :key="'lb-skel-' + i">
                        <div class="flex items-center gap-3 p-2.5 rounded-xl bg-app-main/40 border border-app-border">
                            <div class="size-6 skeleton-shimmer rounded-lg shrink-0"></div>
                            <div class="size-8 skeleton-shimmer rounded-full shrink-0"></div>
                            <div class="flex flex-col gap-1 flex-1">
                                <div class="h-3 skeleton-shimmer rounded w-2/3"></div>
                                <div class="h-2 skeleton-shimmer rounded w-1/3"></div>
                            </div>
                            <div class="h-4 skeleton-shimmer rounded-full w-16 shrink-0"></div>
                        </div>
                    </template>
                </div>

                {{-- Leaderboard List --}}
                <div x-show="!isLeaderboardLoading && leaderboard.length > 0" class="flex flex-col gap-2" x-cloak>
                    <template x-for="entry in leaderboard" :key="'lb-' + entry.rank">
                        <div class="flex items-center gap-3 p-2.5 rounded-xl transition-all"
                            :class="entry.is_current_user
                                ? 'bg-primary/10 border border-primary/30 ring-1 ring-primary/20'
                                : (entry.rank <= 3 ? 'bg-app-main/60 border border-app-border hover:border-primary/20' : 'bg-app-main/40 border border-app-border hover:bg-app-main/60')">

                            {{-- Hạng --}}
                            <div class="flex items-center justify-center size-7 rounded-lg border text-[11px] font-extrabold shrink-0 select-none"
                                :class="getRankClass(entry.rank)" x-text="getRankMedal(entry.rank)">
                            </div>

                            {{-- Avatar --}}
                            <div
                                class="size-8 rounded-full overflow-hidden bg-app-surface border border-app-border shrink-0 flex items-center justify-center">
                                <template x-if="entry.avatar_url">
                                    <img :src="entry.avatar_url" alt="Avatar" class="size-full object-cover">
                                </template>
                                <template x-if="!entry.avatar_url">
                                    <span class="material-symbols-outlined text-app-muted text-[18px]">person</span>
                                </template>
                            </div>

                            {{-- Thông tin --}}
                            <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-app-text truncate"
                                        x-text="entry.fullname"></span>
                                    <template x-if="entry.is_current_user">
                                        <span
                                            class="text-[9px] font-bold text-primary bg-primary/10 px-1.5 py-0.5 rounded-full shrink-0">Bạn</span>
                                    </template>
                                    <template x-if="entry.is_anonymous">
                                        <span class="material-symbols-outlined text-[12px] text-app-muted shrink-0"
                                            title="Ẩn danh">visibility_off</span>
                                    </template>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-[10px] select-none" x-text="entry.tier_icon"></span>
                                    <span class="text-[10px] text-app-muted" x-text="entry.tier_label"></span>
                                </div>
                            </div>

                            {{-- XP --}}
                            <span
                                class="text-[11px] font-bold text-orange-500 bg-orange-500/10 border border-orange-500/20 px-2 py-0.5 rounded-full shrink-0 select-none"
                                x-text="entry.total_xp.toLocaleString() + ' XP'">
                            </span>
                        </div>
                    </template>
                </div>

                {{-- Empty State --}}
                <div x-show="!isLeaderboardLoading && leaderboard.length === 0 && leaderboardLoaded"
                    class="flex flex-col items-center justify-center py-10 text-center" x-cloak>
                    <span
                        class="material-symbols-outlined text-app-muted/30 text-5xl mb-2 select-none">leaderboard</span>
                    <p class="text-xs text-app-muted">Chưa có thành viên nào trên bảng xếp hạng.</p>
                </div>

                {{-- Card Vị trí của bạn (chỉ hiện khi không nằm top 10) --}}
                <div x-show="!isLeaderboardLoading && leaderboardLoaded && !myInTop10 && myRank > 0"
                    class="p-4 rounded-xl bg-gradient-to-r from-primary/5 to-blue-500/5 border border-primary/20 flex items-center justify-between gap-4"
                    x-cloak>
                    <div class="flex items-center gap-3">
                        <div
                            class="size-10 rounded-full bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0">
                            <span class="material-symbols-outlined text-[22px]">person_pin</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold text-app-text">Vị trí của bạn</span>
                            <span class="text-[10px] text-app-muted mt-0.5">
                                Xếp hạng <strong class="text-app-text" x-text="'#' + myRank"></strong>
                                trên <strong x-text="totalRanked.toLocaleString()"></strong> thành viên
                            </span>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        <span class="text-sm font-extrabold text-primary" x-text="'Top ' + myTopPercent + '%'"></span>
                    </div>
                </div>

                {{-- Card Vị trí của bạn (khi nằm trong top 10) --}}
                <div x-show="!isLeaderboardLoading && leaderboardLoaded && myInTop10 && myRank > 0"
                    class="p-3 rounded-xl bg-green-500/5 border border-green-500/20 flex items-center gap-2 text-green-600 dark:text-green-400"
                    x-cloak>
                    <span class="material-symbols-outlined text-[18px]">emoji_events</span>
                    <span class="text-[11px] font-bold">
                        <template x-if="myRank === 1">
                            <span>Bạn xuất sắc đứng ở vị trí <strong class="text-orange-500 font-extrabold">#1</strong>
                                trên toàn hệ thống! 🏆</span>
                        </template>
                        <template x-if="myRank > 1">
                            <span>Bạn đang ở vị trí <strong x-text="'#' + myRank"></strong> trên tổng số <strong
                                    x-text="totalRanked"></strong> thành viên!</span>
                        </template>
                    </span>
                </div>

                {{-- Khi user chưa có XP --}}
                <div x-show="!isLeaderboardLoading && leaderboardLoaded && myRank === 0"
                    class="p-3 rounded-xl bg-amber-500/5 border border-amber-500/20 flex items-center gap-2 text-amber-600 dark:text-amber-400"
                    x-cloak>
                    <span class="material-symbols-outlined text-[18px]">info</span>
                    <span class="text-[11px] font-bold">Hãy tích lũy XP để xuất hiện trên bảng xếp hạng!</span>
                </div>
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
                {{-- Skeleton overlay — mô phỏng 5 dòng XP transaction --}}
                <div x-show="isLoading"
                    class="absolute inset-0 bg-app-surface z-10 flex flex-col divide-y divide-app-border/40 p-1"
                    x-cloak>
                    <template x-for="i in 5" :key="'xp-skel-' + i">
                        <div class="py-3 flex items-center justify-between first:pt-0 last:pb-0">
                            <div class="flex flex-col gap-1.5 flex-1 pr-4">
                                <div class="h-3 skeleton-shimmer rounded w-3/4"></div>
                                <div class="h-2 skeleton-shimmer rounded w-1/3"></div>
                            </div>
                            <div class="h-5 skeleton-shimmer rounded-full w-14 shrink-0"></div>
                        </div>
                    </template>
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
                <template x-for="stat in quests" :key="stat.key">
                    <div
                        class="p-4 bg-app-main border border-app-border rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 transition-all hover:border-primary/20 hover:bg-primary/5">
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <div
                                class="size-9 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center text-primary shrink-0 mt-0.5">
                                <span class="material-symbols-outlined text-[18px]" x-text="
                                    stat.key === 'topup' ? 'payments' :
                                    stat.key === 'daily_checkin' ? 'calendar_month' :
                                    stat.key === 'gift_create' ? 'featured_seasonal_and_gifts' :
                                    stat.key === 'referral_signup' ? 'person_add' :
                                    stat.key === 'referral_first_deposit' ? 'handshake' :
                                    stat.key === 'register' ? 'waving_hand' :
                                    stat.key === 'verify_email' ? 'verified' : 'stars'
                                "></span>
                            </div>
                            <div class="flex flex-col gap-0.5 min-w-0 flex-1">
                                <span class="text-xs font-bold text-app-text truncate sm:whitespace-normal" x-text="stat.title"></span>
                                <span class="text-[11px] text-app-muted leading-relaxed" x-text="stat.description"></span>

                                {{-- Tiến trình lượt hoặc trạng thái nhận thưởng --}}
                                <template x-if="stat.type === 'daily'">
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div class="w-20 h-1 bg-app-surface border border-app-border rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full"
                                                :style="`width: ${Math.min(100, (stat.completed / stat.limit) * 100)}%`">
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-semibold"
                                            :class="stat.completed >= stat.limit ? 'text-green-500' : 'text-primary'"
                                            x-text="`${stat.completed}/${stat.limit} hôm nay`">
                                        </span>
                                    </div>
                                </template>

                                <template x-if="stat.type === 'monthly'">
                                    <div class="flex items-center gap-1.5 mt-1.5">
                                        <div class="w-20 h-1 bg-app-surface border border-app-border rounded-full overflow-hidden">
                                            <div class="h-full bg-primary rounded-full"
                                                :style="`width: ${Math.min(100, (stat.completed / stat.limit) * 100)}%`">
                                            </div>
                                        </div>
                                        <span class="text-[9px] font-semibold"
                                            :class="stat.completed >= stat.limit ? 'text-green-500' : 'text-primary'"
                                            x-text="`${stat.completed}/${stat.limit} tháng này`">
                                        </span>
                                    </div>
                                </template>

                                <template x-if="stat.type === 'checkin'">
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded"
                                            :class="stat.completed > 0 ? 'text-green-500 bg-green-500/10' : 'text-amber-500 bg-amber-500/10'"
                                            x-text="stat.completed > 0 ? 'Đã điểm danh' : 'Chưa điểm danh'">
                                        </span>
                                        <span class="text-[9px] font-semibold text-primary" x-text="`Chuỗi: ${stat.streak}/7 ngày`"></span>
                                    </div>
                                </template>

                                <template x-if="stat.type === 'once'">
                                    <span class="text-[9px] font-bold mt-1.5 uppercase tracking-wider px-1.5 py-0.5 rounded self-start"
                                        :class="stat.completed > 0 ? 'text-green-500 bg-green-500/10' : 'text-amber-500 bg-amber-500/10'"
                                        x-text="stat.completed > 0 ? 'Đã nhận' : 'Chưa nhận'">
                                    </span>
                                </template>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1.5 shrink-0 self-start sm:self-center">
                            <span class="text-[11px] font-bold text-orange-500 bg-orange-500/10 border border-orange-500/20 px-2 py-0.5 rounded-full"
                                x-text="stat.xp"></span>
                            
                            <template x-if="stat.key === 'referral_first_deposit'">
                                <span class="text-[10px] font-bold text-green-500 bg-green-500/10 border border-green-500/20 px-2 py-0.5 rounded-full">
                                    +10% Hoa hồng
                                </span>
                            </template>

                            {{-- Nút hành động cho nhiệm vụ một lần --}}
                            <template x-if="stat.type === 'once' && stat.completed === 0">
                                <div class="mt-1">
                                    <template x-if="stat.is_requirement_met">
                                        <button type="button" @click="$dispatch('claim-quest', stat.key)" :disabled="isClaiming[stat.key]"
                                            class="h-7 px-3 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-bold text-[10px] uppercase tracking-wider rounded-lg transition-all active:scale-[0.98] flex items-center justify-center gap-1 shadow-sm shadow-orange-500/20">
                                            <span x-show="isClaiming[stat.key]" class="material-symbols-outlined text-[12px] animate-spin">refresh</span>
                                            <span>Nhận thưởng</span>
                                        </button>
                                    </template>
                                    <template x-if="!stat.is_requirement_met">
                                        <div>
                                            <template x-if="stat.key === 'verify_email'">
                                                <button type="button" 
                                                    @click="
                                                        $dispatch('close-modal', 'xp-missions-modal');
                                                        setActiveAction('setting');
                                                        $nextTick(() => {
                                                            settingTab = 'security';
                                                            activeSecurityCollapse = 'verification';
                                                        });
                                                    "
                                                    class="h-7 px-3 bg-primary hover:bg-primary/95 text-white font-bold text-[10px] uppercase tracking-wider rounded-lg transition-all active:scale-[0.98] shadow-sm shadow-primary/20">
                                                    Xác thực ngay
                                                </button>
                                            </template>
                                            <template x-if="stat.key !== 'verify_email'">
                                                <span class="text-[10px] text-app-muted italic">Chưa đủ điều kiện</span>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
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
                        <div
                            class="p-3.5 rounded-xl bg-green-500/10 border border-green-500/20 flex items-center gap-3 text-green-500 dark:text-green-400">
                            <span class="material-symbols-outlined text-[20px] animate-pulse">verified</span>
                            <div class="text-xs font-bold">
                                Đây là cấp bậc thành viên hiện tại của bạn
                            </div>
                        </div>
                    </template>

                    {{-- Thẻ thông tin lớn --}}
                    <div
                        class="p-5 rounded-xl border border-app-border flex flex-col items-center justify-center gap-3 bg-app-main/20 relative overflow-hidden">
                        {{-- Background Glow --}}
                        <div class="absolute -right-8 -top-8 size-24 rounded-full blur-2xl opacity-20 pointer-events-none"
                            :style="`background-color: ${selectedTier.color}`"></div>

                        <span class="text-5xl select-none" x-text="selectedTier.icon"
                            :style="`filter: drop-shadow(0 8px 12px ${selectedTier.color}30)`"></span>
                        <div class="flex flex-col items-center text-center mt-1">
                            <span class="text-lg font-extrabold text-app-text" x-text="selectedTier.label"
                                :style="`color: ${selectedTier.color}`"></span>
                            <span class="text-xs text-app-muted mt-1">
                                Yêu cầu tối thiểu: <strong class="text-app-text font-bold"
                                    x-text="`${selectedTier.minXp.toLocaleString()} XP`"></strong>
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
                                    <span class="text-xs font-bold text-app-text"
                                        x-text="`Giảm ${selectedTier.discount}%`"></span>
                                </div>
                            </div>
                            <div class="p-3 bg-app-main border border-app-border rounded-xl flex items-center gap-3">
                                <span class="material-symbols-outlined text-[22px] text-blue-500">ads_click</span>
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-app-muted">Mật độ quảng cáo</span>
                                    <span class="text-xs font-bold text-app-text"
                                        x-text="`Còn ${selectedTier.adPercent}%`"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Mô tả thêm cho từng cấp bậc để tạo cảm giác premium --}}
                    <div class="p-4 bg-app-main/30 border border-app-border/60 rounded-xl">
                        <h4 class="text-xs font-bold text-app-muted mb-1.5 uppercase tracking-wider">Chi tiết đặc quyền
                        </h4>
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