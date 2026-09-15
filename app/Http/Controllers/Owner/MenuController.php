<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreMenuRequest;
use App\Models\Category;
use App\Models\Menu;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->orderBy('created_at', 'desc')->get();
        $categories = Category::orderBy('sort_order')->get();

        return Inertia::render('Owner/Menus/Index', [
            'menus' => $menus,
            'categories' => $categories,
        ]);
    }

    public function store(StoreMenuRequest $request)
    {
        $data = $request->validated();

        // PROSES UPLOAD GAMBAR
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        Menu::create($data);
        return redirect()->back()->with('success', 'Menu berhasil ditambahkan');
    }

    public function update(StoreMenuRequest $request, Menu $menu)
    {
        $data = $request->validated();

        // PROSES UPLOAD GAMBAR BARU (dan hapus yang lama)
        if ($request->hasFile('image')) {
            if ($menu->image) {
                Storage::disk('public')->delete($menu->image);
            }
            $data['image'] = $request->file('image')->store('menus', 'public');
        }

        $menu->update($data);
        return redirect()->back()->with('success', 'Menu berhasil diperbarui');
    }

    public function destroy(Menu $menu)
    {
        // Hapus file gambar dari storage saat menu dihapus
        if ($menu->image) {
            Storage::disk('public')->delete($menu->image);
        }
        
        $menu->delete();
        return redirect()->back()->with('success', 'Menu berhasil dihapus');
    }

    public function toggleAvailability(Menu $menu)
    {
        $menu->update(['is_available' => !$menu->is_available]);
        return redirect()->back();
    }
}