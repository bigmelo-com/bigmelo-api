<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDashboard\DailyTotalsResource;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AdminDashboardController extends Controller
{
    public function getDailyTotals(): JsonResponse
    {
        try {
            $today = Carbon::today();

            $new_leads = Lead::whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->get();

            $result = [
                'total_new_leads' => $new_leads->count()
            ];

            return (new DailyTotalsResource($result))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }
}
