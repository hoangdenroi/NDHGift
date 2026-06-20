{{-- Component Chính sách bảo mật --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">Chính sách quyền riêng tư</h2>
            <p class="text-sm text-app-muted mt-0.5">Cập nhật lần cuối: 20/06/2026</p>
        </div>
    </div>
    
    <div class="p-6 space-y-5 text-sm text-app-muted leading-relaxed max-h-[500px] overflow-y-auto scrollbar-thin">
        <p class="text-app-text font-semibold">Quyền riêng tư của bạn là ưu tiên hàng đầu tại NDHGift. Chính sách này mô tả cách chúng tôi thu thập, sử dụng và bảo vệ thông tin cá nhân của bạn:</p>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">1. Thông tin chúng tôi thu thập</h3>
            <p>Chúng tôi chỉ thu thập các thông tin cần thiết để vận hành tài khoản của bạn bao gồm: Họ tên, địa chỉ email, số điện thoại, ảnh đại diện và địa chỉ IP đăng nhập để phục vụ công tác xác thực bảo mật.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">2. Cách thức sử dụng thông tin</h3>
            <p>Thông tin của bạn được sử dụng để cá nhân hóa trang quản trị, xử lý giao dịch mua template, gửi các thông báo quan trọng về tài khoản và hỗ trợ kỹ thuật khi bạn yêu cầu hỗ trợ.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">3. Bảo vệ và chia sẻ thông tin</h3>
            <p>NDHGift áp dụng các tiêu chuẩn mã hóa bảo mật hiện đại nhất để ngăn chặn truy cập trái phép đối với dữ liệu của bạn. Chúng tôi cam kết tuyệt đối không bán, chia sẻ hoặc cho thuê thông tin cá nhân của bạn cho bất kỳ bên thứ ba nào vì mục đích thương mại.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">4. Quyền kiểm soát dữ liệu của người dùng</h3>
            <p>Bạn có toàn quyền thay đổi thông tin cá nhân (như họ tên, ảnh đại diện, số điện thoại) bất kỳ lúc nào trực tiếp trên trang Hồ sơ cá nhân của mình hoặc yêu cầu chúng tôi xóa vĩnh viễn dữ liệu tài khoản nếu không còn nhu cầu sử dụng.</p>
        </div>
    </div>
</div>
