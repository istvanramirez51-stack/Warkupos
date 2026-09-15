<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Contoh penggunaan di route: ->middleware('role:owner,kasir')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Jika user belum login, lempar ke halaman login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Cek apakah role user ada di daftar role yang diizinkan
        if (!in_array($request->user()->role, $roles)) {
            // Jika tidak punya akses, bisa diarahkan ke halaman 403 atau dashboard umum
            abort(403, 'ANDA TIDAK PUNYA AKSES KE HALAMAN INI.');
        }

        return $next($request);
    }
}