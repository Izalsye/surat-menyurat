<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class SessionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('settings/Session', [
            'items' => DB::table('sessions')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('last_activity')
                ->get()
                ->map(function ($session) {
                    return [
                        'id'           => $session->id,
                        'ip_address'   => $session->ip_address,
                        'user_agent'   => $session->user_agent,
                        'is_current'   => $session->id === session()->getId(),
                        'last_active'  => Carbon::createFromTimestamp($session->last_activity),
                    ];
                })
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        try {
            $input = $request->input('ids');
            if (empty($input)) {
                DB::table('sessions')
                    ->where('user_id', $request->user()->id)
                    ->whereNot('id', session()->getId())
                    ->delete();
            } else {
                DB::table('sessions')
                    ->whereIn('id', $input)
                    ->delete();
            }

            return back()
                ->with('success', __('action.deleted', ['menu' => __('menu.session')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', 'Something went wrong.');
        }
    }
}
