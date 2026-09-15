<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OwnerSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Owner WarkuPos',
            'phone' => '081234567890',
            'password' => Hash::make('password123'), // Password untuk testing
            'role' => 'owner', // Kita belum buat enum, jadi string dulu
        ]);
    }
}