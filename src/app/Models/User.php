<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role',
        'name',
        'last_name',
        'email',
        'country_code',
        'phone_number',
        'full_phone_number',
        'password',
        'active',
        'validation_code',
        'validation_code_sent_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Return abilities for the specific role
     *
     * @return array
     */
    public function getRoleAbilities(): array
    {
        return Config::get('roles.' . $this->role . '.abilities') ?? [];
    }

    /**
     * User's lead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class, 'user_id', 'id');
    }

    /**
     * Organizations where the current user is the owner
     *
     * @return HasMany
     */
    public function own_organizations(): HasMany
    {
        return $this->hasMany(Organization::class, 'owner_id', 'id');
    }

    /**
     * Organizations related to the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class);
    }

    /**
     * Messages limits related to the specific user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function message_limits(): HasMany
    {
        return $this->hasMany(UserMessageLimit::class, 'user_id');
    }

    /**
     * The current active messages limit
     *
     * @return \Illuminate\Database\Eloquent\Model|HasMany|object|null
     */
    public function currentMessagesLimit()
    {
        return $this->message_limits()->where('status', 'active')->first();
    }

    /**
     * Decrease the number of available messages according to the current limit
     *
     * @param int $messages_number
     *
     * @return void
     */
    public function decreaseAvailableMessages(int $messages_number = 1): void
    {
        $limit = $this->currentMessagesLimit();
        $available_messages = $limit->available - $messages_number;

        $limit->available = $available_messages;
        $limit->status = $available_messages > 0 ? 'active' : 'inactive';

        $limit->save();
    }

    /**
     * Check if the user has available messages
     *
     * @param int $messages_number
     *
     * @return bool
     */
    public function hasAvailableMessages(int $messages_number = 1): bool
    {
        if (!$this->currentMessagesLimit()) {
            return false;
        }

        return $this->currentMessagesLimit()->available >= $messages_number;
    }
}
