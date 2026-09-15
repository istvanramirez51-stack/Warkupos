<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {

            // Tambah kolom HANYA kalau belum ada
            if (!Schema::hasColumn('transactions', 'paid_amount')) {
                $table->unsignedBigInteger('paid_amount')->after('amount');
            }

            if (!Schema::hasColumn('transactions', 'change')) {
                $table->unsignedBigInteger('change')->after('paid_amount');
            }

            if (!Schema::hasColumn('transactions', 'receipt_type')) {
                $table->string('receipt_type', 20)->default('print')->after('payment_method');
            }

            if (!Schema::hasColumn('transactions', 'transaction_number')) {
                $table->string('transaction_number', 20)->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            foreach (['paid_amount', 'change', 'receipt_type', 'transaction_number'] as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};