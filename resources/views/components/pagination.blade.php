
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    $paginator do view cha truyền: include("components.pagination", ["paginator" =>
    $products/$categories/$customers]). Cùng dữ liệu nhưng tên biến trong file này là $paginator;
    không query DB tại đây.
--}}

{{--
    GIẢI THÍCH:
    hasPages() chỉ hiện thanh phân trang khi có hơn một trang.
--}}
@if ($paginator->hasPages())
{{-- $paginator là của  @include('components.pagination', ['paginator' => $categories])  ở Module > Category > resources > views > index--}}
    <nav class="pagination" aria-label="Phân trang">

        {{-- Nút trang trước --}}

{{--
    GIẢI THÍCH:
    onFirstPage() hỏi đang trang đầu không; trang đầu hiện span không bấm, trang khác hiện link
    previousPageUrl().
--}}
        @if ($paginator->onFirstPage())
            <span>← Trước</span>

        @else
            <a href="{{ $paginator->previousPageUrl() }}">
                ← Trước
            </a>

        @endif

        {{-- Danh sách số trang --}}
        {{-- Đây là vòng lặp để in các nút số trang. Giả sử bạn đang ở trang 5, tổng có 10 trang. --}}

{{--
    GIẢI THÍCH:
    Ghi chú bạn đã viết trong vòng for: Dòng 1: bắt đầu từ số nào? | $page = max(1, 5 - 2); | 5 - 2
    = 3. max(1, 3) chọn số lớn hơn là 3. → Tạo biến $page, ban đầu bằng 3. | Dòng 2: lặp đến số nào?
    | $page <= min(10, 5 + 2); | 5 + 2 = 7. min(10, 7) chọn số nhỏ hơn là 7. → Tiếp tục lặp khi
    $page còn ≤ 7. | Dòng 3: mỗi lần tăng bao nhiêu? | → Tăng $page thêm 1 sau mỗi lần lặp.
--}}

{{--
    GIẢI THÍCH:
    $page được khai báo ngay trong for, dùng để in nút trang. currentPage() đọc page hiện tại từ kết
    quả paginate; không có ?page thì mặc định 1. -2/+2 là số nút lân cận, không phải OFFSET của
    query.
--}}
{{--
    GIẢI THÍCH:
    lastPage() là số trang cuối, tính từ tổng record/số record mỗi trang; 60 record với paginate(10)
    có 6 trang.
--}}
        @for (
            $page = max(1, $paginator->currentPage() - 2);

            $page <= min($paginator->lastPage(), $paginator->currentPage() + 2);
            $page++
        )
        {{-- Vậy $page lần lượt là: 3 → 4 → 5 → 6 → 7 --}}

{{--
    GIẢI THÍCH:
    Nếu số nút $page trùng trang hiện tại thì hiện span tô màu, không link; các số khác tạo link để
    bấm.
--}}
            @if ($page === $paginator->currentPage())
                <span class="current" aria-current="page">
                    {{ $page }}
                </span>

        @else
            {{-- nút trang 2 được tạo ở đoạn này: --}}
            {{-- Khi vòng @for chạy đến $page = 2, nó tạo HTML: --}}
            {{-- Bạn bấm 2 → browser gửi /products?page=2 → controller chạy: $query->paginate(10); --}}
            {{-- Laravel đọc page=2, tự tính: Bỏ qua (2 − 1) × 10 = 10 sản phẩm  . Lấy 10 sản phẩm tiếp theo: thứ 11 → 20 --}}
{{--
    GIẢI THÍCH:
    Ví dụ $page=2: url(2) tạo ?page=2; bấm link gửi trang=2 về controller. paginate(10) tự tính
    OFFSET=(2-1)*10=10, lấy tối đa 10 record tiếp theo; Blade chỉ tạo link.
--}}
                <a href="{{ $paginator->url($page) }}">
                    {{ $page }}
            </a>

        @endif

        @endfor

        {{-- Nút trang sau --}}

{{--
    GIẢI THÍCH:
    hasMorePages() hỏi còn trang sau không; nextPageUrl() tạo link trang tiếp, trang cuối hiện span
    không bấm.
--}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}">
                Sau →
            </a>

        @else
            <span>Sau →</span>

        @endif

        {{-- Thông tin trang hiện tại --}}
        <span>
{{--
    GIẢI THÍCH:
    lastPage() là số trang cuối, tính từ tổng record/số record mỗi trang; 60 record với paginate(10)
    có 6 trang.
--}}
            Trang {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                </span>
    </nav>

        @endif
