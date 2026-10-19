<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('USER'); // D23: user mới mặc định USER; admin do server/seeder gán, không nhận role từ register form.
        });
        Schema::create('cart_items', function (Blueprint $t) { // D24: database cart lưu theo user, còn sau logout; giỏ chưa phải order lịch sử.
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('product_id');
            $t->unsignedInteger('quantity');
            $t->timestamps();
            $t->unique(['user_id', 'product_id']); // D24/D27: mỗi user chỉ có một dòng cho cùng product; add lần nữa phải tăng quantity.
        });
        Schema::create('coupons', function (Blueprint $t) { // D28: dữ liệu coupon hiện tại; order sẽ lưu snapshot riêng khi dùng mã.
            $t->id();
            $t->string('code')->unique();
            $t->unsignedTinyInteger('discount_percent');
            $t->date('start_date');
            $t->date('end_date');
            $t->unsignedInteger('usage_limit');
            $t->unsignedInteger('used_count')->default(0);
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->restrictOnDelete();
            $t->uuid('checkout_token')->unique(); // D27: DB ngăn hai order trùng token; phối hợp với lock user và replay trong service.
            $t->string('status')->default('pending'); // D25/D26: order mới pending; các bước chuyển trạng thái được kiểm trong CheckoutService.
            $t->unsignedBigInteger('subtotal'); // D25/D28: tiền integer theo 1/100 VND; subtotal trước giảm giá, total sau giảm.
            $t->unsignedBigInteger('discount_amount')->default(0);
            $t->unsignedBigInteger('total');
            $t->string('coupon_code')->nullable(); // D28: snapshot mã lúc mua; null khi không dùng coupon.
            $t->unsignedTinyInteger('discount_percent')->default(0);
            $t->timestamps();
        });
        Schema::create('order_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('order_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('product_id');
            $t->string('product_name'); // D25: snapshot tên để lịch sử không đổi theo Product hiện tại.
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_price'); // D25: snapshot đơn giá integer 1/100 VND, không lấy giá live khi xem đơn cũ.
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('cart_items');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('role'));
    }
};
