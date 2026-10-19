
{{-- D26: OrderController@index/admin dùng chung view; cờ admin quyết định hiện form đổi trạng thái, middleware vẫn bảo vệ backend. --}}

@extends('layouts.app')

@section('content')
    <h1>
        {{ ($admin ?? false) ? 'Quản lý đơn hàng' : 'Đơn của tôi' }}
    </h1>

    <table>
        <thead>
            <tr>
                <th>Đơn</th>
                <th>Ngày</th>
                <th>Trạng thái</th>
                <th>Tổng VND</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($orders as $order)
                <tr>
                    <td>
                        @if ($admin ?? false)
                            #{{ $order->id }} · User {{ $order->user_id }}
                        @else
                            <a href="{{ route('orders.show', $order) }}">
                                #{{ $order->id }}
                            </a>
                        @endif
                    </td>

                    <td>
                        {{ $order->created_at }}
                    </td>

                    <td>
                        {{ $order->status }}

                        @if ($admin ?? false)
                            <form
                                method="POST"
                                action="{{ route('admin.orders.status', $order) }}"
                            >
                                @csrf
                                @method('PATCH')

                                <select name="status">
                                    @foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $status)
                                        <option
                                            value="{{ $status }}"
                                            @selected($order->status === $status)
                                        >
                                            {{ $status }}
                                        </option>
                                    @endforeach
                                </select>

                                <button>Cập nhật</button>
                            </form>
                        @endif
                    </td>

                    <td>
                        {{ number_format($order->total / 100, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        Chưa có đơn.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $orders->links() }}
@endsection
