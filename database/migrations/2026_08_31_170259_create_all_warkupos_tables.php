<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedBigInteger('price');
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('capacity')->default(4);
            $table->enum('status', ['available', 'occupied', 'waiting_payment'])->default('available');
            $table->string('qr_code')->unique();
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['pending', 'processing', 'done', 'cancelled'])->default('pending');
            $table->enum('source', ['kasir', 'qr', 'pelayan'])->default('kasir');
            $table->unsignedBigInteger('total')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained()->restrictOnDelete();
            $table->integer('qty');
            $table->unsignedBigInteger('price');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('amount');
            $table->enum('payment_method', ['cash', 'qris', 'transfer'])->default('cash');
            $table->unsignedBigInteger('change')->default(0);
            $table->enum('receipt_type', ['print', 'whatsapp', 'both'])->default('print');
            $table->string('transaction_number')->unique(); // Format: WKP-YYYYMMDD-XXXX
            $table->timestamps();
        });

        // PRD Section 14: Audit Trail
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('action'); // e.g. 'transaction.created'
            $table->json('meta')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['transaction_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });

        // PRD Section 5.6: Stok Bahan Baku
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit'); // kg, liter, pcs
            $table->decimal('stock_qty', 10, 2)->default(0);
            $table->decimal('min_stock', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('menu_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty_used', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('reports_cache', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // daily, monthly
            $table->date('date');
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(['reports_cache', 'menu_ingredients', 'ingredients', 'transaction_logs', 'transactions', 'order_items', 'orders', 'tables', 'menus', 'categories', 'settings']);
    }
};