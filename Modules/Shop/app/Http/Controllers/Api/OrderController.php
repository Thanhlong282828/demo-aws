<?php

namespace Modules\Shop\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Shop\Models\Order;
use Modules\Shop\Services\CheckoutService;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::where('user_id', $request->user()->id)->latest('id')->paginate(15));
    }

    public function show(Request $request, Order $order): OrderResource
    {
        abort_unless($order->user_id === $request->user()->id, 403);

        return new OrderResource($order->load('items'));
    }

    public function admin(): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::latest('id')->paginate(15));
    }

    public function status(Request $request, Order $order, CheckoutService $checkout): OrderResource
    {
        $data = $request->validate(['status' => 'required|in:pending,confirmed,completed,cancelled']);
        $checkout->transition($order, $data['status']);

        return (new OrderResource($order->fresh()->load('items')))->additional(['message' => 'Status updated.']);
    }
}
