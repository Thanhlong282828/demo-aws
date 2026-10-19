
{{--
    GIẢI THÍCH:
    Comment Blade dạng này chỉ dành cho người đọc source; không xuất hiện trong HTML gửi về browser.
    File thuộc module Product, view namespace product::; controller ở
    Modules/Product/app/Http/Controllers/ProductController.php. Đây là partial: create/edit chèn
    bằng include nên nó nhận các biến của view cha. $product có khi sửa, chưa có khi tạo; old() ưu
    tiên dữ liệu vừa nhập sai rồi mới đến model/default. $categories do
    ProductController::create()/edit() lấy bằng Category::orderBy("name")->get() và truyền vào form;
    không phải danh sách phân trang CategoryController.
--}}

{{--
    GIẢI THÍCH:
    Sinh input ẩn _token. Laravel kiểm tra token khi POST/PUT/DELETE; token không phải dữ liệu trong
    model.
--}}
@csrf
{{--
    GIẢI THÍCH:
    name của input là tên field gửi server; value là nội dung hiện trong ô. old("field", dự phòng)
    lấy input cũ khi validation fail, nếu chưa có thì lấy model hoặc filter/default. Thuộc tính sau
    dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính, price=giá, stock=tồn
    kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ route.
--}}
<label>Tên sản phẩm<input name="name" maxlength="255" required value="{{ old('name', $product->name ?? '') }}">
{{--
    GIẢI THÍCH:
    $message được Laravel tạo trong khối error cho field tương ứng, từ validation của FormRequest;
    không phải biến controller tự truyền.
--}}
@error('name')<small class="error">{{ $message }}</small>
@enderror</label>
<label>Danh mục<select name="category_id" required>
<option value="">Chọn danh mục</option>

{{--
    GIẢI THÍCH:
    In thuộc tính selected khi điều kiện đúng, để dropdown giữ lựa chọn hiện tại. Chuỗi/số ID được
    ép cùng kiểu trước khi so sánh. $categories là danh sách view nhận từ controller. Vòng lặp tự
    tạo $category cho từng record hiện tại; ví dụ record id=2,name=Laptop thì $category->name đọc
    "Laptop" từ cột name. Biến số nhiều và số ít khác nhau. name của input là tên field gửi server;
    value là nội dung hiện trong ô. old("field", dự phòng) lấy input cũ khi validation fail, nếu
    chưa có thì lấy model hoặc filter/default. Thuộc tính sau dấu -> đọc từ record của model, tương
    ứng cột DB: name=tên, id=khóa chính, price=giá, stock=tồn kho, email/phone/company/status là dữ
    liệu Customer; không lấy tên từ route.
--}}
@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>

@endforeach</select>
{{--
    GIẢI THÍCH:
    $message được Laravel tạo trong khối error cho field tương ứng, từ validation của FormRequest;
    không phải biến controller tự truyền.
--}}
@error('category_id')<small class="error">{{ $message }}</small>
@enderror</label>

{{--
    GIẢI THÍCH:
    route("categories.create", model nếu có) tạo URL để mở form tạo: GET /categories/create →
    create(). Hàm route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router
    tải model từ ID khi request đến.
--}}
@if($categories->isEmpty())<p class="error">Chưa có danh mục. <a href="{{ route('categories.create') }}">Tạo danh mục trước</a>.</p>
@endif
<div class="grid">
{{--
    GIẢI THÍCH:
    name của input là tên field gửi server; value là nội dung hiện trong ô. old("field", dự phòng)
    lấy input cũ khi validation fail, nếu chưa có thì lấy model hoặc filter/default. Thuộc tính sau
    dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính, price=giá, stock=tồn
    kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ route.
--}}
<label>Giá (VND)<input type="number" min="0.01" max="9999999999.99" step="0.01" name="price" required value="{{ old('price', $product->price ?? '') }}">
{{--
    GIẢI THÍCH:
    $message được Laravel tạo trong khối error cho field tương ứng, từ validation của FormRequest;
    không phải biến controller tự truyền.
--}}
@error('price')<small class="error">{{ $message }}</small>
@enderror</label>
{{--
    GIẢI THÍCH:
    name của input là tên field gửi server; value là nội dung hiện trong ô. old("field", dự phòng)
    lấy input cũ khi validation fail, nếu chưa có thì lấy model hoặc filter/default. Thuộc tính sau
    dấu -> đọc từ record của model, tương ứng cột DB: name=tên, id=khóa chính, price=giá, stock=tồn
    kho, email/phone/company/status là dữ liệu Customer; không lấy tên từ route.
--}}
<label>Tồn kho<input type="number" min="0" max="2147483647" step="1" name="stock" required value="{{ old('stock', $product->stock ?? 0) }}">
{{--
    GIẢI THÍCH:
    $message được Laravel tạo trong khối error cho field tương ứng, từ validation của FormRequest;
    không phải biến controller tự truyền.
--}}
@error('stock')<small class="error">{{ $message }}</small>
@enderror</label>
</div>
<label>Mô tả<textarea name="description" maxlength="5000">{{ old('description', $product->description ?? '') }}</textarea>
{{--
    GIẢI THÍCH:
    $message được Laravel tạo trong khối error cho field tương ứng, từ validation của FormRequest;
    không phải biến controller tự truyền.
--}}
@error('description')<small class="error">{{ $message }}</small>
@enderror</label>
{{--
    GIẢI THÍCH:
    In disabled khi điều kiện đúng: chặn nút Lưu nếu chưa có danh mục để chọn.
--}}
<button @disabled($categories->isEmpty())>Lưu sản phẩm</button>
{{--
    GIẢI THÍCH:
    route("products.index", model nếu có) tạo URL để mở danh sách: GET /products → index(). Hàm
    route chỉ tạo link, chưa gọi controller ngay. Model truyền vào URL dùng ID; router tải model từ
    ID khi request đến.
--}}
<a href="{{ route('products.index') }}">Quay lại</a>
