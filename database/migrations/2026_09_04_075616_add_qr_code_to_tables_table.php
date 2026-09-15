<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ Guard: skip kalau kolom sudah ada (aman dijalankan ulang)
        if (Schema::hasColumn('tables', 'qr_code')) {
            // tetap lanjut ke backfill di bawah
        } else {
            Schema::table('tables', function (Blueprint $table) {
                $table->string('qr_code', 32)->unique()->nullable()->after('status');
            });
        }

        // Backfill: isi meja yang belum punya qr_code
        \App\Models\Table::whereNull('qr_code')
            ->get()
            ->each(fn ($t) => $t->update(['qr_code' => Str::random(16)]));
    }

    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('qr_code');
        });
    }
};