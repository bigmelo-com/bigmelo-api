<?php

namespace App\Http\Resources\AdminDashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyTotalsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'new_leads'             => $this['total_new_leads'] ?? 0,
            'new_users'             => $this['total_new_users'] ?? 0,
            'new_messages'          => $this['total_new_messages'] ?? 0,
            'new_whatsapp_messages' => $this['total_new_whatsapp_messages'] ?? 0,
            'new_audio_messages'    => $this['total_new_audio_messages'] ?? 0,
            'daily_chats'           => $this['total_daily_chats'] ?? 0,
        ];
    }
}
