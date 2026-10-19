# Test evidence

Ngày19/10/2026: PHPUnit trên MySQL local riêng: 82 tests PASS, 338 assertions. Feature Week5ApiTest có21 tests; các suite Week4/MiniShop giữ lại để bảo vệ business flows. Không claim coverage100%.

Regression thật: bỏ GET index route Products → test_guest_product_list_is_json_and_paginated FAIL405; restore → PASS. Transaction rollback test ném exception ở item thứ2, assert order/items0, cart2, stock không đổi, coupon used_count0.

Production: 35 HTTP checks PASS ban đầu trên HTTP, sau đó35 checks PASS qua HTTPS với TLS verification. Bao gồm product/category CRUD, login/token revoke, 401/403/404/422, session/CSRF, add/update cart, checkout tạo đơn/items/trừ stock, admin status/terminal rejection; private paths blocked. Tự gia hạn certificate dry-run và Nginx deploy hook PASS.

Fresh clone test sẽ được bổ sung kết quả sau khi thực thi. Các kết quả trên là tool output đã chạy; không phải người học đã làm bài exam không AI.
