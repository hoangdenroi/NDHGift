{{-- Component Chính sách bảo mật --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">{{ __('Privacy Policy') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">{{ __('Last updated: June 20, 2026') }}</p>
        </div>
    </div>
    
    <div class="p-6 space-y-5 text-sm text-app-muted leading-relaxed max-h-[500px] overflow-y-auto scrollbar-thin">
        <p class="text-app-text font-semibold">{{ __('Your privacy is a top priority at NDHGift. This policy describes how we collect, use, and protect your personal information:') }}</p>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('1. Information We Collect') }}</h3>
            <p>{{ __('We only collect the information necessary to operate your account, including: Full name, email address, phone number, avatar, and login IP address for security authentication purposes.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('2. How We Use Information') }}</h3>
            <p>{{ __('Your information is used to personalize the admin dashboard, process template purchase transactions, send important account notifications, and support technical requests when you ask for help.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('3. Protecting and Sharing Information') }}</h3>
            <p>{{ __('NDHGift applies modern security encryption standards to prevent unauthorized access to your data. We strictly commit to not selling, sharing, or renting your personal information to any third party for commercial purposes.') }}</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">{{ __('4. User Control Over Data') }}</h3>
            <p>{{ __('You have full rights to change your personal information (such as full name, avatar, phone number) at any time directly on your profile page or request us to permanently delete your account data if no longer needed.') }}</p>
        </div>
    </div>
</div>
