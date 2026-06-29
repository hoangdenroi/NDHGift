{{-- ==========================================
MODAL TẠO NGƯỜI DÙNG MỚI
========================================== --}}
<x-shared.ui.modal name="create-user" maxWidth="lg">
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Thêm người dùng mới</h3>
                <button type="button" x-on:click="$dispatch('close-modal', 'create-user')"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <div class="flex flex-col gap-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Tên đăng nhập
                            <span class="text-rose-500">*</span></label>
                        <input type="text" name="username" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="vd: nguyenvana">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Họ và tên <span
                                class="text-rose-500">*</span></label>
                        <input type="text" name="fullname" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Nguyễn Văn A">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Email <span
                                class="text-rose-500">*</span></label>
                        <input type="email" name="email" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="email@example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Số điện
                            thoại</label>
                        <input type="text" name="phone"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="0901234567">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Mật khẩu <span
                                class="text-rose-500">*</span></label>
                        <input type="password" name="password" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Tối thiểu 8 ký tự">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Xác nhận mật
                            khẩu <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="Nhập lại mật khẩu">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vai trò</label>
                        <select name="role"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="user">Người dùng</option>
                            <option value="admin">Quản trị viên</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Trạng
                            thái</label>
                        <select name="status"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="active">Hoạt động</option>
                            <option value="suspended">Bị khóa</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Số dư
                            (VND)</label>
                        <input type="number" name="balance" value="0" min="0" step="1000"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                </div>
            </div>
        </div>

        <div
            class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
            <button type="button" x-on:click="$dispatch('close-modal', 'create-user')"
                class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                Hủy bỏ
            </button>
            <button type="submit"
                class="px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                Tạo người dùng
            </button>
        </div>
    </form>
</x-shared.ui.modal>

{{-- ==========================================
MODAL CHỈNH SỬA NGƯỜI DÙNG
========================================== --}}
<div x-data="{
        editUser: { id: null, username: '', fullname: '', email: '', phone: '', status: 'active', role: 'user', balance: 0, avatar_url: '' },
    }" @open-edit-user.window="editUser = $event.detail; $dispatch('open-modal', 'edit-user')">

    <x-shared.ui.modal name="edit-user" maxWidth="lg">
        <form :action="'{{ route('admin.users.index') }}/' + editUser.id" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">edit</span>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cập nhật người dùng</h3>
                    </div>
                    <button type="button" x-on:click="$dispatch('close-modal', 'edit-user')"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                {{-- Hidden fields để submit các giá trị disabled bắt buộc lên backend --}}
                <input type="hidden" name="username" :value="editUser.username">
                <input type="hidden" name="fullname" :value="editUser.fullname">
                <input type="hidden" name="email" :value="editUser.email">
                <input type="hidden" name="phone" :value="editUser.phone">

                <div class="flex flex-col gap-4">
                    {{-- Dòng 1: Họ tên | Email --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 dark:text-slate-500 mb-1">Họ
                                tên</label>
                            <input type="text" :value="editUser.fullname" disabled
                                class="w-full bg-slate-100 dark:bg-slate-800/30 border border-slate-200 dark:border-border-dark/50 rounded-lg px-3 py-2 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-slate-400 dark:text-slate-500 mb-1">Email</label>
                            <input type="email" :value="editUser.email" disabled
                                class="w-full bg-slate-100 dark:bg-slate-800/30 border border-slate-200 dark:border-border-dark/50 rounded-lg px-3 py-2 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                        </div>
                    </div>

                    {{-- Dòng 2: Số điện thoại --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-400 dark:text-slate-500 mb-1">Số điện
                            thoại</label>
                        <input type="text" :value="editUser.phone || 'Chưa cập nhật'" disabled
                            class="w-full bg-slate-100 dark:bg-slate-800/30 border border-slate-200 dark:border-border-dark/50 rounded-lg px-3 py-2 text-sm text-slate-500 dark:text-slate-400 cursor-not-allowed">
                    </div>

                    {{-- Tiêu đề phụ: QUẢN LÝ TÀI KHOẢN --}}
                    <div class="flex items-center gap-2 border-t border-slate-100 dark:border-border-dark/50 pt-4 mt-2">
                        <span
                            class="material-symbols-outlined text-slate-500 dark:text-slate-400 text-[18px]">manage_accounts</span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Quản
                            lý tài khoản</span>
                    </div>

                    {{-- Dòng 3: Trạng thái | Số dư (VND) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Trạng
                                thái</label>
                            <div class="relative">
                                <select name="status" x-model="editUser.status"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="active">Hoạt động</option>
                                    <option value="suspended">Bị khóa</option>
                                </select>
                                <span
                                    class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Số dư
                                (VND)</label>
                            <input type="number" name="balance" x-model="editUser.balance" min="0" step="1000"
                                class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>

                    {{-- Dòng 4: Vai trò --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Vai
                                trò</label>
                            <div class="relative">
                                <select name="role" x-model="editUser.role"
                                    class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg pl-3 pr-8 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary appearance-none cursor-pointer">
                                    <option value="user">Người dùng</option>
                                    <option value="admin">Quản trị viên</option>
                                </select>
                                <span
                                    class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[18px]">expand_more</span>
                            </div>
                        </div>
                        <div></div>
                    </div>

                    {{-- Dòng 5: Avatar URL --}}
                    {{-- <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Avatar
                            URL</label>
                        <input type="text" name="avatar_url" x-model="editUser.avatar_url"
                            class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary"
                            placeholder="https://example.com/avatar.png">
                    </div> --}}
                </div>
            </div>

            <div
                class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'edit-user')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-primary hover:bg-primary/90 rounded-lg transition-colors shadow-sm shadow-primary/25">
                    Cập nhật
                </button>
            </div>
        </form>
    </x-shared.ui.modal>
</div>

{{-- ==========================================
MODAL KHÓA/MỞ KHÓA TÀI KHOẢN
========================================== --}}
<div x-data="{
        toggleUser: { id: null, fullname: '', status: 'active' },
    }" @open-toggle-status.window="toggleUser = $event.detail; $dispatch('open-modal', 'toggle-status')">

    <x-shared.ui.modal name="toggle-status" maxWidth="md">
        <form :action="'{{ route('admin.users.index') }}/' + toggleUser.id + '/toggle-status'" method="POST">
            @csrf
            @method('PATCH')
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-full flex items-center justify-center"
                        :class="toggleUser.status === 'active' ? 'bg-rose-100 dark:bg-rose-900/30' : 'bg-emerald-100 dark:bg-emerald-900/30'">
                        <span class="material-symbols-outlined text-[20px]"
                            :class="toggleUser.status === 'active' ? 'text-rose-500' : 'text-emerald-500'"
                            x-text="toggleUser.status === 'active' ? 'lock' : 'lock_open'"></span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white"
                            x-text="toggleUser.status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản'"></h3>
                        <p class="text-sm text-slate-500">
                            <span
                                x-text="toggleUser.status === 'active' ? 'Khóa tài khoản của' : 'Mở khóa tài khoản cho'"></span>
                            <span class="font-bold text-slate-900 dark:text-white" x-text="toggleUser.fullname"></span>?
                        </p>
                    </div>
                </div>

                {{-- Nhập lý do khóa (chỉ hiện khi đang active → khóa) --}}
                <div x-show="toggleUser.status === 'active'" class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Lý do khóa</label>
                    <textarea name="banned_reason" rows="3"
                        class="w-full bg-slate-50 dark:bg-background-dark border border-slate-200 dark:border-border-dark rounded-lg px-3 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary resize-none"
                        placeholder="Nhập lý do khóa tài khoản..."></textarea>
                </div>
            </div>

            <div
                class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'toggle-status')"
                    class="px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 bg-white dark:bg-surface-dark border border-slate-200 dark:border-border-dark rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                    Hủy bỏ
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors shadow-sm"
                    :class="toggleUser.status === 'active' ? 'bg-rose-500 hover:bg-rose-600 shadow-rose-500/25' : 'bg-emerald-500 hover:bg-emerald-600 shadow-emerald-500/25'"
                    x-text="toggleUser.status === 'active' ? 'Xác nhận khóa' : 'Xác nhận mở khóa'">
                </button>
            </div>
        </form>
    </x-shared.ui.modal>
</div>

{{-- ==========================================
MODAL XÓA NGƯỜI DÙNG
========================================== --}}
<div x-data="{
        deleteUser: { id: null, fullname: '' },
    }" @open-delete-user.window="deleteUser = $event.detail; $dispatch('open-modal', 'delete-user')">

    <x-shared.ui.modal name="delete-user" maxWidth="md">
        <form :action="'{{ route('admin.users.index') }}/' + deleteUser.id" method="POST">
            @csrf
            @method('DELETE')
            <div class="p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="size-10 rounded-full bg-rose-100 dark:bg-rose-900/30 flex items-center justify-center">
                        <span class="material-symbols-outlined text-rose-500 text-[20px]">delete_forever</span>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Xóa người dùng</h3>
                        <p class="text-sm text-slate-500">
                            Bạn có chắc muốn xóa <span class="font-bold text-slate-900 dark:text-white"
                                x-text="deleteUser.fullname"></span>?
                        </p>
                    </div>
                </div>
                <div
                    class="bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30 rounded-lg p-3">
                    <p class="text-sm text-amber-700 dark:text-amber-400">
                        <span class="material-symbols-outlined text-[16px] align-text-bottom mr-1">warning</span>
                        Hành động này sẽ xóa mềm tài khoản. Dữ liệu vẫn được giữ lại trong hệ thống.
                    </p>
                </div>
            </div>

            <div
                class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-background-dark/50 border-t border-slate-200 dark:border-border-dark">
                <button type="button" x-on:click="$dispatch('close-modal', 'delete-user')"
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