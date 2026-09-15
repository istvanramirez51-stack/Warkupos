<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public — tanpa auth, proteksi via middleware warung.open + throttle
    }

    public function rules(): array
    {
        return [
            'items'               => ['required', 'array', 'min:1', 'max:50'],
            'items.*.menu_id'     => ['required', 'integer', 'exists:menus,id'],
            'items.*.qty'         => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.notes'       => ['nullable', 'string', 'max:100'],
            'notes'               => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'       => 'Keranjang tidak boleh kosong.',
            'items.*.menu_id.exists' => 'Ada menu yang tidak valid.',
            'items.*.qty.max'      => 'Jumlah per item maksimal 99.',
            'items.*.notes.max'    => 'Catatan per item maksimal 100 karakter.',
        ];
    }
}