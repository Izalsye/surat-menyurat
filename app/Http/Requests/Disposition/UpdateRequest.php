<?php

namespace App\Http\Requests\Disposition;

use App\Enum\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Permission::EditDisposition->isAllowed() || auth()->user()->id === $this->assigner_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'assignee_id' => 'nullable',
            'description' => 'nullable|string',
            'is_done' => 'boolean',
            'reply_letter' => 'boolean',
            'due_at' => 'nullable|date',
            'urgency' => 'nullable|string|max:255'
        ];
    }
}
