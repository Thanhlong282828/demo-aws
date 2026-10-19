# N+1 và pagination

Experiment nằm trong test `test_eager_loading_reduces_category_queries`: tạo 5 products với factory, bật query log, duyệt Product::get() và đọc category của từng product → 6 queries. Đổi thành Product::with('category')->get() → 2 queries. Test assert chính xác 6 và 2, đã PASS trên MySQL.

API ProductController@index eager load category trước paginate, tránh Resource tự kéo từng category. Không dùng get() toàn bảng cho list API. Products/categories max per_page100; orders 15/trang. Đây là experiment nhỏ, không phải benchmark tải hay bằng chứng app chịu tải lớn.

```bash
php artisan test --compact --filter=test_eager_loading_reduces_category_queries
```
