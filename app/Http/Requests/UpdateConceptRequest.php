<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConceptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'explanation' => ['required', 'string', 'min:20'],
            'difficulty'  => ['required', 'in:junior,mid,senior'],
            'status'      => ['required', 'in:to_review,in_progress,mastered'],
        ];
    }
}
