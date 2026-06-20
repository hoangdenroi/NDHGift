{{-- Component Chỉnh sửa thông tin cá nhân --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">{{ __('Personal Information') }}</h2>
            <p class="text-sm text-app-muted mt-0.5">{{ __('Update your display information and contact details') }}</p>
        </div>
    </div>
    
    <form id="profile-form" method="POST" action="{{ route('app.profile.update', ['locale' => app()->getLocale()]) }}"
        enctype="multipart/form-data" class="p-6 space-y-5">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-app-text">{{ __('Full Name') }}</label>
                <input type="text" name="fullname" value="{{ old('fullname', $user->fullname ?? '') }}" required
                    class="w-full h-11 px-4 rounded-xl border border-app-border bg-app-main text-app-text placeholder:text-app-muted text-sm focus:border-primary focus:ring-1 focus:ring-primary transition-colors outline-none" />
                @error('fullname')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-app-text">{{ __('Email') }}</label>
                <input type="email" value="{{ $user->email ?? '' }}" disabled
                    class="w-full h-11 px-4 rounded-xl border border-app-border bg-app-main text-app-muted text-sm cursor-not-allowed outline-none" />
            </div>
            
            <div class="space-y-1.5">
                <label class="block text-sm font-medium text-app-text">{{ __('Phone Number') }}</label>
                <input type="tel" name="phone" value="{{ old('phone', $user->phone ?? '') }}"
                    placeholder="{{ __('Enter phone number') }}"
                    class="w-full h-11 px-4 rounded-xl border border-app-border bg-app-main text-app-text placeholder:text-app-muted text-sm focus:border-primary focus:ring-1 focus:ring-primary transition-colors outline-none" />
                @error('phone')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="space-y-1.5 flex flex-col justify-end pb-1">
                <label class="block text-sm font-medium text-app-text">{{ __('Profile Picture') }}</label>
                <p class="text-xs text-app-muted">{{ __('To change your profile picture, please click directly on the Avatar image on the left column.') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="h-10 px-6 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98]">
                {{ __('Save Changes') }}
            </button>
        </div>
    </form>
</div>
