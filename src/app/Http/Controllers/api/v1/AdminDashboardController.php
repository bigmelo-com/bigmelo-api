<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminDashboard\DailyTotalsResource;
use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminDashboardController extends Controller
{
    /**
     * Get the daily totals for admin dashboard
     *
     * @OA\Get(
     *      path="/v1/admin-dashboard/daily-totals?date={date}",
     *      tags={"Dashboard"},
     *      security={{"bearerAuth":{}}},
     *      summary="Get daily totals",
     *      description="Retrieves the daily totals for new leads, new users, and new messages.",
     *      operationId="getDailyTotals",
     *      @OA\Parameter(
     *          description="Date for the daily report, default date is today.",
     *          in="path",
     *          name="date",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Daily totals retrieved successfully",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized"
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error"
     *      )
     *  )
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
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
            $new_audio_messages = WhatsappMessage::whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->where('media_content_type', 'audio/ogg')->get();
            $daily_chats = Message::select('lead_id')->whereBetween('created_at', [$today->startOfDay(), $today->copy()->endOfDay()])->distinct()->get();

            $result = [
                'total_new_leads'               => $new_leads->count(),
                'total_new_users'               => $new_users->count(),
                'total_new_messages'            => $new_messages->count(),
                'total_new_whatsapp_messages'   => $new_whatsapp_messages->count(),
                'total_new_audio_messages'      => $new_audio_messages->count(),
                'total_daily_chats'             => $daily_chats->count(),
            ];

            return (new DailyTotalsResource($result))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }
}
