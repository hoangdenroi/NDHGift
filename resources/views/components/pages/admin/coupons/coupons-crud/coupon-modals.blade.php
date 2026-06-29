{{-- Declare strict types --}}
{{-- ==========================================
     MODAL TẠO MÃ GIẢM GIÁ MỚI
     ========================================== --}}
<x-shared.ui.modal name="create-coupon" maxWidth="lg">
    <div x-data="{ couponType: 'percent' }">
        <form method="POST" action="{{ route('admin.coupons.store') }}">
            @csrf
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">local_offer</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Thêm mã giảm giá</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Tạo mã giảm giá mới cho hệ thống</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'create-coupon')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- 1. Mã giảm giá (Full width) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mã giảm giá <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono uppercase text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="VD: TET2024" style="text-transform: uppercase;">
                    </div>

                    {{-- 2. Loại giảm giá | Giá trị giảm (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Loại giảm giá <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="type" x-model="couponType"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="percent">Theo phần trăm (%)</option>
                                    <option value="fixed">Cố định (đ)</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Giá trị giảm <span class="text-rose-500">*</span></label>
                            <input type="number" name="value" required min="0" :max="couponType === 'percent' ? 100 : ''" :step="couponType === 'percent' ? 1 : 1000"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                :placeholder="couponType === 'percent' ? 'Nhập phần trăm...' : 'Nhập số tiền giảm...'">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1" x-text="couponType === 'percent' ? 'Nhập phần trăm (0 - 100)' : 'Nhập số tiền giảm (VND)'"></p>
                        </div>
                    </div>

                    {{-- 3. Đơn hàng tối thiểu (đ) | Số lượng tối đa (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Đơn hàng tối thiểu (đ) <span class="text-rose-500">*</span></label>
                            <input type="number" name="min_order" min="0" step="1000" value="0" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="0">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Số lượng tối đa</label>
                            <input type="number" name="max_uses" min="1"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Để trống nếu không giới hạn">
                        </div>
                    </div>

                    {{-- 4. Ngày hết hạn (Full width) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ngày hết hạn</label>
                        <input type="datetime-local" name="expires_at"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Để trống nếu mã giảm giá áp dụng vĩnh viễn.</p>
                    </div>

                    {{-- Cấu hình phụ trợ ẩn hoặc gom hàng: Ngày bắt đầu | Phạm vi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ngày bắt đầu</label>
                            <input type="datetime-local" name="starts_at"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phạm vi</label>
                            <div class="relative">
                                <select name="status"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="public">Công khai</option>
                                    <option value="private">Riêng tư</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>
                    </div>

                    {{-- Trường giảm giá tối đa (chỉ hiện khi loại là phần trăm) --}}
                    <div x-show="couponType === 'percent'">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Giảm tối đa (đ)</label>
                        <input type="number" name="max_discount" min="0" step="1000"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Để trống nếu không giới hạn">
                    </div>

                    {{-- 5. Trạng thái kích hoạt (Switch) --}}
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-border-dark/50 pt-4 mt-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Trạng thái kích hoạt</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'create-coupon')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Tạo mới
                </button>
            </div>
        </form>
    </div>
</x-shared.ui.modal>

{{-- ==========================================
     MODAL CHỈNH SỬA MÃ GIẢM GIÁ
     ========================================== --}}
<div x-data="{
        editCoupon: { id: null, code: '', type: 'fixed', value: 0, max_discount: null, min_order: 0, max_uses: null, starts_at: '', expires_at: '', is_active: true, status: 'public' },
    }"
    @open-edit-coupon.window="editCoupon = $event.detail; $dispatch('open-modal', 'edit-coupon')">

    <x-shared.ui.modal name="edit-coupon" maxWidth="lg">
        <form :action="'{{ route('admin.coupons.index') }}/' + editCoupon.id" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">edit_document</span>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cập nhật mã giảm giá</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Chỉnh sửa thông tin mã giảm giá trong hệ thống</p>
                        </div>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-coupon')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-4">
                    {{-- 1. Mã giảm giá (Full width) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mã giảm giá <span class="text-rose-500">*</span></label>
                        <input type="text" name="code" x-model="editCoupon.code" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm font-mono uppercase text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="VD: TET2024" style="text-transform: uppercase;">
                    </div>

                    {{-- 2. Loại giảm giá | Giá trị giảm (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Loại giảm giá <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <select name="type" x-model="editCoupon.type"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="percent">Theo phần trăm (%)</option>
                                    <option value="fixed">Cố định (đ)</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Giá trị giảm <span class="text-rose-500">*</span></label>
                            <input type="number" name="value" x-model="editCoupon.value" required min="0" :max="editCoupon.type === 'percent' ? 100 : ''" :step="editCoupon.type === 'percent' ? 1 : 1000"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1" x-text="editCoupon.type === 'percent' ? 'Nhập phần trăm (0 - 100)' : 'Nhập số tiền giảm (VND)'"></p>
                        </div>
                    </div>

                    {{-- 3. Đơn hàng tối thiểu (đ) | Số lượng tối đa (2 cột) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Đơn hàng tối thiểu (đ) <span class="text-rose-500">*</span></label>
                            <input type="number" name="min_order" x-model="editCoupon.min_order" min="0" step="1000" required
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Số lượng tối đa</label>
                            <input type="number" name="max_uses" x-model="editCoupon.max_uses" min="1"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                                placeholder="Để trống nếu không giới hạn">
                        </div>
                    </div>

                    {{-- 4. Ngày hết hạn (Full width) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ngày hết hạn</label>
                        <input type="datetime-local" name="expires_at" x-model="editCoupon.expires_at"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Để trống nếu mã giảm giá áp dụng vĩnh viễn.</p>
                    </div>

                    {{-- Cấu hình phụ trợ ẩn hoặc gom hàng: Ngày bắt đầu | Phạm vi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Ngày bắt đầu</label>
                            <input type="datetime-local" name="starts_at" x-model="editCoupon.starts_at"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Phạm vi</label>
                            <div class="relative">
                                <select name="status" x-model="editCoupon.status"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="public">Công khai</option>
                                    <option value="private">Riêng tư</option>
                                </select>
                                <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>
                    </div>

                    {{-- Trường giảm giá tối đa (chỉ hiện khi loại là phần trăm) --}}
                    <div x-show="editCoupon.type === 'percent'">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Giảm tối đa (đ)</label>
                        <input type="number" name="max_discount" x-model="editCoupon.max_discount" min="0" step="1000"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Để trống nếu không giới hạn">
                    </div>

                    {{-- 5. Trạng thái kích hoạt (Switch) --}}
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-border-dark/50 pt-4 mt-2">
                        <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Trạng thái kích hoạt</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" :checked="editCoupon.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-coupon')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                    <span class="material-symbols-outlined text-[18px]">save</span>
                    Cập nhật
                </button>
            </div>
        </form>
    </x-shared.ui.modal>
</div>

{{-- ==========================================
     MODAL XÓA MÃ GIẢM GIÁ
     ========================================== --}}
<div x-data="{
        deleteCoupon: { id: null, code: '' },
    }"
    @open-delete-coupon.window="deleteCoupon = $event.detail; $dispatch('open-modal', 'delete-coupon')">

    <x-shared.ui.modal name="delete-coupon" maxWidth="md">
        <form :action="'{{ route('admin.coupons.index') }}/' + deleteCoupon.id" method="POST">
            @csrf
            @method('DELETE')
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-rose-500 text-[20px]">delete_forever</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Xóa mã giảm giá</h3>
                        <p class="text-sm text-slate-500">
                            Bạn có chắc muốn xóa mã <code class="font-mono font-bold text-primary" x-text="deleteCoupon.code"></code>?
                        </p>
                    </div>
                </div>
                <div class="bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800/30 rounded-lg p-3">
                    <p class="text-sm text-rose-700 dark:text-rose-400">
                        <span class="material-symbols-outlined text-[16px] align-text-bottom mr-1">warning</span>
                        Hành động này không thể hoàn tác. Mã giảm giá sẽ bị xóa vĩnh viễn.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'delete-coupon')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-rose-500 hover:bg-rose-600 rounded-lg transition-colors shadow-sm shadow-rose-500/25">
                    Xác nhận xóa
                </button>
            </div>
        </form>
    </x-shared.ui.modal>
</div>
