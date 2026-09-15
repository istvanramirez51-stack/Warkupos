<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    /**
     * Proses pembayaran (KAS-03).
     */
    public function pay(Order $order, string $method, int $paidAmount, $kasir): Transaction
    {
        return DB::transaction(function () use ($order, $method, $paidAmount, $kasir) {

            if ($order->transaction) {
                abort(422, 'Order ini sudah dibayar.');
            }

            if ($paidAmount < $order->total) {
                abort(422, 'Nominal bayar kurang dari total tagihan.');
            }

            $transaction = Transaction::create([
                'transaction_number' => $this->generateNumber(),
                'order_id'           => $order->id,
                'user_id'            => $kasir->id,
                'amount'             => $order->total,
                'paid_amount'        => $paidAmount,
                'change'             => $paidAmount - $order->total,
                'payment_method'     => $method,
                'receipt_type'       => 'print',
            ]);

            $order->update(['status' => Order::STATUS_PAID]);
            $order->table()->update(['status' => 'available']);

            // ✅ AUDIT: transaction.created (PRD 14.1)
            AuditService::log(
                action:        'transaction.created',
                userId:        $kasir->id,
                orderId:       $order->id,
                transactionId: $transaction->id,
                meta: [
                    'amount'         => $transaction->amount,
                    'payment_method' => $method,
                    'paid_amount'    => $paidAmount,
                    'change'         => $transaction->change,
                    'receipt_type'   => 'print',
                    'table_name'     => $order->table->name,
                ],
            );

            return $transaction;
        });
    }

    /**
     * Nomor transaksi: WKP-YYYYMMDD-XXXX (PRD 12.4).
     */
    private function generateNumber(): string
    {
        $prefix = 'WKP-' . now()->format('Ymd') . '-';
        $count  = Transaction::whereDate('created_at', today())->count() + 1;

        return $prefix . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}