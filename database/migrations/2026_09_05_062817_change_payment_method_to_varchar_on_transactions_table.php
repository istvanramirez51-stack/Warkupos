<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah dari ENUM ke VARCHAR(20) — nilai: tunai | qris | transfer (PRD KAS-03)
        DB::statement("ALTER TABLE transactions MODIFY payment_method VARCHAR(20) NOT NULL");
    }

    public function down(): void
    {
        // Kembalikan ke definisi lama (sesuaikan dengan hasil SHOW COLUMNS kamu)
        DB::statement("ALTER TABLE transactions MODIFY payment_method ENUM('cash','qris','transfer') NOT NULL");
    }
};