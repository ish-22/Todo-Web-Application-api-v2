<?php

namespace App\Http\Requests\Todo;

use Illuminate\Foundation\Http\FormRequest;

class IndexTodoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => is_string($this->search) ? trim($this->search) : $this->search,
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
            'status' => ['sometimes', 'nullable', 'in:all,pending,completed'],
            'priority' => ['sometimes', 'nullable', 'in:Low,Medium,High'],
        ];
    }
}
