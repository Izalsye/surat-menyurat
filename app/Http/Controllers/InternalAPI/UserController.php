<?php

namespace App\Http\Controllers\InternalAPI;

use App\Enum\Permission;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function dispositionAssignee(Request $request): JsonResponse
    {
        try {
            $users = User::query()->whereHas('roles.permissions', function ($query) {
                    $query->where('name', Permission::ViewDisposition);
                })->get();

            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => $users,
            ]);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
