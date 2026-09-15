<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Table;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KasirController extends Controller
{
    /**
     * Halaman POS — grid order aktif (KAS-01, MJA-02).
     */
    public function pos()
    {
        // Order aktif = belum dibayar, urutkan paling lama dulu
        $activeOrders = Order::with(['table', 'items.menu'])
            ->whereDoesntHave('transaction')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($o) => [
                'id'         => $o->id,
                'table_name' => $o->table->name,
                'source'     => $o->source,
                'status'     => $o->status,
                'total'      => $o->total,
                'time'       => $o->created_at->format('H:i'),
                'items'      => $o->items->map(fn ($i) => [
                    'name'  => $i->menu->name,
                    'qty'   => $i->qty,
                    'price' => $i->price,
                    'notes' => $i->notes,
                ]),
            ]);

        return Inertia::render('Kasir/POS', [
            'activeOrders' => $activeOrders,
        ]);
    }

    /**
     * Proses pembayaran (KAS-03).
     */
    public function pay(Request $request, Order $order)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'in:tunai,qris,transfer'],
            'paid_amount'    => ['required', 'integer', 'min:0'],
        ]);

        $transaction = app(PaymentService::class)->pay(
            order:      $order->load('table'),
            method:     $validated['payment_method'],
            paidAmount: $validated['paid_amount'],
            kasir:      auth()->user(),
        );

        return back()->with('success', "Pembayaran {$transaction->transaction_number} berhasil — kembalian Rp "
            . number_format($transaction->change, 0, ',', '.'));
    }
}