<?php

namespace App\Models;

use App\Classes\ChatGPT\ChatGPTEmbedding;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Pgvector\Laravel\Vector;

class ProjectEmbedding extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'project_id',
        'project_content_id',
        'text',
        'embedding'
    ];

    protected $casts = [
        'embedding' => Vector::class
    ];

    /**
     * Store extra embedding data
     *
     * @param ChatGPTEmbedding $embedding
     *
     * @return void
     */
    public function storeOpenAIEmbeddingData(ChatGPTEmbedding $embedding): void
    {
        OpenaiTokensEmbedding::create([
            'project_embedding_id' => $this->id,
            'prompt_tokens'        => $embedding->getPromptTokens(),
            'total_tokens'         => $embedding->getTotalTokens()
        ]);
    }
}
