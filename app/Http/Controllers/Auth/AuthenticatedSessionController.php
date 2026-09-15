<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): \Inertia\Response
    {
        return inertia('Auth/Login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Coba lakukan login menggunakan kolom 'phone' 
        // (berkat method username() yang sudah kita buat di User.php)
        if (! Auth::attempt($request->only('phone', 'password'), $request->boolean('remember'))) {
            
            // 2. Jika gagal, lempar error generik ke field 'error' 
            // (Sesuai PRD: jangan sebut mana yang salah)
            throw ValidationException::withMessages([
                'error' => 'Nomor HP atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        // Redirect ke halaman dashboard ( akan kita buat next step )
        return redirect()->intended(route('owner.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}