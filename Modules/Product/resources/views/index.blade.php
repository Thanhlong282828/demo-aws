
@extends('layouts.app')

{{-- Tiêu đề của trang --}}
@section('title', 'Sản phẩm — Mini Shop')

{{-- Nội dung chính --}}
@section('content')

    {{-- ============================================
         1. THANH TIÊU ĐỀ
         ============================================ --}}
    <div class="toolbar">

        <h1>Sản phẩm</h1>

        {{-- total() lấy tổng số sản phẩm sau khi lọc, trước khi phân trang --}}
        <span>{{ $products->total() }} sản phẩm</span>

        {{-- Chỉ hiển thị nút tạo sản phẩm nếu User có role ADMIN --}}
        @if(auth()->user()?->role === 'ADMIN')
            <a class="button" href="{{ route('products.create') }}">
                + Tạo sản phẩm
            </a>
        @endif

    </div>


    {{-- ============================================
         2. FORM TÌM KIẾM / LỌC / SẮP XẾP
         ============================================ --}}
    <form
        method="GET"
        action="{{ route('products.index') }}"
        class="filters"
    >
        <div class="grid">

            {{-- Tìm sản phẩm theo tên --}}
            <label>
                Tìm tên

                <input
                    name="keyword"
                    value="{{ $filters['keyword'] ?? '' }}"
                >
            </label>

            {{-- Lọc theo danh mục --}}
            <label>
                Danh mục

                <select name="category_id">

                    <option value="">Tất cả</option>

                    @foreach($categories as $category)

                        <option
                            value="{{ $category->id }}"
                            @selected(
                                ($filters['category_id'] ?? '') == $category->id
                            )
                        >
                            {{ $category->name }}
                        </option>

                    @endforeach

                </select>
            </label>

            {{-- Giá tối thiểu --}}
            <label>
                Giá từ

                <input
                    type="number"
                    name="min_price"
                    step="0.01"
                    value="{{ $filters['min_price'] ?? '' }}"
                >
            </label>

            {{-- Giá tối đa --}}
            <label>
                Giá đến

                <input
                    type="number"
                    name="max_price"
                    step="0.01"
                    value="{{ $filters['max_price'] ?? '' }}"
                >
            </label>

            {{-- Sắp xếp sản phẩm --}}
            <label>
                Sắp xếp

                <select name="sort">

                    @foreach([
                        'newest'     => 'Mới nhất',
                        'price_asc'  => 'Giá tăng dần',
                        'price_desc' => 'Giá giảm dần'
                    ] as $value => $label)

                        <option
                            value="{{ $value }}"
                            @selected(
                                ($filters['sort'] ?? 'newest') === $value
                            )
                        >
                            {{ $label }}
                        </option>

                    @endforeach

                </select>
            </label>

        </div>

        {{-- Gửi form GET để lọc sản phẩm --}}
        <button>Lọc</button>

        {{-- Quay về trang index không có bộ lọc --}}
        <a href="{{ route('products.index') }}">
            Xóa bộ lọc
        </a>

    </form>


    {{-- ============================================
         3. BẢNG DANH SÁCH SẢN PHẨM
         ============================================ --}}
    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Giá VND</th>
                    <th>Tồn kho</th>
                    <th>Thao tác</th>
                </tr>
            </thead>

            <tbody>

                {{-- Duyệt từng sản phẩm trong kết quả phân trang --}}
                @forelse($products as $product)

                    <tr>

                        {{-- Tên sản phẩm --}}
                        <td>{{ $product->name }}</td>

                        {{-- Tên danh mục thông qua quan hệ category --}}
                        <td>{{ $product->category->name }}</td>

                        {{-- Định dạng giá với 2 chữ số thập phân --}}
                        <td>
                            {{ number_format((float) $product->price, 2) }}
                        </td>

                        {{-- Số lượng tồn kho --}}
                        <td>{{ $product->stock }}</td>

                        <td>
                            <div class="actions">

                                {{-- ==================================
                                     4. CHỨC NĂNG GIỎ HÀNG
                                     ================================== --}}

                                {{-- Người dùng đã đăng nhập --}}
                                @auth

                                    <form
                                        method="POST"
                                        action="{{ route('cart.add') }}"
                                    >
                                        {{-- CSRF chống giả mạo request --}}
                                        @csrf

                                        {{-- Gửi ID sản phẩm --}}
                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="{{ $product->id }}"
                                        >

                                        {{-- Hết hàng thì vô hiệu hóa nút --}}
                                        <button
                                            @disabled($product->stock < 1)
                                        >
                                            Thêm giỏ
                                        </button>

                                    </form>

                                {{-- Người dùng chưa đăng nhập --}}
                                @else

                                    <a href="{{ route('login') }}">
                                        Đăng nhập để mua
                                    </a>

                                @endauth


                                {{-- ==================================
                                     5. CHỨC NĂNG ADMIN
                                     ================================== --}}

                                {{-- Chỉ ADMIN thấy nút sửa và xóa --}}
                                @if(auth()->user()?->role === 'ADMIN')

                                    {{-- Chuyển đến trang sửa sản phẩm --}}
                                    <a href="{{ route('products.edit', $product) }}">
                                        Sửa
                                    </a>

                                    {{-- Form xóa sản phẩm --}}
                                    <form
                                        method="POST"
                                        action="{{ route('products.destroy', $product) }}"
                                    >
                                        @csrf

                                        {{-- HTML không hỗ trợ DELETE trực tiếp --}}
                                        @method('DELETE')

                                        <button class="danger">
                                            Xóa
                                        </button>

                                    </form>

                                @endif

                            </div>
                        </td>

                    </tr>

                {{-- Không có sản phẩm phù hợp --}}
                @empty

                    <tr>
                        <td colspan="5">
                            Không tìm thấy sản phẩm phù hợp.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ============================================
         6. PHÂN TRANG
         ============================================ --}}

    {{-- Truyền $products vào component dưới tên $paginator --}}
    @include('components.pagination', [
        'paginator' => $products
    ])

@endsection
