<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Controllers\Controller;
use App\Models\LetterCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LetterCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $categories = LetterCategory::query()->get();

            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => $categories,
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
