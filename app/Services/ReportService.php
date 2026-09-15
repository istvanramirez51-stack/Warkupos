<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * LAP-01 — Laporan harian: total transaksi, pendapatan, rata-rata.
     */
    public function daily(string $date): array
    {
        $stats = Transaction::whereDate('created_at', $date)
            ->selectRaw('COUNT(*) as total_transactions')
            ->selectRaw('COALESCE(SUM(amount), 0) as revenue')
            ->selectRaw('COALESCE(AVG(amount), 0) as avg_per_transaction')
            ->first();

        // Pendapatan per jam (untuk grafik batang harian)
        $hourly = Transaction::whereDate('created_at', $date)
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count, COALESCE(SUM(amount),0) as revenue')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Bandingkan dengan hari sebelumnya
        $yesterday = Transaction::whereDate('created_at', date('Y-m-d', strtotime($date . ' -1 day')))
            ->selectRaw('COALESCE(SUM(amount), 0) as revenue')
            ->value('revenue');

        return [
            'date'               => $date,
            'total_transactions' => (int) $stats->total_transactions,
            'revenue'            => (int) $stats->revenue,
            'avg_per_transaction'=> (int) $stats->avg_per_transaction,
            'yesterday_revenue'  => (int) $yesterday,
            'growth_percent'     => $yesterday > 0
                ? round((($stats->revenue - $yesterday) / $yesterday) * 100, 1)
                : null,
            'hourly'             => $hourly,
        ];
    }

    /**
     * LAP-02 — Laporan bulanan: tren harian sebulan, bandingkan bulan ini vs lalu.
     */
    public function monthly(string $month): array // format: Y-m
    {
        $start = $month . '-01';
        $end   = date('Y-m-t', strtotime($start)); // hari terakhir bulan

        // Pendapatan per hari dalam bulan (untuk grafik garis)
        $daily = Transaction::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, COALESCE(SUM(amount),0) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Total bulan ini
        $thisMonth = Transaction::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->selectRaw('COUNT(*) as total_transactions, COALESCE(SUM(amount),0) as revenue')
            ->first();

        // Total bulan lalu (untuk perbandingan)
        $prevStart = date('Y-m-01', strtotime($start . ' -1 month'));
        $prevEnd   = date('Y-m-t', strtotime($prevStart));
        $prevMonth = Transaction::whereBetween('created_at', [$prevStart . ' 00:00:00', $prevEnd . ' 23:59:59'])
            ->selectRaw('COUNT(*) as total_transactions, COALESCE(SUM(amount),0) as revenue')
            ->first();

        // Rekap metode bayar (PRD LAP-06 bonus)
        $methods = Transaction::whereBetween('created_at', [$start . ' 00:00:00', $end . ' 23:59:59'])
            ->selectRaw('payment_method, COUNT(*) as count, COALESCE(SUM(amount),0) as revenue')
            ->groupBy('payment_method')
            ->get();

        return [
            'month'              => $month,
            'total_transactions' => (int) $thisMonth->total_transactions,
            'revenue'            => (int) $thisMonth->revenue,
            'prev_transactions'  => (int) $prevMonth->total_transactions,
            'prev_revenue'       => (int) $prevMonth->revenue,
            'growth_percent'     => $prevMonth->revenue > 0
                ? round((($thisMonth->revenue - $prevMonth->revenue) / $prevMonth->revenue) * 100, 1)
                : null,
            'daily'              => $daily,
            'methods'            => $methods,
        ];
    }

    /**
     * Menu terlaris periode tertentu (LAP-03 bonus).
     */
    public function topMenus(string $startDate, string $endDate, int $limit = 10): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('menus', 'menus.id', '=', 'order_items.menu_id')
            ->whereIn('orders.status', ['selesai', 'paid'])
            ->whereBetween('orders.created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->selectRaw('menus.name, SUM(order_items.qty) as total_qty, SUM(order_items.qty * order_items.price) as revenue')
            ->groupBy('menus.id', 'menus.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}