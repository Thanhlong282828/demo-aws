
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Category, view namespace category::; controller ở
    Modules/Category/app/Http/Controllers/CategoryController.php. index() gọi
    view('category::index', ['categories' => ...]); key 'categories' tạo biến $categories trong file
    này.
--}}

{{--
    GIẢI THÍCH:
    Dùng layout resources/views/layouts/app.blade.php; phần content bên dưới được chèn tại
    yield("content") của layout.
--}}
@extends('layouts.app')

{{--
    GIẢI THÍCH:
    Đặt tiêu đề tab trình duyệt; layout đọc bằng yield("title").
--}}
@section('title', 'Danh mục — Mini Shop')

{{--
    GIẢI THÍCH:
    Bắt đầu nội dung trang; kết thúc bằng endsection. Đây là HTML được chèn vào layout.
--}}
@section('content')

    {{-- Thanh tiêu đề và nút tạo danh mục --}}
    <div class="toolbar">
        <h1>Danh mục</h1>
{{-- gọi return view('category::create'); --}}
{{--
    GIẢI THÍCH:
    route("categories.create", model nếu có) tạo URL để mở form tạo: GET /categories/create →
    create(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router
    tải model từ ID khi request đến.
--}}
        @if(auth()->user()?->role === 'ADMIN')<a class="button" href="{{ route('categories.create') }}">
            + Tạo danh mục
        </a>@endif
    </div>

    {{-- Bảng danh sách danh mục --}}
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Danh mục</th>
                    <th>Số sản phẩm</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                {{-- $categories là biến trong view (Blade) là 'categories' trong return, được controller truyền vào. --}}

{{--
    GIẢI THÍCH:
    $categories là danh sách view nhận từ controller. Vòng lặp tự tạo $category cho từng record hiện
    tại; ví dụ record id=2,name=Laptop thì $category->name đọc "Laptop" từ cột name. Biến số nhiều
    và số ít khác nhau.
--}}
                @forelse ($categories as $category)
                <tr>
                        {{-- Tên danh mục --}}
                        <td>
                         {{--  categories là ở route , show là function show() --}}
{{--
    GIẢI THÍCH:
    route("categories.show", model nếu có) tạo URL để mở chi tiết: GET /categories/ID → show(). Hàm
    route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router tải model từ
    ID khi request đến.
--}}
                            <a href="{{ route('categories.show', $category) }}">
{{--
    GIẢI THÍCH:
    Thuộc tính sau dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính,
    price=giá, stock=tồn kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ
    route.
--}}
                                {{ $category->name }}
        </a>
                        </td>

                        {{-- Số lượng sản phẩm --}}
                        {{-- là dó có withCount() nên có thể đặt Tên quan hệ + _count nếu không có withCount() thì đặt tên cũng vô ích --}}
                       {{-- withCount('products') → Laravel tạo products_count → Blade đọc products_count --}}
                        <td>
{{--
    GIẢI THÍCH:
    products_count không phải cột categories: CategoryController@index dùng
    Category::withCount("products") để đếm relationship products() và thêm thuộc tính products_count
    vào mỗi model.
--}}
                            {{ $category->products_count }}
                        </td>
{{-- Vì dòng này tạo thêm thuộc tính products_count cho mỗi danh mục:
              Category::withCount('products')
     Laravel đặt tên theo quy tắc:
               Tên quan hệ + _count
               products    + _count  → products_count
     Giả sử danh mục Laptop có 5 sản phẩm. Kết quả Laravel trả về có dạng:
               $category:
                   id             = 1
                   name           = "Laptop"
                   products_count = 5
     Vì $category đã có thuộc tính đó, Blade đọc được:
                    {{ $category->products_count }}
     Nếu bỏ withCount('products'), Laravel sẽ không tự tính và thêm products_count vào kết quả này.
 --}}

                        {{-- Các thao tác --}}
                        <td>
                            @if(auth()->user()?->role === 'ADMIN')<div class="actions">

                                {{-- Sửa danh mục --}}
                                {{--  categories là ở route , edit là function edit() --}}
{{--
    GIẢI THÍCH:
    route("categories.edit", model nếu có) tạo URL để mở form sửa: GET /categories/ID/edit → edit().
    Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router tải model
    từ ID khi request đến.
--}}
                                <a href="{{ route('categories.edit', $category) }}">
                                    Sửa
        </a>

                                {{-- Xóa danh mục --}}
                                <form
                                    method="POST"
{{--
    GIẢI THÍCH:
    route("categories.destroy", model nếu có) tạo URL để nhận yêu cầu xóa khi submit: DELETE
    /categories/ID → destroy(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào
    URL dùng ID; router tải model từ ID khi request đến.
--}}
                                    action="{{ route('categories.destroy', $category) }}"
{{--
    GIẢI THÍCH:
    confirm() hỏi trước khi gửi form: Cancel trả false và ngăn submit; OK tiếp tục gửi request xóa.
--}}
                                    onsubmit="return confirm('Xóa danh mục này?')"
                                >

{{--
    GIẢI THÍCH:
    Sinh input ẩn _token. Laravel kiểm tra token khi POST/PUT/DELETE; token không phải dữ liệu trong
    model.
--}}
                                    @csrf

{{--
    GIẢI THÍCH:
    Input ẩn _method=DELETE giúp router gọi destroy khi submit form xóa.
--}}
                                    @method('DELETE')

                                    <button class="danger">
                                        Xóa
                                    </button>
                                </form>
    </div>@endif
                        </td>
                </tr>

{{--
    GIẢI THÍCH:
    Nếu danh sách của forelse rỗng thì hiện hàng thông báo; không xem danh sách rỗng là lỗi DB.
--}}
                @empty
                <tr>
                        <td colspan="3">
                            Chưa có danh mục.
                        </td>
                </tr>

                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phân trang --}}

{{--
    GIẢI THÍCH:
    Chèn resources/views/components/pagination.blade.php; key paginator đổi tên biến truyền vào
    thành $paginator bên file đó.
--}}
{{--
    GIẢI THÍCH:
    Ghi chú bạn đã viết: $categories = 'categories'         return view('category::index',
    ['categories' => Category::withCount('products')->orderBy('name')->paginate(10)]);
--}}
    @include('components.pagination', [

        'paginator' => $categories
    ])
{{-- Controller
        ↓ truyền với tên 'categories'
     index.blade.php dùng $categories
        ↓ truyền với tên 'paginator'
     pagination.blade.php dùng $paginator
--}}

@endsection
