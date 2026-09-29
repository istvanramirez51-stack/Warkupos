<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class StockController extends Controller
{
    /**
     * Daftar bahan (STK-01) — urutkan yang kritis paling atas.
     */
    public function index()
    {
        $ingredients = Ingredient::orderByRaw('stock_qty <= min_stock DESC, name ASC')->get();

        return Inertia::render('Owner/Stock/Index', [
            'ingredients' => $ingredients,
            'lowStockCount' => $ingredients->filter(fn ($i) => $i->isLowStock())->count(),
        ]);
    }

    /**
     * Tambah bahan baru (STK-01).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100', 'unique:ingredients,name'],
            'unit'      => ['required', 'in:kg,gram,liter,ml,pcs'],
            'stock_qty' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'numeric', 'min:0'],
        ], [
            'name.unique' => 'Bahan dengan nama ini sudah ada.',
        ]);

        Ingredient::create($validated);
        return redirect()->back()->with('success', 'Bahan berhasil ditambahkan');
    }

    /**
     * Update stok manual (STK-02) — input penambahan/pengurangan harian.
     * mode: 'add' (restok) | 'set' (set langsung)
     */
    public function updateStock(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'mode'      => ['required', 'in:add,set'],
            'qty'       => ['required', 'numeric', 'min:0'],
        ]);

        $newStock = $validated['mode'] === 'add'
            ? $ingredient->stock_qty + $validated['qty']
            : $validated['qty'];

        $ingredient->update(['stock_qty' => $newStock]);

        $message = $validated['mode'] === 'add'
            ? "Stok {$ingredient->name} ditambah {$validated['qty']} {$ingredient->unit}"
            : "Stok {$ingredient->name} diset ke {$newStock} {$ingredient->unit}";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Edit detail bahan (nama, unit, threshold).
     */
    public function update(Request $request, Ingredient $ingredient)
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:100', Rule::unique('ingredients')->ignore($ingredient->id)],
            'unit'      => ['required', 'in:kg,gram,liter,ml,pcs'],
            'min_stock' => ['required', 'numeric', 'min:0'],
        ], [
            'name.unique' => 'Bahan dengan nama ini sudah ada.',
        ]);

        $ingredient->update($validated);
        return redirect()->back()->with('success', 'Bahan berhasil diperbarui');
    }

    /**
     * Hapus bahan.
     */
    public function destroy(Ingredient $ingredient)
    {
        // Guard: bahan yang terikat resep tidak boleh dihapus (data resep korup)
        if ($ingredient->menus()->exists()) {
            return redirect()->back()->with('error', "Tidak bisa menghapus {$ingredient->name} — masih terikat ke resep menu.");
        }

        $ingredient->delete();
        return redirect()->back()->with('success', 'Bahan berhasil dihapus');
    }
}