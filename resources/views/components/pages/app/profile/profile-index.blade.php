<x-app-layout :title="__('Profile - NDHGift')">
    {{-- Header --}}
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold text-app-text">Hồ sơ</h1>
        <p class="text-app-muted text-sm">Cập nhật thông tin cá nhân và cài đặt hiển thị</p>
    </div>
    {{-- Tab Hồ sơ: Avatar + Thông tin cơ bản + Form chỉnh sửa --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Cột trái: Avatar + Thông tin cơ bản --}}
        <div class="lg:col-span-1 flex flex-col gap-4">
            <div class="bg-app-surface border border-app-border rounded-xl p-6 flex flex-col items-center gap-4">
                <div class="relative group" x-data="{
                avatarPreview: '{{ $user->avatar_url }}',
                loading: false,
                handleFileChange(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                        if (!validTypes.includes(file.type)) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: 'Lỗi', message: 'Vui lòng chọn file hình ảnh hợp lệ (jpeg, png, jpg, gif).' } }));
                            event.target.value = '';
                            return;
                        }
                        if (file.size > 2 * 1024 * 1024) {
                            window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: 'Lỗi', message: 'Kích thước ảnh không được vượt quá 2MB.' } }));
                            event.target.value = '';
                            return;
                        }

                        this.loading = true;
                        this.avatarPreview = URL.createObjectURL(file);
                        // Tự động submit form sau khi chọn ảnh
                        document.getElementById('profile-form').submit();
                    }
                }
            }" x-init="() => {
                @error('avatar_file')
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', title: 'Lỗi tải ảnh', message: @js($message) } }));
                @enderror
            }">
                    <div class="size-24 rounded-full overflow-hidden border-4 border-app-border relative">
                        <template x-if="avatarPreview">
                            <img :src="avatarPreview" alt="Avatar" class="size-full object-cover"
                                :class="{'opacity-50': loading}">
                        </template>
                        <template x-if="!avatarPreview">
                            <div class="size-full bg-primary/10 flex items-center justify-center">
                                <span class="material-symbols-outlined text-primary text-4xl">person</span>
                            </div>
                        </template>

                        {{-- Loading Spinner --}}
                        <div x-show="loading" class="absolute inset-0 flex items-center justify-center bg-black/20">
                            <span class="material-symbols-outlined text-white animate-spin">refresh</span>
                        </div>
                    </div>
                    <label x-show="!loading"
                        class="absolute inset-0 size-24 rounded-full bg-black/40 flex items-center justify-center opacity-0 lg:group-hover:opacity-100 transition-opacity cursor-pointer">
                        <span class="material-symbols-outlined text-white">photo_camera</span>
                        <input type="file" name="avatar_file" form="profile-form" accept="image/*" class="hidden"
                            @change="handleFileChange">
                    </label>
                </div>
                <div class="text-center">
                    <h3 class="text-base font-bold text-app-text">{{ $user->fullname }}</h3>
                    <p class="text-sm text-app-muted mt-0.5">{{ $user->email }}</p>
                </div>
                <div class="w-full flex flex-col gap-2 pt-2 border-t border-app-border">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-app-muted">Số dư</span>
                        <span
                            class="text-app-text font-semibold">{{ number_format($user->balance, 0, ',', '.') }}đ</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-app-muted">Mã tài khoản</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary/10 text-primary">
                            {{ $user->unitcode }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-app-muted">Ngày tạo</span>
                        <span class="text-app-text">{{ $user->created_at->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- 3 Button chức năng nhanh dưới Avatar --}}
            <div class="grid grid-cols-3 gap-4">
                {{-- Nút Thông báo --}}
                <a href="#" 
                    class="flex flex-col items-center justify-center p-4 bg-app-surface border border-app-border rounded-xl hover:border-primary/50 hover:bg-primary/5 transition-all duration-300 group shadow-sm">
                    <span class="material-symbols-outlined text-[26px] text-app-muted group-hover:text-primary transition-colors duration-300">
                        notifications
                    </span>
                    <span class="text-[11px] font-semibold text-app-text mt-1.5 group-hover:text-primary transition-colors duration-300">
                        Thông báo
                    </span>
                </a>

                {{-- Nút Voucher --}}
                <a href="{{ route('app.gift.index', ['locale' => app()->getLocale()]) }}" 
                    class="flex flex-col items-center justify-center p-4 bg-app-surface border border-app-border rounded-xl hover:border-primary/50 hover:bg-primary/5 transition-all duration-300 group shadow-sm">
                    <span class="material-symbols-outlined text-[26px] text-app-muted group-hover:text-primary transition-colors duration-300">
                        redeem
                    </span>
                    <span class="text-[11px] font-semibold text-app-text mt-1.5 group-hover:text-primary transition-colors duration-300">
                        Voucher
                    </span>
                </a>

                {{-- Nút Lịch sử --}}
                <a href="#" 
                    class="flex flex-col items-center justify-center p-4 bg-app-surface border border-app-border rounded-xl hover:border-primary/50 hover:bg-primary/5 transition-all duration-300 group shadow-sm">
                    <span class="material-symbols-outlined text-[26px] text-app-muted group-hover:text-primary transition-colors duration-300">
                        history
                    </span>
                    <span class="text-[11px] font-semibold text-app-text mt-1.5 group-hover:text-primary transition-colors duration-300">
                        Lịch sử
                    </span>
                </a>
            </div>
        </div>

        {{-- Cột phải: Form chỉnh sửa hồ sơ --}}
        <div class="lg:col-span-2">
            <div class="bg-app-surface border border-app-border rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-app-border">
                    <h2 class="text-base font-bold text-app-text">Thông tin cá nhân</h2>
                    <p class="text-sm text-app-muted mt-0.5">Cập nhật thông tin hiển thị của bạn</p>
                </div>
                <form id="profile-form" method="POST" action="#" enctype="multipart/form-data" class="p-6 space-y-5">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-app-text">Họ và tên</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                class="w-full h-11 px-4 rounded-xl border border-app-border bg-app-main text-app-text placeholder:text-app-muted text-sm focus:border-primary focus:ring-1 focus:ring-primary transition-colors" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-app-text">Email</label>
                            <input type="email" value="{{ $user->email }}" disabled
                                class="w-full h-11 px-4 rounded-xl border border-app-border bg-app-main text-app-muted text-sm cursor-not-allowed" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-app-text">Số điện thoại</label>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                                placeholder="Nhập số điện thoại"
                                class="w-full h-11 px-4 rounded-xl border border-app-border bg-app-main text-app-text placeholder:text-app-muted text-sm focus:border-primary focus:ring-1 focus:ring-primary transition-colors" />
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-app-text">Ảnh đại diện</label>
                            <p class="text-sm text-app-muted">Sử dụng ảnh đại diện mặc định hoặc click vào ảnh đại diện
                                để
                                tải lên</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                            class="h-10 px-6 bg-primary hover:bg-primary/90 text-white font-semibold text-sm rounded-xl transition-all shadow-sm shadow-primary/20 active:scale-[0.98]">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>