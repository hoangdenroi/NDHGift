{{-- Component Hướng dẫn & Trợ giúp --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm" x-data="{ activeFaq: null }">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">{{ __('Help Center') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">{{ __('Find answers to frequently asked questions') }}</p>
        </div>
    </div>
    
    <div class="p-6 space-y-6">
        {{-- Section 1: FAQ Accordion --}}
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-app-text flex items-center gap-2 mb-4">
                <span class="material-symbols-outlined text-[20px] text-app-muted">live_help</span>
                {{ __('Frequently Asked Questions (FAQ)') }}
            </h3>
            
            <div class="space-y-2">
                {{-- FAQ 1 --}}
                <div class="border border-app-border rounded-xl overflow-hidden">
                    <button @click="activeFaq = activeFaq === 1 ? null : 1"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm text-app-text hover:bg-primary/5 transition-all">
                        <span>{{ __('How do I create a new gift page?') }}</span>
                        <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                            :class="activeFaq === 1 ? 'rotate-180 text-primary' : ''">keyboard_arrow_down</span>
                    </button>
                    <div x-show="activeFaq === 1" 
                        x-transition:enter="transition-all ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                        x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave="transition-all ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                        x-cloak class="px-5 pb-4 text-sm text-app-muted border-t border-app-border/50 pt-3">
                        {{ __('To create a gift page, just go to the NDHGift homepage, select a preferred gift template (Valentine, Birthday, etc.), configure the details (greetings, images), and click publish. The system will issue you a unique URL.') }}
                    </div>
                </div>
 
                {{-- FAQ 2 --}}
                <div class="border border-app-border rounded-xl overflow-hidden">
                    <button @click="activeFaq = activeFaq === 2 ? null : 2"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm text-app-text hover:bg-primary/5 transition-all">
                        <span>{{ __('How is my wallet balance topped up?') }}</span>
                        <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                            :class="activeFaq === 2 ? 'rotate-180 text-primary' : ''">keyboard_arrow_down</span>
                    </button>
                    <div x-show="activeFaq === 2" 
                        x-transition:enter="transition-all ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                        x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave="transition-all ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                        x-cloak class="px-5 pb-4 text-sm text-app-muted border-t border-app-border/50 pt-3">
                        {{ __('You can contact NDHGift customer support directly or make a bank transfer according to the exact syntax displayed on the NDHShop payment page to get your balance updated automatically within 1-3 minutes.') }}
                    </div>
                </div>
 
                {{-- FAQ 3 --}}
                <div class="border border-app-border rounded-xl overflow-hidden">
                    <button @click="activeFaq = activeFaq === 3 ? null : 3"
                        class="w-full flex items-center justify-between px-5 py-4 text-left font-semibold text-sm text-app-text hover:bg-primary/5 transition-all">
                        <span>{{ __('Can I customize the background music and images of the template?') }}</span>
                        <span class="material-symbols-outlined text-[20px] text-app-muted transition-transform duration-300"
                            :class="activeFaq === 3 ? 'rotate-180 text-primary' : ''">keyboard_arrow_down</span>
                    </button>
                    <div x-show="activeFaq === 3" 
                        x-transition:enter="transition-all ease-out duration-200"
                        x-transition:enter-start="opacity-0 max-h-0 overflow-hidden"
                        x-transition:enter-end="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave="transition-all ease-in duration-150"
                        x-transition:leave-start="opacity-100 max-h-40 overflow-hidden"
                        x-transition:leave-end="opacity-0 max-h-0 overflow-hidden"
                        x-cloak class="px-5 pb-4 text-sm text-app-muted border-t border-app-border/50 pt-3">
                        {{ __('Absolutely. Each template you create provides an intuitive interface that allows you to upload personal images, select background MP3 songs or YouTube links, and change greeting text very easily.') }}
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Section 2: Contact Support --}}
        <div class="pt-6 border-t border-app-border flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="space-y-0.5 text-center md:text-left">
                <h4 class="text-sm font-semibold text-app-text">{{ __('Do you still need help?') }}</h4>
                <p class="text-xs text-app-muted">{{ __('Our technical support team is always ready to assist you 24/7.') }}</p>
            </div>
            <a href="{{ route('app.support.index', ['locale' => app()->getLocale()]) }}"
                class="h-10 px-5 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 flex items-center gap-2 active:scale-95">
                <span class="material-symbols-outlined text-[18px]">support_agent</span>
                {{ __('Submit support request') }}
            </a>
        </div>
    </div>
</div>
