<?php

namespace App\Http\Controllers;

use App\Enum\Permission;
use App\Http\Requests\Disposition\StoreRequest;
use App\Http\Requests\Disposition\UpdateRequest;
use App\Mail\DispositionDone;
use App\Mail\DispositionNotification;
use App\Models\Disposition;
use App\Models\IncomingLetter;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DispositionController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, IncomingLetter $letter): RedirectResponse
    {
        try {
            $input = $request->validated();
            $disposition = $letter->dispositions()->create($input);

            if ($request->input('assignee_id')) {
                $assignee = User::query()
                    ->find($request->input('assignee_id'));
                Mail::to($assignee->email)
                    ->queue(new DispositionNotification($assignee, $disposition));
            }

            return back()->with('success', __('action.created', ['menu' => __('menu.disposition')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, IncomingLetter $letter, Disposition $disposition): RedirectResponse
    {
        try {
            $input = $request->validated();
            if (
                ($request->input('assignee_id') && $request->input('assignee_id') != $disposition->assignee_id) ||
                ($request->input('urgency')) !== $disposition->urgency
            ) {
                $assignee = User::query()
                    ->find($request->input('assignee_id'));
                Mail::to($assignee->email)
                    ->queue(new DispositionNotification($assignee, $disposition));
            }
            $disposition->update($input);

            return back()->with('success', __('action.updated', ['menu' => __('menu.disposition')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IncomingLetter $letter, Disposition $disposition): RedirectResponse
    {
        $user = auth()->user();
        if (!Gate::allows(Permission::DeleteDisposition) && $disposition->assigner_id !== $user->id) {
            abort(403);
        }

        try {
            $disposition->delete();

            return back()->with('success', __('action.deleted', ['menu' => __('menu.disposition')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function markAsDone(IncomingLetter $letter, Disposition $disposition): RedirectResponse
    {
        $user = auth()->user();
        if (!Gate::allows(Permission::EditDisposition) && !in_array($user->id, [$disposition->assignee_id, $disposition->assigner_id])) {
            abort(403);
        }

        try {
            $disposition->update([
                'is_done' => true,
                'done_at' => now(),
            ]);
            $assignee = $disposition->assignee;
            if ($assignee) {
                Mail::to($assignee->email)
                    ->queue(new DispositionDone($assignee, $disposition, null));
            }
            $assigner = $disposition->assigner;
            Mail::to($assigner->email)
                ->queue(new DispositionDone($assigner, $disposition, null));

            return back()->with('success', __('action.updated', ['menu' => __('menu.disposition')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }
}
