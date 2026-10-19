# Week5 release checklist — bằng chứng thật

| Area | Check | Result |
| --- | --- | --- |
| API | Product/Category CRUD, own/admin Orders | PASS, feature tests |
| Auth | Login, role, ownership, token expiry/revoke | PASS, feature tests |
| Business | Checkout order/items/stock, rollback | PASS, MySQL tests |
| Production | HTTP→HTTPS; TLS verify0 | PASS |
| Production | Guest/User/Admin/API smoke | PASS35 checks HTTPS; register/foreign owner bổ sung sau |
| Renewal | systemd timer + dry-run + Nginx reload | PASS |
| Tests | MySQL suite | PASS82 tests/338 assertions |
| Docs | D29–D35, API, Postman, README | PASS: Markdown links, JSON parse, 17 requests Postman |
| Fresh setup | README trong folder sạch | PASS: clone sạch, install, migrate/seed, 82 tests PASS |
| Source audit | Tracked secrets/runtime | PASS: .env/khóa/log ignored; tracked secret-pattern audit PASS |
| GitHub | Repo, push, v1.0.0 release | Repo đã tạo; push/tag/release đang thực hiện |
| Learner | Knowledge checks | NOT ASSESSED |
| Learner | Live Change45 phút không AI | USER TODO |
| Learner | Project Defense | USER TODO |
| Career | CV cá nhân/16 applications/funnel | USER TODO; draft project bullets available |

v1.0.0 là release kỹ thuật project, không phải chứng nhận người học đã pass mọi mục Week5. Không backdate commits hoặc giả mạo lịch sử học theo ngày.
