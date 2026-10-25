# Permission matrix

| Endpoint                                  | Guest | USER     | ADMIN                        |
| ----------------------------------------- | ----- | -------- | ---------------------------- |
| GET Products/Categories và detail         | 200   | 200      | 200                          |
| POST/PUT/PATCH/DELETE Products/Categories | 401   | 403      | Thành công nếu input hợp lệ  |
| GET Own Orders                            | 401   | Own only | Own only                     |
| GET Other User Order                      | 401   | 403      | 403 trên user endpoint       |
| GET Admin Orders / PUT Status             | 401   | 403      | Được phép theo business rule |

Authentication: Sanctum token hoặc web session. Authorization: Admin middleware và so sánh order.user_id với request.user.id. Ownership không dựa vào user_id gửi từ client. Link bị ẩn không phải security boundary.

Evidence: [API routes](../Modules/Shop/routes/api.php), [Admin middleware](../Modules/Shop/app/Http/Middleware/Admin.php), [Order API controller](../Modules/Shop/app/Http/Controllers/Api/OrderController.php), [Tests](../tests/Feature/Week5ApiTest.php).
