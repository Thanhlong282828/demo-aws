
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Category, view namespace category::; controller ở
    Modules/Category/app/Http/Controllers/CategoryController.php. Đây là partial: create/edit chèn
    bằng include nên nó nhận các biến của view cha. $category có khi sửa, chưa có khi tạo; old() ưu
    tiên dữ liệu vừa nhập sai rồi mới đến model/default.
--}}

{{--
    @csrf:
    → Laravel tạo một input ẩn chứa CSRF Token.
    → Giúp bảo vệ Form khỏi tấn công CSRF.
    → Dùng cho Form gửi bằng phương thức POST.
--}}

{{--
    GIẢI THÍCH:
    Sinh input ẩn _token. Laravel kiểm tra token khi POST/PUT/DELETE; token không phải dữ liệu trong
    model.
--}}
@csrf

{{--
    <label>:
    → Nhãn của ô nhập tên danh mục.

    <input>:
    → Ô nhập dữ liệu từ người dùng.

    name="name":
    → Tên field gửi lên server.
    → Controller / FormRequest nhận được bằng:
      $request->input('name')

    maxlength="255":
    → Giới hạn tối đa 255 ký tự trên trình duyệt.

    required:
    → Bắt buộc nhập dữ liệu trên trình duyệt.
--}}
<label>
    Tên danh mục

    {{--
        value="{{ old('name', $category->name ?? '') }}":

        old('name'):
        → Lấy dữ liệu người dùng đã nhập trước đó.
        → Hữu ích khi Validation thất bại.

        $category:
        → Được truyền từ CategoryController::edit()
          khi sửa danh mục.

        $category->name:
        → Tên danh mục hiện tại trong database.

        ?? '':
        → Nếu không có giá trị thì dùng chuỗi rỗng.

        Ưu tiên:
        1. Giá trị người dùng nhập trước đó.
        2. Tên danh mục hiện tại khi sửa.
        3. Chuỗi rỗng khi tạo mới.
    --}}
    <input
        type="text"
        name="name"
        maxlength="255"
        required
{{--
    GIẢI THÍCH:
    name của input là tên field gửi server; value là nội dung hiện trong ô. old("field", dự phòng)
    lấy input cũ khi validation fail, nếu chưa có thì lấy model hoặc filter/default. Thuộc tính sau
    dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính, price=giá, stock=tồn
    kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ route.
--}}
        value="{{ old('name', $category->name ?? '') }}"
    >

    {{--
        @error('name'):
        → Kiểm tra Validation có lỗi ở field name không.

        $message:
        → Thông báo lỗi tương ứng với field name.
        → Được Laravel cung cấp trong @error.

        Ví dụ:
        "Vui lòng nhập tên danh mục."
        "Tên danh mục đã tồn tại."
    --}}

{{--
    GIẢI THÍCH:
    $message được Laravel tạo trong khối error cho field tương ứng, từ validation của FormRequest;
    không phải biến controller tự truyền.
--}}
    @error('name')
        <small class="error">
            {{ $message }}
        </small>

    @enderror

</label>

{{--
    <button>:
    → Nút gửi Form.
    → Khi bấm, trình duyệt gửi dữ liệu đến
      URL được khai báo trong <form action="...">.
--}}
<button type="submit">
    Lưu danh mục
</button>

{{--
    route('categories.index'):
    → Tạo URL đến trang danh sách danh mục.

    Ví dụ:
    /categories
--}}
{{--
    GIẢI THÍCH:
    route("categories.index", model nếu có) tạo URL để mở danh sách: GET /categories → index(). Hàm
    route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router tải model từ
    ID khi request đến.
--}}
<a href="{{ route('categories.index') }}">
    Quay lại
</a>
