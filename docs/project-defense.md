# D35 — tự thi không AI

Chưa thực hiện. Không có đáp án sẵn để tránh biến bài thi thành đọc thuộc.

## Live Change —45 phút, không AI

Admin cần GET /api/admin/dashboard trả total users/products/orders/revenue, orders by status. Admin only, JSON, status đúng, automated test. Tự làm trên branch riêng; commit `week 5 release exam - unaided change`. Định nghĩa revenue (chỉ completed hay tất cả?) phải được nêu rõ trước query. AI chưa triển khai endpoint này.

## Project Defense

1. Project giải quyết gì, ai dùng, flow chính?
2. Vì sao module + MVC, vì sao Laravel?
3. Main tables, relationship, tại sao tách OrderItem?
4. Vì sao snapshot unit_price và tiền integer1/100 VND?
5. Transaction/row lock/idempotency ở đâu, fail giữa checkout ra sao?
6. Validation và authorization khác gì?
7. Web session vs API bearer token?
8. 401/403, token expiry/revoke và ownership?
9. API noun/path/status/resources được thiết kế ra sao?
10. Test suite kiểm gì, rollback test tạo lỗi thế nào?
11. Regression experiment bắt được gì, giới hạn của tests?
12. N+1 experiment, eager loading và pagination?
13. Secrets được giữ ở đâu, public root là gì?
14. Nginx/PHP-FPM/MySQL phối hợp thế nào?
15. HTTPS certificate IP gia hạn thế nào, IP đổi thì sao?
16. Production500 xem log theo thứ tự nào?
17. Nếu version mới lỗi, rollback source/config/DB thế nào?
18. Postman env và token giúp người khác dùng API ra sao?
19. Điểm yếu nào chưa làm: reset demo, monitoring/backup, CI, load test?
20. Tự sửa requirement trong45 phút theo chain route→auth→query→JSON→test được không?

Sau phỏng vấn ghi tối đa5 knowledge gaps, evidence sai, bước sửa và ngày test lại. Không mở rộng feature để né interview.
