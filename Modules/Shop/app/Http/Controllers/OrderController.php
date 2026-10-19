<?php

namespace Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Shop\Models\Order;
use Modules\Shop\Services\CheckoutService;

class OrderController extends Controller
{
    public function index(Request $request): View // D26: GET /my-orders; khác ProductController@index, mỗi controller có index riêng.
    {
        $orders = Order::where('user_id', $request->user()->id)->latest()->paginate(15); // D26: chỉ lấy đơn của chính mình, 15 đơn/trang; không chỉ ẩn đơn khác trên HTML.

        return view('shop::orders', compact('orders'));
    }

    public function show(Request $request, Order $order): View // D26: Laravel route model binding tải Order theo {order}; không tồn tại thì 404.
    {
        abort_unless($order->user_id === $request->user()->id, 403); // D26: đã login nhưng không phải chủ order thì 403, kể cả nhập URL trực tiếp.
        $order->load('items'); // D26: tải relationship items; view dùng product_name/unit_price snapshot, không giá product hiện tại.

        return view('shop::order', compact('order'));
    }

    public function admin(): View // D26: GET /admin/orders đã qua auth và Admin middleware mới tới đây.
    {
        $orders = Order::latest()->paginate(15); // D26: admin được xem danh sách mọi order; không giới hạn user_id như My Orders.

        return view('shop::orders', ['orders' => $orders, 'admin' => true]);
    }

    public function status(Request $request, Order $order, CheckoutService $service): RedirectResponse // D26: PATCH admin/orders/{order}/status; form giả lập PATCH bằng @method.
    {
        $data = $request->validate(['status' => 'required|in:pending,confirmed,completed,cancelled']);
        $service->transition($order, $data['status']); // D26: validate tên status ở controller, service còn kiểm trạng thái nào được chuyển sang đâu.

        return back()->with('success', 'Đã đổi trạng thái.');
    }
}
