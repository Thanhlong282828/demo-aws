<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return ['id' => $this->id, 'status' => $this->status, 'subtotal' => $this->subtotal, 'discount_amount' => $this->discount_amount, 'total' => $this->total, 'money_unit' => '0.01 VND', 'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => ['id' => $item->id, 'product_id' => $item->product_id, 'product_name' => $item->product_name, 'quantity' => $item->quantity, 'unit_price' => $item->unit_price]))];
    }
}
