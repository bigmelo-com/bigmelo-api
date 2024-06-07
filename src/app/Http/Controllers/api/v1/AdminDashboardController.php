<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDashboard\DailyTotalsResource;
use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminDashboardController extends Controller
{
    public function getDailyTotals(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'nullable|date_format:Y-m-d',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Wrong date format. It have to be YYYY-MM-DD.'
                ], 400);
            }

            $today = $request->input('date') ? Carbon::parse($request->input('date')) : Carbon::today();

            $new_leads = Lead::whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->get();
            $new_users = User::whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->get();
            $new_messages = Message::whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->get();
            $new_whatsapp_messages = Message::whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->where('source', 'WhatsApp')->get();

            $result = [
                'total_new_leads'               => $new_leads->count(),
                'total_new_users'               => $new_users->count(),
                'total_new_messages'            => $new_messages->count(),
                'total_new_whatsapp_messages'   => $new_whatsapp_messages->count(),
            ];

            return (new DailyTotalsResource($result))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }
}
