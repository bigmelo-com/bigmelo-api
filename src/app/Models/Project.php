<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'description',
        'phone_number',
        'assistant_description',
        'assistant_goal',
        'assistant_knowledge_about',
        'target_public',
        'language',
        'default_answer',
        'has_system_prompt',
        'active'
    ];

    /**
     * Organization linked to the project
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    /**
     * Leads related to the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class);
    }
}
