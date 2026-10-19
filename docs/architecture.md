# Module + MVC và database

Route chọn controller; middleware kiểm auth/role trước khi vào controller. FormRequest validate dữ liệu, model truy cập MySQL, Blade dựng HTML. API Resource dựng JSON. Service chứa business logic cần transaction, không dựng HTML.

- Product thuộc Category; Category có nhiều Product.
- User có CartItems và Orders (query theo user_id).
- Order có nhiều OrderItems; personal_access_tokens thuộc User bằng polymorphic relation Sanctum.
- Coupon giữ luật hiện tại; Order giữ snapshot mã/percent/subtotal/discount/total.
- Product.price là decimal VND. Order.total và OrderItem.unit_price là integer theo 1/100 VND: 350000.00 VND tương ứng 35000000. API order trả money_unit rõ ràng.
- OrderItem product_name/unit_price là snapshot, không thay đổi khi catalog đổi.
- cart_items/order_items product_id không FK tới products, có chủ đích để phát hiện product bị xóa khỏi cart và giữ lịch sử đơn.

CheckoutService khóa User, cart, products theo thứ tự ổn định trong MySQL transaction, kiểm stock lần nữa, tạo order/items, decrement stock, tăng coupon usage, clear cart. Exception rollback toàn bộ. Unique checkout_token + lock User + replay trả cùng Order ngăn double checkout trong cùng user.

Status: pending → confirmed/cancelled; confirmed → completed/cancelled; completed/cancelled là terminal. Hủy hoàn stock một lần; không hoàn coupon usage theo thiết kế hiện tại.

[Models và relationships](../Modules/Shop/app/Models/Order.php) · [Checkout service](../Modules/Shop/app/Services/CheckoutService.php) · [Migration business](../Modules/Shop/database/migrations/2026_10_16_000001_create_business_tables.php)
