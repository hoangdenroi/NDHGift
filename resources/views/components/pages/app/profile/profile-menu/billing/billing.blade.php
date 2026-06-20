{{-- Component Hóa đơn thanh toán --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">{{ __('Invoice History') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">{{ __('List of your transaction invoices') }}</p>
        </div>
    </div>
    
    <div class="flex flex-col items-center justify-center py-16 px-6 text-center gap-4">
        <div class="size-16 rounded-full bg-primary/10 flex items-center justify-center text-primary">
            <span class="material-symbols-outlined text-3xl">receipt_long</span>
        </div>
        <div class="space-y-1">
            <h3 class="text-base font-bold text-app-text">{{ __('No invoices found') }}</h3>
            <p class="text-sm text-app-muted max-w-sm">
                {{ __('You have not made any payment transactions or purchased any premium templates on NDHGift yet.') }}
            </p>
        </div>
        <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}" 
            class="mt-2 h-10 px-5 bg-primary/10 hover:bg-primary/20 text-primary font-semibold text-sm rounded-xl transition-all flex items-center justify-center gap-2">
            <span class="material-symbols-outlined text-[18px]">explore</span>
            {{ __('Explore templates now') }}
        </a>
    </div>
</div>
