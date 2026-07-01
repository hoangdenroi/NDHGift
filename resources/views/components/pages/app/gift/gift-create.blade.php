<x-app-layout :title="__('Chỉnh sửa quà tặng - NDHGift')">

    {{-- Tiêu đề trang & Breadcrumbs --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-app-border/40 pb-5">
        <div class="flex flex-col gap-1">
            <h1 class="text-2xl font-bold text-app-text">{{ __('Thiết kế quà tặng') }}</h1>
            <p class="text-app-muted text-sm">{{ $giftTemplate->name }}</p>
        </div>
        <!-- Breadcrumbs -->
        <nav class="hidden md:flex items-center gap-1 text-xs font-semibold text-app-muted" aria-label="Breadcrumb">
            <a href="{{ route('app.home.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                NDHGift
            </a>
            <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
            <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}"
                class="hover:text-primary transition-colors">
                {{ __('Gift Templates') }}
            </a>
            <span class="material-symbols-outlined text-[22px] text-app-muted/40 select-none">chevron_right</span>
            <span class="text-app-text">{{ __('Thiết kế') }}</span>
        </nav>
    </div>

    {{-- Layout chia làm 2 cột: Trái Editor, Phải Live Preview --}}
    <div x-data="giftEditor()" class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

        {{-- CỘT TRÁI: FORM EDITOR --}}
        <div class="lg:col-span-7 xl:col-span-8 space-y-6">

            <!-- Thẻ thông tin Template chính -->
            <div class="bg-app-surface border border-app-border rounded-2xl p-5 flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                <img src="{{ $giftTemplate->image_url ?? asset('assets/images/default-gift.png') }}"
                    alt="{{ $giftTemplate->name }}"
                    class="w-20 h-20 object-cover rounded-xl border border-app-border">
                <div class="space-y-1">
                    <h2 class="text-lg font-bold text-app-text">{{ $giftTemplate->name }}</h2>
                    <p class="text-xs text-app-muted line-clamp-2">{{ $giftTemplate->description }}</p>
                    <div class="flex items-center gap-2 pt-1">
                        <span class="text-sm font-extrabold text-primary">
                            {{ number_format($giftTemplate->price - ($giftTemplate->price * $giftTemplate->discount / 100)) }}đ
                        </span>
                        @if($giftTemplate->discount > 0)
                            <span class="text-xs text-app-muted line-through">
                                {{ number_format($giftTemplate->price) }}đ
                            </span>
                            <span class="text-[10px] font-bold bg-rose-500/10 text-rose-500 px-1.5 py-0.5 rounded">
                                -{{ $giftTemplate->discount }}%
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bước 1: Nội dung thiệp/quà tặng -->
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 space-y-5">
                <div class="flex items-center gap-2 border-b border-app-border/40 pb-3">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold">1</span>
                    <h3 class="text-base font-bold text-app-text">{{ __('Nội dung quà tặng') }}</h3>
                </div>

                <div class="space-y-4">
                    @if(isset($giftTemplate->form_schema['fields']) && is_array($giftTemplate->form_schema['fields']))
                        @foreach($giftTemplate->form_schema['fields'] as $field)
                            @php
                                $key = $field['key'] ?? '';
                                $type = $field['type'] ?? 'text';
                                $label = $field['label'] ?? '';
                                $placeholder = $field['placeholder'] ?? '';
                                $required = $field['required'] ?? false;
                            @endphp

                            <div class="space-y-1.5">
                                <label class="text-xs font-bold text-app-text/80 flex items-center gap-1">
                                    <span>{{ __($label) }}</span>
                                    @if($required)
                                        <span class="text-rose-500">*</span>
                                    @endif
                                </label>

                                @if($type === 'textarea')
                                    <textarea x-model="formData.{{ $key }}"
                                        placeholder="{{ __($placeholder) }}"
                                        rows="4"
                                        class="w-full bg-app-bg border border-app-border focus:border-primary/80 focus:ring-1 focus:ring-primary/40 rounded-xl px-4 py-2.5 text-xs text-app-text outline-none transition-all placeholder:text-app-muted/50 resize-none"
                                        {{ $required ? 'required' : '' }}></textarea>
                                @elseif($type === 'number')
                                    <input type="number"
                                        x-model.number="formData.{{ $key }}"
                                        placeholder="{{ __($placeholder) }}"
                                        class="w-full bg-app-bg border border-app-border focus:border-primary/80 focus:ring-1 focus:ring-primary/40 rounded-xl px-4 py-2.5 text-xs text-app-text outline-none transition-all placeholder:text-app-muted/50"
                                        {{ $required ? 'required' : '' }}>
                                @elseif($type === 'date')
                                    <input type="date"
                                        x-model="formData.{{ $key }}"
                                        class="w-full bg-app-bg border border-app-border focus:border-primary/80 focus:ring-1 focus:ring-primary/40 rounded-xl px-4 py-2.5 text-xs text-app-text outline-none transition-all text-left"
                                        {{ $required ? 'required' : '' }}>
                                @elseif($type === 'image')
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex-1">
                                            <input type="file"
                                                @change="handleFileUpload($event, '{{ $key }}', 'image')"
                                                accept="image/*"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <div class="w-full bg-app-bg border border-app-border rounded-xl px-4 py-2.5 text-xs text-app-muted/70 flex items-center gap-2 justify-center hover:bg-app-border/20 transition-all">
                                                <span class="material-symbols-outlined text-[18px]">upload</span>
                                                <span x-text="fileNames.{{ $key }} || '{{ __('Chọn ảnh tải lên...') }}'"></span>
                                            </div>
                                        </div>
                                        <template x-if="formData.{{ $key }}">
                                            <div class="relative w-10 h-10 border border-app-border rounded-lg overflow-hidden flex-shrink-0 bg-app-bg">
                                                <img :src="formData.{{ $key }}" class="w-full h-full object-cover">
                                                <button @click="removeFile('{{ $key }}')" type="button" class="absolute inset-0 bg-black/60 flex items-center justify-center text-white opacity-0 hover:opacity-100 transition-all">
                                                    <span class="material-symbols-outlined text-[16px]">close</span>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                @elseif($type === 'music')
                                    <div class="flex items-center gap-4">
                                        <div class="relative flex-1">
                                            <input type="file"
                                                @change="handleFileUpload($event, '{{ $key }}', 'music')"
                                                accept="audio/*"
                                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                            <div class="w-full bg-app-bg border border-app-border rounded-xl px-4 py-2.5 text-xs text-app-muted/70 flex items-center gap-2 justify-center hover:bg-app-border/20 transition-all">
                                                <span class="material-symbols-outlined text-[18px]">music_note</span>
                                                <span x-text="fileNames.{{ $key }} || '{{ __('Tải nhạc nền lên...') }}'"></span>
                                            </div>
                                        </div>
                                        <template x-if="formData.{{ $key }}">
                                            <button @click="removeFile('{{ $key }}')" type="button" class="flex-shrink-0 w-10 h-10 rounded-lg bg-rose-500/10 text-rose-500 border border-rose-500/20 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                                <span class="material-symbols-outlined text-[18px]">close</span>
                                            </button>
                                        </template>
                                    </div>
                                @else
                                    <input type="text"
                                        x-model="formData.{{ $key }}"
                                        placeholder="{{ __($placeholder) }}"
                                        class="w-full bg-app-bg border border-app-border focus:border-primary/80 focus:ring-1 focus:ring-primary/40 rounded-xl px-4 py-2.5 text-xs text-app-text outline-none transition-all placeholder:text-app-muted/50"
                                        {{ $required ? 'required' : '' }}>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Bước 2: Thời gian hiệu lực -->
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 space-y-5">
                <div class="flex items-center gap-2 border-b border-app-border/40 pb-3">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold">2</span>
                    <h3 class="text-base font-bold text-app-text">{{ __('Thời gian hiệu lực') }}</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($durationPlans as $plan)
                        <label class="relative flex flex-col justify-between p-4 border rounded-2xl cursor-pointer select-none transition-all hover:bg-app-border/10"
                            :class="selectedDurationId === '{{ $plan->id }}' ? 'border-primary bg-primary/5 ring-1 ring-primary/40' : 'border-app-border bg-app-bg/50'">
                            <input type="radio" name="duration_plan" value="{{ $plan->id }}"
                                @change="selectDuration({{ json_encode($plan) }})"
                                class="sr-only" {{ $plan->code === '15d' ? 'checked' : '' }}>
                            
                            <div class="space-y-1">
                                <span class="block text-xs font-extrabold text-app-text">{{ $plan->name }}</span>
                                <span class="block text-[10px] text-app-muted leading-relaxed line-clamp-2">{{ $plan->description }}</span>
                            </div>
                            
                            <div class="pt-3 border-t border-app-border/30 mt-3 flex items-center justify-between">
                                <span class="text-xs font-extrabold text-primary">
                                    {{ $plan->price > 0 ? number_format($plan->price) . 'đ' : __('Miễn phí') }}
                                </span>
                                <span class="material-symbols-outlined text-[16px] text-primary" x-show="selectedDurationId === '{{ $plan->id }}'">check_circle</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Bước 3: Hiệu ứng Premium (Premium Effects) -->
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 space-y-5">
                <div class="flex items-center gap-2 border-b border-app-border/40 pb-3">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold">3</span>
                    <h3 class="text-base font-bold text-app-text">{{ __('Hiệu ứng đặc biệt (Premium)') }}</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($giftEffects as $effect)
                        <label class="relative flex gap-3 p-4 border rounded-2xl cursor-pointer select-none transition-all hover:bg-app-border/10"
                            :class="isEffectSelected('{{ $effect->id }}') ? 'border-primary bg-primary/5 ring-1 ring-primary/40' : 'border-app-border bg-app-bg/50'">
                            <input type="checkbox" value="{{ $effect->id }}"
                                @change="toggleEffect({{ json_encode($effect) }})"
                                class="sr-only">
                            
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-app-bg border border-app-border flex items-center justify-center text-primary">
                                <span class="material-symbols-outlined text-[20px]">{{ $effect->icon ?? 'sparkles' }}</span>
                            </div>

                            <div class="flex-1 space-y-0.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-app-text">{{ $effect->name }}</span>
                                    <span class="text-xs font-extrabold text-primary">+{{ number_format($effect->price) }}đ</span>
                                </div>
                                <p class="text-[10px] text-app-muted leading-relaxed line-clamp-2">{{ $effect->description }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Bước 4: Thiết lập bổ sung (Slug & Hẹn giờ) -->
            <div class="bg-app-surface border border-app-border rounded-2xl p-6 space-y-5">
                <div class="flex items-center gap-2 border-b border-app-border/40 pb-3">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-primary/10 text-primary text-xs font-bold">4</span>
                    <h3 class="text-base font-bold text-app-text">{{ __('Cài đặt liên kết & hẹn giờ') }}</h3>
                </div>

                <div class="divide-y divide-app-border/40 space-y-4">
                    <!-- Tùy chỉnh Slug -->
                    <div class="pt-0 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <h4 class="text-xs font-bold text-app-text flex items-center gap-1.5">
                                    <span>{{ __('Tự chọn đường dẫn liên kết (Custom Slug)') }}</span>
                                    <span class="text-[10px] font-extrabold bg-primary/10 text-primary px-1.5 py-0.5 rounded">+10,000đ</span>
                                </h4>
                                <p class="text-[10px] text-app-muted">{{ __('Mặc định liên kết gồm 12 ký tự ngẫu nhiên. Bật để tự chọn (VD: /g/my-anniversary)') }}</p>
                            </div>
                            <!-- Switch -->
                            <button type="button" @click="customSlug = !customSlug"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="customSlug ? 'bg-primary' : 'bg-app-border'">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="customSlug ? 'translate-x-4' : 'translate-x-0'"></span>
                            </button>
                        </div>

                        <div x-show="customSlug" x-collapse>
                            <div class="flex items-center bg-app-bg border border-app-border rounded-xl px-3 py-2">
                                <span class="text-xs text-app-muted select-none font-semibold">ndhgift.com/g/</span>
                                <input type="text" x-model="slugValue"
                                    placeholder="your-custom-link"
                                    class="flex-1 bg-transparent border-none text-xs text-app-text outline-none p-0 focus:ring-0">
                            </div>
                        </div>
                    </div>

                    <!-- Hẹn giờ mở quà -->
                    <div class="pt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="space-y-0.5">
                                <h4 class="text-xs font-bold text-app-text flex items-center gap-1.5">
                                    <span>{{ __('Hẹn giờ gửi quà') }}</span>
                                    <span class="text-[10px] font-extrabold bg-green-500/10 text-green-500 px-1.5 py-0.5 rounded">{{ __('Miễn phí') }}</span>
                                </h4>
                                <p class="text-[10px] text-app-muted">{{ __('Liên kết quà tặng sẽ bị khóa và đếm ngược cho đến thời điểm được đặt.') }}</p>
                            </div>
                            <!-- Switch -->
                            <button type="button" @click="scheduled = !scheduled"
                                class="relative inline-flex h-5 w-9 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none"
                                :class="scheduled ? 'bg-primary' : 'bg-app-border'">
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="scheduled ? 'translate-x-4' : 'translate-x-0'"></span>
                            </button>
                        </div>

                        <div x-show="scheduled" x-collapse>
                            <input type="datetime-local" x-model="scheduledAt"
                                class="w-full bg-app-bg border border-app-border focus:border-primary/80 focus:ring-1 focus:ring-primary/40 rounded-xl px-4 py-2.5 text-xs text-app-text outline-none transition-all text-left">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- CỘT PHẢI: LIVE PREVIEW SIDEBAR --}}
        <div class="lg:col-span-5 xl:col-span-4 lg:sticky lg:top-6 space-y-6">

            <!-- Card Preview trực quan -->
            <div class="bg-app-surface border border-app-border rounded-2xl overflow-hidden shadow-md">
                <!-- Preview Header -->
                <div class="border-b border-app-border/40 px-5 py-3.5 flex items-center justify-between bg-app-border/5">
                    <span class="text-xs font-bold text-app-text flex items-center gap-1.5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                        </span>
                        <span>{{ __('Xem trước (Live Preview)') }}</span>
                    </span>
                    <button type="button" class="text-app-muted hover:text-app-text flex items-center gap-0.5 text-xs font-semibold">
                        <span class="material-symbols-outlined text-[16px]">fullscreen</span>
                        <span>{{ __('Toàn màn hình') }}</span>
                    </button>
                </div>

                <!-- Mô phỏng màn hình điện thoại di động -->
                <div class="p-6 bg-app-bg/30 flex justify-center">
                    <div class="relative w-full max-w-[280px] aspect-[9/16] bg-black rounded-[36px] p-2.5 shadow-2xl border-4 border-app-border/80 overflow-hidden flex flex-col justify-between">
                        
                        <!-- Hiệu ứng hạt giả lập bay lơ lửng nếu có chọn premium effect -->
                        <div class="absolute inset-0 pointer-events-none z-10 overflow-hidden">
                            <!-- Tuyết rơi -->
                            <template x-if="hasEffect('snow_fall')">
                                <div class="absolute inset-0 snow-container"></div>
                            </template>
                            <!-- Pháo hoa -->
                            <template x-if="hasEffect('fireworks')">
                                <div class="absolute inset-0 fireworks-container flex items-center justify-center">
                                    <span class="animate-ping absolute inline-flex h-12 w-12 rounded-full bg-yellow-500/20"></span>
                                    <span class="animate-ping absolute inline-flex h-24 w-24 rounded-full bg-amber-500/10"></span>
                                </div>
                            </template>
                            <!-- Mưa giấy -->
                            <template x-if="hasEffect('confetti')">
                                <div class="absolute inset-0 confetti-container"></div>
                            </template>
                            <!-- Mưa tim -->
                            <template x-if="hasEffect('heart_rain')">
                                <div class="absolute inset-0 heart-rain-container"></div>
                            </template>
                        </div>

                        <!-- Camera/Tai thỏ giả lập -->
                        <div class="absolute top-4 left-1/2 -translate-x-1/2 w-20 h-4 bg-black rounded-full z-20"></div>

                        <!-- Nội dung quà tặng mô phỏng trên nền tối/sáng cực đẹp -->
                        <div class="w-full h-full rounded-[28px] overflow-hidden relative bg-gradient-to-br from-indigo-950 via-slate-900 to-rose-950 p-4 flex flex-col justify-between text-center text-white">
                            
                            <!-- Header mô phỏng -->
                            <div class="flex justify-between items-center text-[8px] text-white/50 pt-2 z-10">
                                <span>9:41</span>
                                <div class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[10px]">signal_cellular_alt</span>
                                    <span class="material-symbols-outlined text-[10px]">wifi</span>
                                    <span class="material-symbols-outlined text-[10px]">battery_full</span>
                                </div>
                            </div>

                            <!-- Lời nhắn chính -->
                            <div class="my-auto space-y-3 px-2 z-10">
                                <!-- Tên người nhận -->
                                <h3 class="text-sm font-extrabold text-pink-300 drop-shadow-md"
                                    x-text="formData.receiver_name || '{{ __('Người Nhận') }}'"></h3>
                                
                                <!-- Ảnh đôi / Ảnh người nhận tải lên (mockup) -->
                                <div class="w-24 h-24 mx-auto border-2 border-white/20 rounded-full overflow-hidden shadow-lg bg-slate-800 flex items-center justify-center">
                                    <template x-if="formData.photo">
                                        <img :src="formData.photo" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!formData.photo">
                                        <span class="material-symbols-outlined text-white/30 text-3xl">favorite</span>
                                    </template>
                                </div>

                                <!-- Tin nhắn chúc -->
                                <p class="text-[10px] text-white/80 leading-relaxed break-words line-clamp-5 drop-shadow"
                                    x-text="formData.message || '{{ __('Nội dung tin nhắn yêu thương của bạn sẽ xuất hiện tại đây...') }}'"></p>
                            </div>

                            <!-- Footer mô phỏng -->
                            <div class="space-y-1 pb-2 z-10">
                                <span class="block text-[8px] text-white/40">{{ __('Gửi từ') }}</span>
                                <span class="block text-[10px] font-bold text-pink-200"
                                    x-text="formData.sender_name || '{{ __('Người Gửi') }}'"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Bảng tóm tắt chi phí thanh toán -->
                <div class="bg-app-border/10 p-5 border-t border-app-border/40 space-y-3">
                    <h3 class="text-xs font-bold text-app-text mb-1">{{ __('Tóm tắt đơn hàng') }}</h3>
                    
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-app-muted">
                            <span>{{ __('Giá mẫu (Template)') }}</span>
                            <span class="font-bold text-app-text">
                                {{ number_format($giftTemplate->price - ($giftTemplate->price * $giftTemplate->discount / 100)) }}đ
                            </span>
                        </div>
                        <div class="flex justify-between text-app-muted">
                            <span>{{ __('Gói thời hạn') }}</span>
                            <span class="font-bold text-app-text" x-text="durationPrice > 0 ? formatPrice(durationPrice) + 'đ' : '{{ __('Miễn phí') }}'"></span>
                        </div>
                        <div class="flex justify-between text-app-muted" x-show="totalEffectsPrice > 0">
                            <span>{{ __('Hiệu ứng Premium') }}</span>
                            <span class="font-bold text-app-text" x-text="'+' + formatPrice(totalEffectsPrice) + 'đ'"></span>
                        </div>
                        <div class="flex justify-between text-app-muted" x-show="customSlug">
                            <span>{{ __('Custom Slug') }}</span>
                            <span class="font-bold text-app-text">+10,000đ</span>
                        </div>
                    </div>

                    <!-- Tổng thanh toán -->
                    <div class="pt-3 border-t border-app-border/40 flex items-center justify-between">
                        <span class="text-xs font-bold text-app-text">{{ __('Tổng tiền dự kiến') }}</span>
                        <div class="text-right">
                            <span class="text-base font-extrabold text-primary" x-text="formatPrice(calculateTotal()) + 'đ'"></span>
                        </div>
                    </div>

                    <!-- Nút Tạo quà -->
                    <button type="button" @click="submitGift()"
                        class="w-full mt-2 py-3 bg-primary hover:bg-primary/95 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-primary/10 active:scale-[0.98] flex items-center justify-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">celebration</span>
                        <span>{{ __('Tạo quà ngay') }}</span>
                    </button>
                </div>
            </div>

        </div>

    </div>

    {{-- Phong cách giả lập tuyết rơi, tim bay cho Live Preview --}}
    <style>
        .snow-container::before {
            content: "❄ ❅ ❆ ❄ ❅ ❆";
            position: absolute;
            top: -20px;
            left: 0;
            width: 100%;
            font-size: 8px;
            color: rgba(255, 255, 255, 0.7);
            white-space: nowrap;
            animation: fall 3s linear infinite;
        }
        .heart-rain-container::before {
            content: "❤ ❤ ❤ ❤";
            position: absolute;
            top: -20px;
            left: 0;
            width: 100%;
            font-size: 10px;
            color: rgba(244, 63, 94, 0.8);
            white-space: nowrap;
            animation: fall 2.5s linear infinite;
        }
        @keyframes fall {
            0% { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(280px) rotate(360deg); opacity: 0.2; }
        }
    </style>

    {{-- Alpine.js State Management --}}
    <script>
        function giftEditor() {
            // Đọc form schema ban đầu từ backend
            const schemaFields = @json($giftTemplate->form_schema['fields'] ?? []);
            
            // Khởi tạo formData trống dựa trên schema keys
            const initialData = {};
            const initialFileNames = {};
            schemaFields.forEach(f => {
                initialData[f.key] = '';
                if (f.type === 'image' || f.type === 'music') {
                    initialFileNames[f.key] = '';
                }
            });

            return {
                formData: initialData,
                fileNames: initialFileNames,

                // Gói thời hạn
                selectedDurationId: '{{ $durationPlans->firstWhere('code', '15d')?->id ?? '' }}',
                durationPrice: parseFloat('{{ $durationPlans->firstWhere('code', '15d')?->price ?? 0 }}'),

                // Hiệu ứng premium
                selectedEffects: [],

                // Custom Slug & Hẹn giờ
                customSlug: false,
                slugValue: '',
                scheduled: false,
                scheduledAt: '',

                // Giá template gốc sau chiết khấu
                basePrice: parseFloat('{{ $giftTemplate->price - ($giftTemplate->price * $giftTemplate->discount / 100) }}'),

                // Tổng giá hiệu ứng
                get totalEffectsPrice() {
                    return this.selectedEffects.reduce((sum, effect) => sum + parseFloat(effect.price), 0);
                },

                // Xử lý chọn gói thời hạn
                selectDuration(plan) {
                    this.selectedDurationId = plan.id;
                    this.durationPrice = parseFloat(plan.price);
                },

                // Thêm/Xóa hiệu ứng
                toggleEffect(effect) {
                    const idx = this.selectedEffects.findIndex(e => e.id === effect.id);
                    if (idx > -1) {
                        this.selectedEffects.splice(idx, 1);
                    } else {
                        this.selectedEffects.push(effect);
                    }
                },

                // Kiểm tra hiệu ứng có được chọn không
                isEffectSelected(effectId) {
                    return this.selectedEffects.some(e => e.id == effectId);
                },

                // Kiểm tra hiệu ứng code có tồn tại để hiển thị preview animation
                hasEffect(code) {
                    return this.selectedEffects.some(e => e.code === code);
                },

                // Upload file giả lập cho live preview
                handleFileUpload(event, key, type) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.fileNames[key] = file.name;

                    // Đọc file thành base64 để hiển thị live preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.formData[key] = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                // Xóa file đã chọn
                removeFile(key) {
                    this.formData[key] = '';
                    this.fileNames[key] = '';
                },

                // Tính tổng tiền
                calculateTotal() {
                    let total = this.basePrice + this.durationPrice + this.totalEffectsPrice;
                    if (this.customSlug) {
                        total += 10000; // Giá custom slug cố định 10,000 VND
                    }
                    return total;
                },

                // Format tiền tệ
                formatPrice(val) {
                    return new Intl.NumberFormat('vi-VN').format(val);
                },

                // Gửi thông tin tạo quà tặng lên backend
                submitGift() {
                    // Sẽ xử lý logic Ajax gửi request post lên backend lưu bản ghi user_gifts ở trạng thái draft.
                    // Đồng thời redirect sang cổng thanh toán.
                    alert('{{ __('Đang chuẩn bị tạo quà tặng và thanh toán...') }}');
                }
            }
        }
    </script>

</x-app-layout>
