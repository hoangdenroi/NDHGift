---
trigger: always_on
---

# 🛡️ Quy tắc Bảo mật Hệ thống (Zero-Trust Client-side Data Rules)

Quy tắc bảo mật này là **bắt buộc** và là **ưu tiên số 1** của dự án NDHGift. Toàn bộ các Agent phát triển hệ thống phải tuân thủ nghiêm ngặt để đảm bảo an toàn tuyệt đối cho tài khoản, ví tiền và tài nguyên của khách hàng.

---

## 1. Nguyên lý Zero-Trust Client-side Data (Không bao giờ tin tưởng Client)

- **Alpine.js & HTML chỉ làm nhiệm vụ UI/UX:**
    - Mọi biến số lưu trữ trong Alpine.js `x-data` hoặc các thẻ HTML **chỉ** được dùng để quản lý giao diện hiển thị (UI State), đóng mở modal, chuyển tab hoặc hiển thị tạm tính chi phí (Real-time Preview) để nâng cao trải nghiệm người dùng.
    - **Tuyệt đối không gửi** các thông số về giá tiền, chiết khấu, thông số phần cứng, hay quyền hạn từ phía Client lên Server khi thực hiện các yêu cầu giao dịch/thanh toán.
- **Bắt buộc Validate & Tự tính toán lại 100% ở Backend:**
    - Mọi yêu cầu thanh toán hay thay đổi cấu hình dịch vụ chỉ được truyền các mã ID thô (`vps_category_id`, `operating_system`, `location`, `ssh_key_id`, `quantity`, `coupon_code`) và token an toàn `@csrf`.
    - Server tự động truy vấn giá tiền gốc và phụ phí từ Database, tự đối soát mã giảm giá, tự nhân số lượng và thực hiện kiểm tra số dư ví tài khoản trước khi thanh toán, chặn hoàn toàn các hành vi thay đổi tham số (Parameter Tampering) qua DevTools.

---

## 2. Phòng chống lỗ hổng IDOR (Insecure Direct Object Reference)

- **Xác thực quyền sở hữu trực tiếp:**
    - Mọi thao tác thay đổi trạng thái (Reboot, Rebuild, Reset Password, Hủy dịch vụ, Update Firewall) phải luôn đi kèm với việc kiểm tra quyền sở hữu của chính User hiện tại đang đăng nhập đối với tài nguyên đó (qua `user_id` hoặc kiểm tra quyền trên database).
- **Xác thực tính toàn vẹn liên kết dữ liệu:**
    - Khi nhận các ID gửi lên từ form (ví dụ: `vps_category_id`, `operating_system`, `location`), Backend bắt buộc phải truy vấn CSDL để kiểm tra chéo liên kết (ví dụ: Hệ điều hành gửi lên có thực sự được hỗ trợ bởi gói cấu hình đó không qua pivot table; Vị trí datacenter gửi lên có thuộc gói hay không).

---

## 3. Làm sạch và Validate dữ liệu (Sanitize & Validate)

- Không bao giờ tin tưởng dữ liệu đầu vào. Sử dụng Laravel `FormRequest` làm lớp lá chắn đầu tiên để xác thực kiểu dữ liệu, giới hạn độ dài ký tự và kiểm tra định dạng dữ liệu gửi lên.
- Mọi dữ liệu dạng chuỗi do người dùng tự nhập (ví dụ: `hostname`, `reason`, `notes`...) phải được làm sạch và validate nghiêm ngặt (ví dụ: `hostname` bắt buộc tuân thủ chuẩn RFC 1123 bằng biểu thức chính quy Regex, loại bỏ ký tự lạ) để ngăn chặn các cuộc tấn công chèn mã độc (XSS, Command Injection).

---

## 4. Giới hạn Tần suất Yêu cầu (Rate Limiting)

- **Bảo vệ các endpoint nhạy cảm (Auth / Transactions):**
    - Bắt buộc áp dụng Middleware Rate Limit dùng riêng cho các tác vụ nhạy cảm, dễ bị tấn công Brute-force hoặc Spam (Đăng ký, Đăng nhập, Quên mật khẩu, Reset mật khẩu, Xác thực mật khẩu).
    - Sử dụng cấu hình RateLimiter `throttle:auth` (được định nghĩa tại AppServiceProvider): giới hạn tối đa **5 yêu cầu mỗi phút**, phân biệt định danh duy nhất theo sự kết hợp giữa **`email + IP`** (`$request->input('email').$request->ip()`), đảm bảo an toàn tối đa cho hệ thống trước các công cụ dò quét tự động.
