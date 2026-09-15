<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function toggleOpen()
    {
        $status = Setting::where('key', 'is_open')->first();

        // ✅ PERUBAHAN 1: Defensive — buat barisnya kalau belum ada,
        //    mencegah error "read property 'value' on null"
        if (!$status) {
            $status = Setting::create(['key' => 'is_open', 'value' => '1']);
        }

        $newValue = $status->value === '1' ? '0' : '1';
        $status->update(['value' => $newValue]);

        return redirect()->back()->with('success', $newValue === '1' ? 'Warung berhasil dibuka' : 'Warung berhasil ditutup');
    }
}