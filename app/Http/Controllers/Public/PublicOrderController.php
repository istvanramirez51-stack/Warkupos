<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePublicOrderRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Table;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PublicOrderController extends Controller
{
    /**
     * Halaman menu self-order (QR-02) — via scan QR, tanpa login.
     */
    public function menu(string $qrCode)
    {
        $table = Table::where('qr_code', $qrCode)->firstOrFail();

        $categories = Category::with(['menus' => function ($q) {
                $q->where('is_available', true)->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->get()
            ->filter(fn ($c) => $c->menus->isNotEmpty())
            ->values();

        return Inertia::render('Public/Menu', [
            'table' => [
                'id'   => $table->id,
                'name' => $table->name,
            ],
            'categories' => $categories,
            'warung' => [
                'name' => DB::table('settings')->where('key', 'warung_name')->value('value')
                    ?? config('app.name', 'WarkuPos'),
            ],
        ]);
    }

    /**
     * Submit order pelanggan (QR-03).
     */
    public function store(StorePublicOrderRequest $request, string $qrCode)
    {
        $table = Table::where('qr_code', $qrCode)->firstOrFail();

        $order = app(OrderService::class)->createFromQr(
            table: $table,
            validatedItems: $request->validated('items'),
            orderNotes: $request->validated('notes'),
        );

        return redirect()->route('public.confirm', $table->qr_code)
            ->with('order', [
                'id'    => $order->id,
                'total' => $order->total,
                'items' => $order->items->map(fn ($i) => [
                    'name'  => $i->menu->name,
                    'qty'   => $i->qty,
                    'price' => $i->price,
                    'notes' => $i->notes,
                ]),
            ]);
    }

    /**
     * Halaman konfirmasi (QR-04).
     */
    public function confirm(string $qrCode)
    {
        $order = session('order');

        if (!$order) {
            // Kalau refresh tanpa data order — balik ke menu
            return redirect()->route('public.menu', $qrCode);
        }

        return Inertia::render('Public/OrderConfirm', [
            'qrCode' => $qrCode,
            'order'  => $order,
        ]);
    }
}