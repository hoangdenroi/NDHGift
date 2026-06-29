<x-admin-layout title="NDHGift - Quản lý thông báo">
    <div class="flex flex-col gap-6">

        {{-- 1. Thẻ thống kê (dữ liệu tĩnh mẫu) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Tổng thông báo</p>
                    <span class="material-symbols-outlined text-primary text-[20px]">notifications</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">1,248</h3>
                    <span class="text-slate-400 text-xs font-medium">tin</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Chưa đọc</p>
                    <span class="material-symbols-outlined text-amber-500 text-[20px]">mark_email_unread</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">86</h3>
                    <span class="text-slate-400 text-xs font-medium">tin</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Broadcast</p>
                    <span class="material-symbols-outlined text-purple-500 text-[20px]">campaign</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">12</h3>
                    <span class="text-slate-400 text-xs font-medium">tin</span>
                </div>
            </div>
            <div class="flex flex-col gap-2 rounded-lg p-5 border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark/50">
                <div class="flex justify-between items-start">
                    <p class="text-slate-400 text-sm font-medium">Hết hiệu lực</p>
                    <span class="material-symbols-outlined text-rose-500 text-[20px]">event_busy</span>
                </div>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-slate-900 dark:text-white text-2xl font-bold tracking-tight">34</h3>
                    <span class="text-slate-400 text-xs font-medium">tin</span>
                </div>
            </div>
        </div>

        {{-- 2. Thanh lọc & công cụ --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white/80 dark:bg-surface-dark border border-slate-200 dark:border-border-dark p-4 rounded-xl backdrop-blur-sm">
            <div class="flex flex-col sm:flex-row flex-wrap items-center gap-2 w-full xl:w-auto flex-1">
                {{-- Lọc theo phạm vi --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-40">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">tune</span>
                    <select
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors">
                        <option>Tất cả phạm vi</option>
                        <option>Cá nhân</option>
                        <option>Broadcast</option>
                        <option>Hệ thống</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Lọc theo loại --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-40">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">label</span>
                    <select
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors">
                        <option>Tất cả loại</option>
                        <option>Thành công</option>
                        <option>Thanh toán</option>
                        <option>Cảnh báo</option>
                        <option>Lỗi</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Lọc theo ưu tiên --}}
                <div class="relative flex-1 sm:flex-none w-full sm:w-40">
                    <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">flag</span>
                    <select
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-9 pr-8 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary cursor-pointer appearance-none transition-colors">
                        <option>Tất cả ưu tiên</option>
                        <option>Khẩn cấp</option>
                        <option>Cao</option>
                        <option>Trung bình</option>
                        <option>Thấp</option>
                    </select>
                    <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px] pointer-events-none">expand_more</span>
                </div>
                {{-- Ô tìm kiếm --}}
                <div class="relative flex-1 w-full sm:min-w-[180px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-[18px]">search</span>
                    <input type="text" placeholder="Tìm theo tiêu đề..."
                        class="bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark text-slate-700 dark:text-slate-300 text-sm rounded-lg pl-10 pr-4 py-2 w-full focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary transition-colors">
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button
                    class="flex items-center gap-2 px-3 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm shadow-primary/25 whitespace-nowrap cursor-default opacity-60"
                    title="Sắp ra mắt" disabled>
                    <span class="material-symbols-outlined text-[18px]">campaign</span>
                    Gửi thông báo
                </button>
                <button
                    class="flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-background-dark border border-slate-200 dark:border-border-dark hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-sm font-medium rounded-lg transition-colors whitespace-nowrap cursor-default opacity-60"
                    title="Sắp ra mắt" disabled>
                    <span class="material-symbols-outlined text-[18px]">done_all</span>
                    Đánh dấu tất cả đã đọc
                </button>
            </div>
        </div>

        {{-- 3. Bảng danh sách Thông báo (dữ liệu tĩnh mẫu) --}}
        <div class="rounded-lg border border-slate-200 dark:border-border-dark bg-white dark:bg-surface-dark overflow-hidden flex flex-col">
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-background-dark/50 border-b border-slate-200 dark:border-border-dark">
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider w-12">STT</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tiêu đề</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Phạm vi</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Loại</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Người nhận</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ưu tiên</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Trạng thái</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Ngày tạo</th>
                            <th class="p-4 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider text-right">Hành động</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-border-dark">
                        {{-- Dữ liệu mẫu 1 --}}
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors">
                            <td class="p-4 text-sm text-slate-500 text-center">1</td>
                            <td class="p-4">
                                <div class="max-w-[250px]">
                                    <p class="text-sm text-slate-900 dark:text-white font-medium truncate">Nạp tiền thành công</p>
                                    <p class="text-xs text-slate-400 truncate mt-0.5">Bạn đã nạp thành công 500.000đ vào ví.</p>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                    <span class="material-symbols-outlined text-[14px]">person</span> Cá nhân
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/10 text-emerald-500">
                                    <span class="material-symbols-outlined text-[14px]">check_circle</span> Thành công
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">NV</div>
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Nguyễn Văn</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                                    <span class="size-1.5 rounded-full bg-slate-400"></span> Thấp
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                    <span class="size-1.5 rounded-full bg-emerald-500"></span> Đã đọc
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-500">5 phút trước</td>
                            <td class="p-4 text-right">
                                <button class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-default opacity-60" disabled>
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                            </td>
                        </tr>
                        {{-- Dữ liệu mẫu 2 --}}
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors">
                            <td class="p-4 text-sm text-slate-500 text-center">2</td>
                            <td class="p-4">
                                <div class="max-w-[250px]">
                                    <p class="text-sm text-slate-900 dark:text-white font-medium truncate">🎉 Khuyến mãi mùa hè</p>
                                    <p class="text-xs text-slate-400 truncate mt-0.5">Giảm 30% cho tất cả template premium trong tháng 7.</p>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-purple-500/10 text-purple-500 border border-purple-500/20">
                                    <span class="material-symbols-outlined text-[14px]">campaign</span> Broadcast
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-500">
                                    <span class="material-symbols-outlined text-[14px]">info</span> Thông tin
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded-full bg-purple-500/10 flex items-center justify-center">
                                        <span class="material-symbols-outlined text-purple-500 text-[14px]">groups</span>
                                    </div>
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Tất cả</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-500">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span> Cao
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span> Chưa đọc
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-500">2 giờ trước</td>
                            <td class="p-4 text-right">
                                <button class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-default opacity-60" disabled>
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                            </td>
                        </tr>
                        {{-- Dữ liệu mẫu 3 --}}
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-background-dark/30 transition-colors">
                            <td class="p-4 text-sm text-slate-500 text-center">3</td>
                            <td class="p-4">
                                <div class="max-w-[250px]">
                                    <p class="text-sm text-slate-900 dark:text-white font-medium truncate">Cảnh báo bảo mật</p>
                                    <p class="text-xs text-slate-400 truncate mt-0.5">Phát hiện đăng nhập bất thường từ IP 103.x.x.x</p>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-cyan-500/10 text-cyan-500 border border-cyan-500/20">
                                    <span class="material-symbols-outlined text-[14px]">terminal</span> Hệ thống
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-rose-500/10 text-rose-500">
                                    <span class="material-symbols-outlined text-[14px]">error</span> Cảnh báo
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <div class="size-6 rounded-full bg-primary/10 flex items-center justify-center text-[10px] font-bold text-primary">TH</div>
                                    <span class="text-sm text-slate-600 dark:text-slate-400">Trần Hoàng</span>
                                </div>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-rose-500">
                                    <span class="size-1.5 rounded-full bg-rose-500 animate-pulse"></span> Khẩn cấp
                                </span>
                            </td>
                            <td class="p-4">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    <span class="size-1.5 rounded-full bg-amber-500"></span> Chưa đọc
                                </span>
                            </td>
                            <td class="p-4 text-sm text-slate-500">1 ngày trước</td>
                            <td class="p-4 text-right">
                                <button class="p-1.5 text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors cursor-default opacity-60" disabled>
                                    <span class="material-symbols-outlined text-[20px]">more_vert</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Pagination mẫu --}}
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 border-t border-slate-200 dark:border-border-dark bg-slate-50 dark:bg-background-dark/30 gap-4">
                <div class="text-sm text-slate-500 dark:text-slate-400">
                    Hiển thị <span class="font-bold text-slate-900 dark:text-white">1</span>
                    đến <span class="font-bold text-slate-900 dark:text-white">3</span>
                    trong <span class="font-bold text-slate-900 dark:text-white">3</span> thông báo
                </div>
                <div class="flex items-center gap-1">
                    <span class="text-xs text-slate-400 italic">Chức năng đang phát triển...</span>
                </div>
            </div>
        </div>

        {{-- Banner thông báo chức năng đang phát triển --}}
        <div class="bg-primary/5 border border-primary/20 rounded-xl p-4 flex items-start gap-3">
            <span class="material-symbols-outlined text-primary text-[24px] mt-0.5">construction</span>
            <div>
                <p class="text-sm font-bold text-slate-900 dark:text-white">Chức năng đang phát triển</p>
                <p class="text-sm text-slate-500 mt-1">
                    Trang quản lý thông báo hiện đang hiển thị dữ liệu mẫu. Các tính năng CRUD, gửi broadcast và đánh dấu đã đọc sẽ được triển khai trong phiên bản tiếp theo.
                </p>
            </div>
        </div>
    </div>
</x-admin-layout>
