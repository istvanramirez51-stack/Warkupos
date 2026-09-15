<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\TransactionLog;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = TransactionLog::with(['user:id,name,role'])
            ->when($request->input('action'), fn ($q, $v) => $q->where('action', $v))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Owner/Audit/Index', [
            'logs'    => $logs,
            'filters' => $request->only('action'),
        ]);
    }
}