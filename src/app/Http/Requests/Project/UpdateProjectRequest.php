<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name'                      => 'nullable|string|max:255',
            'description'               => 'nullable|string|max:500',
            'assistant_description'     => 'nullable|string|min:20',
            'assistant_goal'            => 'nullable|string|min:20',
            'assistant_knowledge_about' => 'nullable|string|min:20',
            'target_public'             => 'nullable|string|min:20',
            'language'                  => 'nullable|string|max:20',
            'default_answer'            => 'nullable|string|min:20'
        ];
    }
}
