<?php

namespace App\Http\Resources\Project;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'name'                      => $this->name,
            'description'               => $this->description,
            'phone_number'              => $this->phone_number,
            'assistant_description'     => $this->assistant_description,
            'assistant_goal'            => $this->assistant_goal,
            'assistant_knowledge_about' => $this->assistant_knowledge_about,
            'target_public'             => $this->target_public,
            'language'                  => $this->language,
            'default_answer'            => $this->default_answer,
            'has_system_prompt'         => (boolean)$this->has_system_prompt,
            'active'                    => (boolean)$this->active,
            'created_at'                => Carbon::parse($this->created_at)->toDateString()
        ];
    }
}
