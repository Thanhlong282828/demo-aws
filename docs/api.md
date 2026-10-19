# Mini Shop API

Base URL: `https://54.252.208.88/api` hoặc local `http://127.0.0.1:8005/api`. Gửi Accept: application/json; khi có body gửi Content-Type: application/json. Không version prefix ở v1 hiện tại để theo đúng đề; thay đổi contract sau này cần version/migration plan.

## Endpoint matrix

| Method | Path sau /api | Permission | Input | Status |
| --- | --- | --- | --- | --- |
| POST | /login | Guest | email, password | 200 / 401 / 422 / 429 |
| POST | /logout | Token | — | 200 / 401 |
| GET | /products | Public | page, per_page 1–100 | 200 / 422 |
| GET | /products/{id} | Public | id | 200 / 404 |
| POST | /products | Admin | xem schema | 201 / 401 / 403 / 422 |
| PUT/PATCH | /products/{id} | Admin | full schema | 200 / 401 / 403 / 404 / 422 |
| DELETE | /products/{id} | Admin | id | 200 / 401 / 403 / 404 / 422 |
| GET | /categories | Public | page, per_page 1–100 | 200 / 422 |
| GET | /categories/{id} | Public | id | 200 / 404 |
| POST | /categories | Admin | xem schema | 201 / 401 / 403 / 422 |
| PUT/PATCH | /categories/{id} | Admin | full schema | 200 / 401 / 403 / 404 / 422 |
| DELETE | /categories/{id} | Admin | id | 200 / 401 / 403 / 404 / 422 |
| GET | /orders | Token | page | 200 / 401 |
| GET | /orders/{id} | Owner | id | 200 / 401 / 403 / 404 |
| GET | /admin/orders | Admin | page | 200 / 401 / 403 |
| PUT | /admin/orders/{id}/status | Admin | status | 200 / 401 / 403 / 404 / 422 |

## Authentication

POST /login body `{ "email": "user@minishop.test", "password": "DemoShop2026!" }`.
Response `{ "data": { "token": "<only-shown-on-login>", "token_type": "Bearer", "expires_in": 28800 }, "message": "Authenticated." }`.
Token được hash trong DB; hết hạn sau 8 giờ. Gửi Authorization: Bearer <token>. POST /logout xóa token hiện tại; token cũ → 401. Không đưa token thật vào file collection hay GitHub. Token không thay role/ownership check.

## Schemas

Product create/update: name required string max255; category_id required integer exists; price >0 decimal tối đa 2 chữ số thập phân, max9999999999.99; stock integer 0..2147483647; description nullable string max5000. PUT và PATCH ở bản này cùng yêu cầu đầy đủ các trường required; chưa hỗ trợ sparse PATCH.

```json
{"name":"Keyboard","category_id":1,"price":350000,"stock":10,"description":"Demo"}
```

Category create/update: `{ "name": "Accessories" }`, required unique max255; cập nhật tên giữ nguyên được phép. Xóa category có products → 422, không cascade xóa catalog.

Order status: `{ "status": "confirmed" }`. Luật chuyển trạng thái xem architecture. User orders chỉ own orders; Admin muốn xem mọi order dùng endpoint admin. Tạo order qua web cart/checkout, không POST /api/orders.

## Responses

Detail: `{ "data": { "id": 1, "name": "Keyboard", "price": "350000.00", "stock": 10, "category_id": 1, "category": { "id": 1, "name": "Accessory" }, "description": "Demo" } }`.
List: data array + links + meta theo Laravel paginator, mặc định 15 records; categories/products cho per_page tối đa100. Orders cố định15. Không trả toàn bộ bảng vô hạn. Order list không nhúng items; detail/status load items. Tiền trong Order là 1/100 VND integer, xem money_unit; Product.price là chuỗi decimal VND.

Create: 201 + data + message. Update: 200 + data + message. Delete: 200 + `{ "data": null, "message": "Product deleted." }`.

Validation: 422 + `{ "message": "Validation failed.", "errors": { "name": ["..."] } }`.
Các lỗi auth/HTTP dùng message + errors rỗng. Production 500 che nội dung exception, không lộ trace; debug local có thể chi tiết hơn.

## Postman walkthrough

Import Collection và Environment trong [postman](postman). Login → token tự lưu; GET Products → detail; Login Admin trước các write request. Collection tự lưu product_id/category_id sau create. ID order cần chọn từ GET Orders đúng tài khoản rồi đặt order_id. Example responses trong collection là minh họa, không phải dữ liệu production cố định. Nên thử write local, không dùng số ID ngẫu nhiên trên demo công khai.
