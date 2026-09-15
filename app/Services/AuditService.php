<?php

namespace App\Services;

use App\Models\TransactionLog;
use Illuminate\Support\Facades\Log;

class AuditService
{
    /**
     * Catat aksi ke audit trail (PRD 14).
     * TIDAK PERNAH memblokir transaksi utama — error di-log saja (PRD 21).
     */
    public static function log(
        string $action,
        ?int $userId = null,
        ?int $orderId = null,
        ?int $transactionId = null,
        array $meta = [],
    ): void {
        try {
            TransactionLog::create([
                'action'         => $action,
                'user_id'        => $userId,
                'order_id'       => $orderId,
                'transaction_id' => $transactionId,
                'meta'           => $meta,
                'ip_address'     => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditService gagal: ' . $e->getMessage());
        }
    }
}