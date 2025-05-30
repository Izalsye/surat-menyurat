<?php

namespace App\Http\Controllers\InternalAPI;

use App\Http\Controllers\Controller;
use App\Models\Disposition;
use App\Models\IncomingLetter;
use App\Models\OutgoingLetter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function statistic(Request $request): JsonResponse
    {
        try {
            $now = now();
            $month = $request->get('month', $now->month);
            $year = $request->get('year', $now->year);

            $startDate = Carbon::create($year, $month, 1);
            $endDate = $startDate->copy()->endOfMonth();
            $dates = collect(CarbonPeriod::create($startDate, $endDate))->map->toDateString();

            $incomingLetters = IncomingLetter::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->groupByRaw('DATE(created_at)')
                ->pluck('total', 'date');

            $outgoingLetters = OutgoingLetter::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->groupByRaw('DATE(created_at)')
                ->pluck('total', 'date');

            $dispositions = Disposition::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->groupByRaw('DATE(created_at)')
                ->pluck('total', 'date');

            $data = $dates->mapWithKeys(function ($date) use ($incomingLetters, $outgoingLetters, $dispositions) {
                return [
                    $date => [
                        'incoming' => $incomingLetters[$date] ?? 0,
                        'outgoing' => $outgoingLetters[$date] ?? 0,
                        'disposition' => $dispositions[$date] ?? 0,
                    ],
                ];
            });

            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => $data,
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

    public function averageProcessTime(Request $request): JsonResponse
    {
        try {
            $now = now();
            $month = $request->get('month', $now->month);
            $year = $request->get('year', $now->year);

            $summaries = [
                [
                    'title' => 'average_response_time',
                    'minutes' => (int) IncomingLetter::query()
                        ->join(DB::raw('(SELECT incoming_letter_id, MIN(created_at) as first_disposition_at FROM dispositions GROUP BY incoming_letter_id) as first_dispositions'),
                            'first_dispositions.incoming_letter_id', '=', 'incoming_letters.id')
                        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, incoming_letters.created_at, first_dispositions.first_disposition_at)) as value')
                        ->whereMonth('incoming_letters.created_at', $month)
                        ->whereYear('incoming_letters.created_at', $year)
                        ->value('value') ?? 0,
                    'hours' => 0,
                    'days' => 0,
                ],
                [
                    'title' => 'average_disposition_time',
                    'minutes' => (int) Disposition::query()
                        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, done_at)) as value')
                        ->whereNotNull('done_at')
                        ->whereMonth('created_at', $month)
                        ->whereYear('created_at', $year)
                        ->value('value') ?? 0,
                    'hours' => 0,
                    'days' => 0,
                ],
                [
                    'title' => 'average_turnaround_time',
                    'minutes' => (int) OutgoingLetter::query()
                        ->join('dispositions', 'dispositions.id', '=', 'outgoing_letters.disposition_id')
                        ->join('incoming_letters', 'incoming_letters.id', '=', 'dispositions.incoming_letter_id')
                        ->whereNotNull('incoming_letters.created_at')
                        ->whereNotNull('outgoing_letters.created_at')
                        ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, incoming_letters.created_at, outgoing_letters.created_at)) as value')
                        ->whereMonth('outgoing_letters.created_at', $month)
                        ->whereYear('outgoing_letters.created_at', $year)
                        ->value('value') ?? 0,
                    'hours' => 0,
                    'days' => 0,
                ]
            ];

            $summaries = collect($summaries)->map(function ($summary) {
                $summary['minutes'] = $summary['minutes'] ?? 0;
                $summary['hours'] = floor($summary['minutes'] / 60);
                $summary['days'] = floor($summary['minutes'] / 1440);
                return $summary;
            });

            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => $summaries,
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

    public function dispositionPunctuality(Request $request): JsonResponse
    {
        try {
            $now = now();
            $month = $request->get('month', $now->month);
            $year = $request->get('year', $now->year);

            $total = Disposition::query()
                ->whereMonth('due_at', $month)
                ->whereYear('due_at', $year)
                ->count();
            $onTime = Disposition::query()
                ->whereMonth('due_at', $month)
                ->whereYear('due_at', $year)
                ->whereColumn('done_at', '<=', 'due_at')
                ->count();

            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => [
                    'total' => $total,
                    'onTime' => $onTime,
                    'percentage' => $total > 0 ? round($onTime / $total * 100, 0) : 0,
                ],
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

    public function dispositionConversionRate(Request $request): JsonResponse
    {
        try {
            $now = now();
            $month = $request->get('month', $now->month);
            $year = $request->get('year', $now->year);

            $total = Disposition::query()
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->count();

            $converted = Disposition::query()
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->whereHas('replies')
                ->count();

            return response()->json([
                'error' => false,
                'message' => 'Success',
                'data' => [
                    'total' => $total,
                    'converted' => $converted,
                    'percentage' => $total > 0 ? round($converted / $total * 100, 0) : 0,
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::error($exception->getMessage());

            return response()->json([
                'error' => true,
                'message' => $exception->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
