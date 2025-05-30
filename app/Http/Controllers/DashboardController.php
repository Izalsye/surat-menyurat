<?php

namespace App\Http\Controllers;

use App\Enum\IncomingStatus;
use App\Enum\Permission;
use App\Models\Disposition;
use App\Models\IncomingLetter;
use App\Models\LetterCategory;
use App\Models\OutgoingLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $summaries = [
            [
                'title' => __('menu.incoming_letter'),
                'total' => IncomingLetter::query()->count(),
                'link' => route('incoming-letter.index'),
                'allow' => Gate::allows(Permission::ViewIncomingLetter),
            ],
            [
                'title' => __('menu.outgoing_letter'),
                'total' => OutgoingLetter::query()->count(),
                'link' => route('outgoing-letter.index'),
                'allow' => Gate::allows(Permission::ViewOutgoingLetter),
            ],
            [
                'title' => __('menu.no_disposition'),
                'total' => IncomingLetter::query()
                    ->whereDoesntHave('dispositions')
                    ->count(),
                'link' => route('incoming-letter.index', ['status' => IncomingStatus::NoDisposition->value]),
                'allow' => Gate::allows(Permission::ViewIncomingLetter),
            ],
            [
                'title' => __('menu.need_action'),
                'total' => IncomingLetter::query()
                    ->whereHas('dispositions', function ($query) {
                        $query->whereDoesntHave('children')
                            ->where('reply_letter', true)
                            ->where('is_done', false);
                    })->count(),
                'link' => route('incoming-letter.index', ['status' => IncomingStatus::NeedAction->value]),
                'allow' => Gate::allows(Permission::ViewIncomingLetter),
            ],
        ];

        return Inertia::render('dashboard/Dashboard', [
            'summaries' => $summaries,
        ]);
    }
}
