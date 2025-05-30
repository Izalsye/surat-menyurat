<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutgoingLetter\StoreRequest;
use App\Http\Requests\OutgoingLetter\UpdateRequest;
use App\Jobs\GenerateOutgoingLetterReport;
use App\Mail\DispositionDone;
use App\Models\Disposition;
use App\Models\LetterCategory;
use App\Models\OutgoingLetter;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OutgoingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('outgoing_letter/Index', [
            'items' => OutgoingLetter::with(['letter_categories', 'disposition'])
                ->latest()
                ->filter($request->query())
                ->render($request->query('size')),
            'categories' => LetterCategory::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            if ($input['letter_date']) {
                $input['letter_date'] = Carbon::parse($input['letter_date']);
            }
            $input['file'] = $input['file']->store('outgoing_letter');
            $input['created_by'] = $request->user()->id;
            $letter = OutgoingLetter::query()->create($input);
            if ($codes = $request->input('categories')) {
                $categories = LetterCategory::query()
                    ->whereIn('code', $codes)
                    ->pluck('id')->toArray();
                $letter->letter_categories()->attach($categories);
            }

            if ($request->input('disposition_id')) {
                $disposition = Disposition::query()->find($request->input('disposition_id'));
                $disposition->update([
                    'is_done' => true,
                    'done_at' => now(),
                ]);
                $assignee = $disposition->assignee;
                if ($assignee) {
                    Mail::to($assignee->email)
                        ->queue(new DispositionDone($assignee, $disposition, $letter));
                }
                $assigner = $disposition->assigner;
                Mail::to($assigner->email)
                    ->queue(new DispositionDone($assigner, $disposition, $letter));
            }

            return back()->with('success', __('action.created', ['menu' => __('menu.outgoing_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(OutgoingLetter $letter): Response
    {
        $letter->load([
            'letter_categories', 'incoming_letter', 'disposition',
            'disposition.assigner', 'disposition.assignee',
        ]);

        return Inertia::render('outgoing_letter/Show', [
            'item' => $letter,
            'next' => OutgoingLetter::query()
                ->where('created_at', '<', $letter->created_at)
                ->orderByDesc('created_at')
                ->limit(1)
                ->value('id'),
            'prev' => OutgoingLetter::query()
                ->where('created_at', '>', $letter->created_at)
                ->orderBy('created_at')
                ->limit(1)
                ->value('id'),
            'total' => OutgoingLetter::query()->count('id'),
            'index' => OutgoingLetter::query()
                    ->where('created_at', '>', $letter->created_at)
                    ->orderByDesc('created_at')
                    ->count('id') + 1,
            'categories' => LetterCategory::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, OutgoingLetter $letter): RedirectResponse
    {
        try {
            $input = $request->validated();
            if ($input['letter_date']) {
                $input['letter_date'] = Carbon::parse($input['letter_date']);
            }
            $letter->update($input);
            if ($codes = $request->input('categories')) {
                $categories = LetterCategory::query()
                    ->whereIn('code', $codes)
                    ->pluck('id')->toArray();
                $letter->letter_categories()->sync($categories);
            }

            return back()->with('success', __('action.updated', ['menu' => __('menu.outgoing_letter')]));;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, OutgoingLetter $letter): RedirectResponse
    {
        try {
            $nextId = $request->input('prev') ?? $request->input('next');
            $letter->delete();
            if ($nextId) {
                return redirect()
                    ->route('outgoing-letter.show', $nextId)
                    ->with('success', __('action.deleted', ['menu' => __('menu.outgoing_letter')]));
            }
            return redirect()->route('outgoing-letter.index')
                ->with('success', __('action.deleted', ['menu' => __('menu.outgoing_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function massDestroy(Request $request): RedirectResponse
    {
        try {
            $ids = $request->input('ids');
            if (empty($ids)) {
                throw new \Exception('Empty ids');
            }

            $files = OutgoingLetter::query()
                ->whereIn('id', $ids)
                ->pluck('file')->toArray();
            foreach ($files as $file) {
                Storage::delete($file);
            }

            OutgoingLetter::query()
                ->whereIn('id', $ids)
                ->delete();

            return back()->with('success', __('action.deleted', ['menu' => __('menu.outgoing_letter')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function export(Request $request): RedirectResponse
    {
        try {
            $input = $request->all();
            $user = $request->user();

            GenerateOutgoingLetterReport::dispatch($user, $input);

            return back()->with('success', __('action.report'));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());
            return back()->with('error', $exception->getMessage());
        }
    }
}
