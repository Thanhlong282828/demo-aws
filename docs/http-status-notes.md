# HTTP status — tự giải thích

| Code | Ý nghĩa | Ví dụ Mini Shop |
| --- | --- | --- |
| 200 | Request thành công, response có body | GET Product hoặc update/delete có JSON |
| 201 | Tạo tài nguyên mới | POST Product hợp lệ bằng Admin |
| 400 | Request không hợp lệ ở mức cấu trúc/protocol | HTTP malformed; không dùng thay validation nghiệp vụ |
| 401 | Chưa xác thực hoặc credential/token sai/hết hạn | Guest gọi orders, login sai |
| 403 | Đã xác thực nhưng không được phép | USER gọi Admin API hoặc xem đơn khác |
| 404 | Không có resource/route | Product không tồn tại |
| 422 | Dữ liệu không đạt validation/business rule | Giá âm, danh mục có sản phẩm, status chuyển sai |
| 500 | Lỗi ngoài dự kiến phía server | Xem Laravel/Nginx logs; không public stack trace |
| 429 | Gửi quá nhiều request | Login vượt 10 request/phút |

404 không được trả 200 với chuỗi “not found”. DELETE ở project này trả 200 vì có JSON message; nếu dùng 204 thì body phải rỗng.
