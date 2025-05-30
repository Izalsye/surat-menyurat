<?php

namespace App\Http\Controllers;

use App\Enum\Permission;
use App\Http\Requests\Role\StoreRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        return Inertia::render('role/Index', [
            'items' => Role::with(['permissions'])
                ->filter($request->query('filter'))
                ->render($request->query('size')),
            'modules' => Permission::module(),
            'actions' => Permission::action(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            $input = $request->validated();
            $role = Role::query()->create($request->only('name'));
            $role->givePermissionTo($input['permissions']);

            return back()->with('success', __('action.created', ['menu' => __('menu.role')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Role $role): RedirectResponse
    {
        try {
            $input = $request->validated();
            $role->update($request->only(['name']));
            $role->syncPermissions($input['permissions']);

            return back()->with('success', __('action.updated', ['menu' => __('menu.role')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        try {
            $role->delete();

            return back()->with('success', __('action.updated', ['menu' => __('menu.role')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }

    public function massDestroy(Request $request): RedirectResponse
    {
        try {
            $ids = $request->input('ids');
            if (empty($ids)) {
                throw new \Exception('Empty ids');
            }

            Role::query()
                ->whereIn('id', $ids)
                ->delete();

            return back()->with('success', __('action.updated', ['menu' => __('menu.role')]));
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return back()->with('error', $exception->getMessage());
        }
    }
}
