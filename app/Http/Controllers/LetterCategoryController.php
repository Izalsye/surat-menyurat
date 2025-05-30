<?php

namespace App\Http\Controllers;

use App\Http\Requests\LetterCategory\StoreRequest;
use App\Http\Requests\LetterCategory\UpdateRequest;
use App\Models\LetterCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class LetterCategoryController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('letter_category/Index', [
            'items' => LetterCategory::query()
                ->filter($request->query('filter'))
                ->render($request->query('size')),
        ]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            LetterCategory::query()->create($input);

            return back()->with('success', __('action.created', ['menu' => __('menu.letter_category')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function update(UpdateRequest $request, LetterCategory $category): RedirectResponse
    {
        try {
            $input = $request->validated();
            $category->update($input);

            return back()->with('success', __('action.updated', ['menu' => __('menu.letter_category')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function destroy(LetterCategory $category): RedirectResponse
    {
        try {
            $category->delete();

            return back()->with('success', __('action.deleted', ['menu' => __('menu.letter_category')]));
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

            LetterCategory::query()
                ->whereIn('id', $ids)
                ->delete();

            return back()->with('success', __('action.deleted', ['menu' => __('menu.letter_category')]));;
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }
}
