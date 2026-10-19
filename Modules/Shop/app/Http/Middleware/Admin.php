<?php

namespace Modules\Shop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    public function handle(Request $request, Closure $next): Response // D23: route gắn middleware Admin sẽ được Laravel đưa request qua hàm handle này.
    {
        abort_unless($request->user()?->role === 'ADMIN', 403); // D23: chỉ ADMIN được qua; USER bị 403 dù tự gửi request. auth chạy trước ở nhóm route.

        return $next($request); // D23: đủ quyền thì đi tiếp tới middleware/route mà URL đang yêu cầu; không chạy mọi route trong nhóm.
    }
}
