<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'active',
        'message_limit'
    ];

    /**
     * Organization linked to the project
     *
     * @return BelongsTo
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }

    /**
     * Leads related to the user
     *
     * @return BelongsToMany
     */
    public function leads(): BelongsToMany
    {
        return $this->belongsToMany(Lead::class);
    }

    /**
     * Project contents
     *
     * @return HasMany
     */
    public function contents(): HasMany
    {
        return $this->hasMany(ProjectContent::class, 'project_id', 'id');
    }

    /**
     * The current active content
     *
     * @return ProjectContent|null
     */
    public function currentContent(): ?ProjectContent
    {
        return $this->contents()->where('active', true)->first();
    }
}
