<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Meja {{ $tableName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: monospace;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 24px;
        }

        .warung-name {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .table-name {
            font-size: 64px;
            font-weight: 800;
            letter-spacing: -0.02em;
            margin-bottom: 24px;
        }

        .qr-box { padding: 16px; border: 2px solid #0A0A0A; }

        .qr-box img { display: block; width: 320px; height: 320px; }

        .instruction {
            margin-top: 24px;
            font-size: 16px;
            color: #52525B;
        }

        .url {
            margin-top: 8px;
            font-size: 12px;
            color: #A1A1AA;
            word-break: break-all;
        }

        .no-print { margin-top: 32px; }

        .btn-print {
            font-family: monospace;
            font-size: 16px;
            padding: 12px 32px;
            background: #1F3A8A;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        /* Sama polanya dengan struk — bagian 12.2 PRD:
           sembunyikan tombol saat print, hanya konten yang tercetak */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="warung-name">{{ config('app.name', 'WarkuPos') }}</div>
    <div class="table-name">MEJA {{ $tableName }}</div>

    <div class="qr-box">
        <img src="{{ $qrImage }}" alt="QR Code Meja {{ $tableName }}">
    </div>

    <p class="instruction">Scan QR untuk memesan menu</p>
    <p class="url">{{ $url }}</p>

    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Print QR</button>
    </div>
</body>
</html>