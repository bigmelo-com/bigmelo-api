<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectContent extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'content',
        'total_embeddings',
        'embedding',
        'status',
        'error',
        'active'
    ];

    /**
     * Embeddings related to the project content
     *
     * @return HasMany
     */
    public function embeddings(): HasMany
    {
        return $this->hasMany(ProjectEmbedding::class, 'project_content_id', 'id');
    }

    /**
     * Mark content as completed
     *
     * The last completed is the new active content.
     *
     * @return void
     */
    public function markAsCompleted(): void
    {
        self::query()->update(['active' => false]);

        $this->refresh();
        $this->status = 'completed';
        $this->active = true;
        $this->save();
    }

    /**
     * Mark content as error
     *
     * If getting embeddings fails, the content is marked as error.
     *
     * @return void
     */
    public function markAsError(): void
    {
        $this->refresh();
        $this->status = 'error';
        $this->save();
    }
}
