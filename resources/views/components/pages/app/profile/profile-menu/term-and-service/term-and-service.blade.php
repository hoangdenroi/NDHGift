{{-- Component Điều khoản và dịch vụ --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">{{ __('Terms of Service') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">{{ __('Last updated: June 20, 2026') }}</p>
        </div>
    </div>
    
    <div class="p-6 space-y-5 text-sm text-app-muted leading-relaxed max-h-[500px] overflow-y-auto scrollbar-thin">
        <p class="text-app-text font-semibold">{{ __('Welcome to the NDHGift gift page creation platform. By using our service, you agree to comply with the following terms:') }}</p>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('1. Account Ownership') }}</h3>
            <p>{{ __('Each account created on NDHGift is the personal property of the user. You are responsible for maintaining the confidentiality of your account password and are responsible for all activities that occur under your account.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('2. Lawful Use of Service') }}</h3>
            <p>{{ __('You commit to only using the service to create healthy, lawful gift page content, not violating trademark copyrights, and not inserting malicious code or phishing links. Any gift page with violating content will be permanently locked without prior notice.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('3. Transactions and Wallet Balance') }}</h3>
            <p>{{ __('NDHGift account wallet balance is used to purchase premium gift templates on the platform. Template purchase transactions after successful completion will not be refunded unless severe technical errors occur from the system side.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('4. Changes to Terms') }}</h3>
            <p>{{ __('NDHGift reserves the right to update, modify or supplement these terms of use at any time to comply with legal regulations and improve service quality. Changes will take effect immediately upon being posted on this website.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('5. Limitation of Liability') }}</h3>
            <p>{{ __('We are not responsible for any direct or indirect damages arising from the use or inability to use the service due to objective causes such as internet transmission errors of third parties or natural disasters.') }}</p>
        </div>
    </div>
</div>
