<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk {{ $transaction->transaction_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            width: 48mm;               /* kertas 58mm - margin 5mm kiri-kanan (PRD 12.2) */
            margin: 0 auto;
            padding: 8px 2mm;
            color: #000;
        }

        .center { text-align: center; }
        .bold { font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }

        .row { display: flex; justify-content: space-between; gap: 4px; }
        .row .name { flex: 1; word-break: break-word; }

        .item-sub { font-size: 9pt; padding-left: 6px; }

        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; }

        @media print {
            /* Sembunyikan semua kecuali struk (PRD 12.2) */
            body { width: 100%; padding: 0; }
        }
    </style>
</head>
<body>
    <!-- HEADER (PRD 12.1) -->
    <div class="center bold" style="font-size: 11pt;">{{ strtoupper($warung['name']) }}</div>
    @if($warung['address'])
        <div class="center" style="font-size: 9pt;">{{ $warung['address'] }}</div>
    @endif
    @if($warung['phone'])
        <div class="center" style="font-size: 9pt;">WA: {{ $warung['phone'] }}</div>
    @endif

    <div class="divider"></div>

    <!-- INFO TRANSAKSI -->
    <div>No&nbsp;&nbsp;: {{ $transaction->transaction_number }}</div>
    <div>Tgl&nbsp;: {{ $transaction->created_at->format('d/m/Y H:i') }}</div>
    <div>Meja: {{ $order->table->name }}&nbsp;&nbsp;Kasir: {{ $transaction->user->name }}</div>

    <div class="divider"></div>

    <!-- DAFTAR ITEM -->
    @foreach($order->items as $item)
        <div class="row">
            <span class="name">{{ $item->menu->name }}</span>
            <span>x{{ $item->qty }}</span>
        </div>
        @if($item->notes)
            <div class="item-sub">({{ $item->notes }})</div>
        @endif
        <div class="row item-sub">
            <span>{{ number_format($item->price, 0, ',', '.') }}</span>
            <span class="bold">= {{ number_format($item->price * $item->qty, 0, ',', '.') }}</span>
        </div>
    @endforeach

    <div class="divider"></div>

    <!-- RINGKASAN -->
    <table>
        <tr>
            <td>TOTAL</td>
            <td style="text-align: right;">Rp {{ number_format($transaction->amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ ucfirst($transaction->payment_method) }})</td>
            <td style="text-align: right;">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td style="text-align: right;">Rp {{ number_format($transaction->change, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- FOOTER (PRD 12.1 — bisa dikustomisasi di Settings) -->
    <div class="center" style="font-size: 9pt;">
        Terima kasih atas kunjungan<br>
        Anda! Sampai jumpa :)
    </div>

    <script>
        // Auto print saat halaman dibuka (PRD 12.2)
        window.onload = function () { window.print(); };
    </script>
</body>
</html>