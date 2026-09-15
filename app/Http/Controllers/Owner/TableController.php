<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\TableRequest;
use App\Models\Table;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::orderBy('name')->get();
        return Inertia::render('Owner/Tables/Index', compact('tables'));
    }

    public function store(TableRequest $request)
    {
        Table::create([
            'name' => $request->name,
            'capacity' => $request->capacity,
            'qr_code' => 'MEJA-' . strtoupper(Str::random(8)), // Auto generate QR unik
            'status' => 'available'
        ]);
        return redirect()->back()->with('success', 'Meja berhasil ditambahkan');
    }

    public function update(TableRequest $request, Table $table)
    {
        $table->update($request->only('name', 'capacity'));
        return redirect()->back()->with('success', 'Meja berhasil diperbarui');
    }

    public function destroy(Table $table)
    {
        $table->delete();
        return redirect()->back()->with('success', 'Meja berhasil dihapus');
    }

    /**
     * Preview QR kecil — dipakai <img> di halaman Meja.
     * Output SVG, generate lokal, tidak depend API eksternal maupun GD.
     */
    public function qrImage(Table $table)
    {
        $this->ensureQrCode($table);

        $url = url('/order/' . $table->qr_code);

        $result = (new QRCode($this->qrOptions()))->render($url);

        // Normalisasi: render() bisa return markup SVG langsung ATAU data URI —
        // pastikan yang dikirim ke browser selalu markup SVG murni
        if (str_starts_with($result, 'data:image/svg+xml;base64,')) {
            $result = base64_decode(substr($result, strlen('data:image/svg+xml;base64,')));
        }

        return response($result)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * Halaman print QR — buka di tab baru, tombol print pakai window.print()
     * (pola sama dengan cetak struk — PRD 12.2)
     */
    public function printQr(Table $table)
    {
        $this->ensureQrCode($table);

        $url = url('/order/' . $table->qr_code);

        $result = (new QRCode($this->qrOptions(scale: 8)))->render($url);

        // Normalisasi juga, lalu jadikan data URI untuk <img src> di blade
        if (str_starts_with($result, 'data:image/svg+xml;base64,')) {
            $svg = base64_decode(substr($result, strlen('data:image/svg+xml;base64,')));
        } else {
            $svg = $result;
        }

        return view('tables.qr-print', [
            'tableName' => $table->name,
            'url'       => $url,
            'qrImage'   => 'data:image/svg+xml;base64,' . base64_encode($svg),
        ]);
    }

    /**
     * Regenerate QR — dipakai kalau QR lama perlu di-invalidate
     * (misal generate waktu masih di localhost).
     */
    public function regenerateQr(Table $table)
    {
        $table->update(['qr_code' => 'MEJA-' . strtoupper(Str::random(8))]);
        return redirect()->back()->with('success', 'QR Code berhasil di-generate ulang');
    }

    // --------------------------------------------------------

    /**
     * Safety untuk meja lama yang dibuat sebelum kolom qr_code ada.
     */
    private function ensureQrCode(Table $table): void
    {
        if (!$table->qr_code) {
            $table->update(['qr_code' => 'MEJA-' . strtoupper(Str::random(8))]);
        }
    }

    /**
     * Konfigurasi QR.
     * Catatan: pakai string literal, bukan constant package —
     * nama constant berubah antar versi mayor, nilai string-nya stabil.
     */
    private function qrOptions(int $scale = 4): QROptions
    {
        return new QROptions([
            'outputType'       => 'svg', // markup SVG — tidak butuh GD
            'eccLevel'         => 'M',   // error correction Medium
            'scale'            => $scale,
            'imageTransparent' => false,
        ]);
    }
}