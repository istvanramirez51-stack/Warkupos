<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Category;
use App\Models\Menu;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WarkuposDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Settings Warung (PRD Section 15)
        $settings = [
            ['key' => 'warung_name', 'value' => 'Warung Padang Maju'],
            ['key' => 'warung_address', 'value' => 'Jl. Sudirman No. 12 Medan'],
            ['key' => 'warung_phone', 'value' => '0812-3456-7890'],
            ['key' => 'is_open', 'value' => '1'], // 1 = Buka, 0 = Tutup
            ['key' => 'receipt_footer', 'value' => 'Terima kasih atas kunjungan Anda! Sampai jumpa :)'],
        ];
        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // 2. Kategori Menu (PRD Section 5.2)
        $categories = ['Makanan', 'Minuman', 'Snack', 'Paket'];
        foreach ($categories as $index => $cat) {
            Category::create(['name' => $cat, 'sort_order' => $index]);
        }

        // 3. Data Menu (Terkait ke Kategori)
        $menus = [
            ['category_id' => 1, 'name' => 'Nasi Goreng Spesial', 'price' => 18000, 'is_available' => true],
            ['category_id' => 1, 'name' => 'Nasi Ayam Bakar', 'price' => 22000, 'is_available' => true],
            ['category_id' => 1, 'name' => 'Mie Goreng', 'price' => 15000, 'is_available' => true],
            ['category_id' => 1, 'name' => 'Nasi Rendang', 'price' => 25000, 'is_available' => false], // Contoh menu habis
            ['category_id' => 2, 'name' => 'Es Teh Manis', 'price' => 5000, 'is_available' => true],
            ['category_id' => 2, 'name' => 'Es Jeruk', 'price' => 7000, 'is_available' => true],
            ['category_id' => 2, 'name' => 'Kopi Hitam', 'price' => 6000, 'is_available' => true],
            ['category_id' => 3, 'name' => 'Pisang Goreng', 'price' => 10000, 'is_available' => true],
            ['category_id' => 3, 'name' => 'Tahu Crispy', 'price' => 8000, 'is_available' => true],
            ['category_id' => 4, 'name' => 'Paket Hemat 1 (Nasgor + Es Teh)', 'price' => 20000, 'is_available' => true],
        ];
        foreach ($menus as $menu) {
            Menu::create($menu);
        }

        // 4. Data Meja (PRD Section 5.5)
        for ($i = 1; $i <= 5; $i++) {
            Table::create([
                'name' => 'Meja ' . $i,
                'capacity' => $i <= 3 ? 4 : 6, // Meja 1-3 muat 4 orang, 4-5 muat 6 orang
                'status' => 'available',
                'qr_code' => 'MEJA-' . Str::upper(Str::random(8)), // QR unik untuk self-order
            ]);
        }
    }
}