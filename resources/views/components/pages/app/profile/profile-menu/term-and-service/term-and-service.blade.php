{{-- Component Điều khoản và dịch vụ --}}
<div class="bg-app-surface border border-app-border rounded-xl overflow-hidden shadow-sm">
    <div class="px-6 py-4 border-b border-app-border flex items-center gap-3">
        <button @click="setActiveAction('menu')" 
            class="flex items-center justify-center size-9 rounded-lg border border-app-border hover:bg-primary/5 hover:border-primary/30 transition-all text-app-muted hover:text-primary active:scale-95">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
        </button>
        <div>
            <h2 class="text-base font-bold text-app-text">Điều khoản sử dụng dịch vụ</h2>
            <p class="text-sm text-app-muted mt-0.5">Cập nhật lần cuối: 20/06/2026</p>
        </div>
    </div>
    
    <div class="p-6 space-y-5 text-sm text-app-muted leading-relaxed max-h-[500px] overflow-y-auto scrollbar-thin">
        <p class="text-app-text font-semibold">Chào mừng bạn đến với nền tảng tạo trang quà tặng NDHGift. Khi sử dụng dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản sau đây:</p>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">1. Quyền sở hữu tài khoản</h3>
            <p>Mỗi tài khoản được tạo trên NDHGift là quyền sở hữu cá nhân của người dùng. Bạn có trách nhiệm bảo mật mật khẩu tài khoản và chịu trách nhiệm cho tất cả các hoạt động xảy ra dưới tài khoản của mình.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">2. Sử dụng dịch vụ hợp pháp</h3>
            <p>Bạn cam kết chỉ sử dụng dịch vụ để tạo các nội dung trang quà tặng lành mạnh, hợp pháp, không vi phạm bản quyền thương hiệu, không chèn các mã độc hoặc liên kết lừa đảo. Mọi trang quà tặng có nội dung vi phạm sẽ bị khóa vĩnh viễn không cần báo trước.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">3. Giao dịch và số dư ví</h3>
            <p>Số dư ví tài khoản NDHGift được sử dụng để mua các template quà tặng premium trên nền tảng. Các giao dịch mua template sau khi hoàn tất thành công sẽ không được hoàn tiền trừ khi có lỗi kỹ thuật nghiêm trọng phát sinh từ phía hệ thống.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">4. Thay đổi điều khoản</h3>
            <p>NDHGift có quyền cập nhật, thay đổi hoặc bổ sung các điều khoản sử dụng này bất kỳ lúc nào để phù hợp với quy định pháp luật và nâng cao chất lượng dịch vụ. Các thay đổi sẽ có hiệu lực ngay khi được đăng tải lên trang web này.</p>
        </div>
        
        <div class="space-y-2">
            <h3 class="font-bold text-app-text">5. Giới hạn trách nhiệm</h3>
            <p>Chúng tôi không chịu trách nhiệm đối với bất kỳ thiệt hại trực tiếp hay gián tiếp nào phát sinh từ việc sử dụng hoặc không thể sử dụng dịch vụ do các nguyên nhân khách quan như lỗi đường truyền internet của bên thứ ba hoặc thiên tai.</p>
        </div>
    </div>
</div>
