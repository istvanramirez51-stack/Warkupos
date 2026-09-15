<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // status orders: pending | diproses | siap | paid | void (PRD DPR-02 + KAS-03)
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");

        // status tables: available | occupied | waiting_payment (PRD 5.5)
        DB::statement("ALTER TABLE tables MODIFY status VARCHAR(20) NOT NULL DEFAULT 'available'");
    }

    public function down(): void
    {
        // Sesuaikan dengan definisi ENUM asli dari hasil SHOW COLUMNS (Langkah 1)
    }
};