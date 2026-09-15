<?php

namespace App\Http\Controllers\Dapur;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DapurController extends Controller
{
    /**
     * Antrian dapur (DPR-01) — polling 5 detik via partial reload.
     */
    public function queue()
    {
        $orders = Order::with(['table', 'items.menu'])
            ->whereIn('status', [Order::STATUS_PENDING, Order::STATUS_DIPROSES])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($o) => [
                'id'         => $o->id,
                'table_name' => $o->table->name,
                'status'     => $o->status,
                'source'     => $o->source,
                'created_at' => $o->created_at->format('H:i'),
                'notes'      => $o->notes,
                'items'      => $o->items->map(fn ($i) => [
                    'name'  => $i->menu->name,
                    'qty'   => $i->qty,
                    'notes' => $i->notes,
                ]),
            ]);

        return Inertia::render('Dapur/Queue', [
            'orders' => $orders,
        ]);
    }

    /**
     * Update status order (DPR-02): pending → diproses → selesai.
     */
    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:diproses,selesai'],
        ]);

        $from = $order->status;
        $order->update(['status' => $validated['status']]);

        // ✅ AUDIT: order.status_changed (PRD 14.1)
        AuditService::log(
            action:  'order.status_changed',
            userId:  auth()->id(),
            orderId: $order->id,
            meta: [
                'from' => $from,
                'to'   => $validated['status'],
            ],
        );

        return back();
    }
}