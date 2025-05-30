<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('activity_log/Index', [
            'items' => ActivityLog::with(['causer'])
                ->where('causer_id', $request->user()->id)
                ->latest()
                ->filter($request->query('filter'))
                ->render($request->query('size')),
        ]);
    }
}
