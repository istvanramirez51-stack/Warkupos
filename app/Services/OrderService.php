<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\Order;
use App\Models\Table;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Buat order dari QR self-order (QR-03).
     * KEAMANAN: harga dihitung ulang dari DB — tidak pernah percaya input client.
     */
    public function createFromQr(Table $table, array $validatedItems, ?string $orderNotes = null): Order
    {
        return DB::transaction(function () use ($table, $validatedItems, $orderNotes) {

            $order = Order::create([
                'table_id' => $table->id,
                'user_id'  => null,
                'status'   => Order::STATUS_PENDING,
                'source'   => 'qr',
                'total'    => 0,
                'notes'    => $orderNotes,
            ]);

            $total = 0;

            foreach ($validatedItems as $item) {
                $menu = Menu::where('id', $item['menu_id'])
                    ->where('is_available', true)
                    ->firstOrFail();

                $subtotal = $menu->price * $item['qty'];
                $total   += $subtotal;

                $order->items()->create([
                    'menu_id' => $menu->id,
                    'qty'     => $item['qty'],
                    'price'   => $menu->price,
                    'notes'   => $item['notes'] ?? null,
                ]);
            }

            $order->update(['total' => $total]);

            if ($table->status === 'available') {
                $table->update(['status' => 'occupied']);
            }

            // ✅ AUDIT: order.created (PRD 14.1)
            AuditService::log(
                action:  'order.created',
                userId:  null,
                orderId: $order->id,
                meta: [
                    'table_id'   => $table->id,
                    'table_name' => $table->name,
                    'source'     => 'qr',
                    'total'      => $total,
                    'items'      => collect($validatedItems)->map(fn ($i) => [
                        'menu_id' => $i['menu_id'],
                        'qty'     => $i['qty'],
                    ])->all(),
                ],
            );

            return $order->load('items.menu');
        });
    }
}