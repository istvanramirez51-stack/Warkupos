<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class IsWarungOpen
{
    public function handle(Request $request, Closure $next): Response
    {
        // Baca status dari tabel settings (PRD 15.2 — key 'is_open', nilai '1' atau '0')
        $isOpen = DB::table('settings')
            ->where('key', 'is_open')
            ->value('value') === '1';

        if (!$isOpen) {
            return Inertia::render('Public/Closed', [
                'warung' => [
                    'name' => DB::table('settings')->where('key', 'warung_name')->value('value')
                        ?? config('app.name', 'WarkuPos'),
                ],
            ])->toResponse($request);
        }

        return $next($request);
    }
}