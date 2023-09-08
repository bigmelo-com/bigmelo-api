<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message_id',
        'media_content_type',
        'sms_message_sid',
        'num_media',
        'profile_name',
        'sms_sid',
        'wa_id',
        'sms_status',
        'to',
        'num_segments',
        'referral_num_media',
        'message_sid',
        'account_sid',
        'from',
        'media_url',
        'api_version'
    ];

    /**
     * Message related to this whatsapp message
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }
}
