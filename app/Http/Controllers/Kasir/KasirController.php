<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Transaction;
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
            // Flash dari pay() — muncul hanya setelah pembayaran sukses
            'receipt'      => session('receipt'),
        ]);
    }

    /**
     * Proses pembayaran (KAS-03) + siapkan data struk (KAS-04).
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

        // Data struk — format sesuai PRD 12.1
        $order->load('items.menu');

        $receipt = [
            'id'     => $transaction->id,
            'number' => $transaction->transaction_number,
            'datetime' => $transaction->created_at->format('d/m/Y H:i'),
            'table'    => $order->table->name,
            'kasir'    => auth()->user()->name,
            'items'    => $order->items->map(fn ($i) => [
                'name'  => $i->menu->name,
                'qty'   => $i->qty,
                'price' => $i->price,
                'notes' => $i->notes,
            ])->all(),
            'total'    => $transaction->amount,
            'paid'     => $transaction->paid_amount,
            'change'   => $transaction->change,
            'method'   => $transaction->payment_method,
            'warung'   => $this->warungInfo(),
        ];

        return redirect()->route('kasir.pos')->with('receipt', $receipt);
    }

    /**
     * Halaman cetak struk — render Blade 48mm, auto window.print() (PRD 12.2).
     */
    public function printReceipt(Transaction $transaction)
    {
        $transaction->load(['order.items.menu', 'order.table', 'user']);

        return view('receipts.print', [
            'transaction' => $transaction,
            'order'       => $transaction->order,
            'warung'      => $this->warungInfo(),
        ]);
    }

    /**
     * Info warung dari settings — header struk (PRD 12.1).
     */
    private function warungInfo(): array
    {
        return [
            'name'    => Setting::where('key', 'warung_name')->value('value') ?? config('app.name', 'WarkuPos'),
            'address' => Setting::where('key', 'warung_address')->value('value') ?? '',
            'phone'   => Setting::where('key', 'warung_phone')->value('value') ?? '',
        ];
    }
}