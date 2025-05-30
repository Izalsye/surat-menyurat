<?php

namespace App\Http\Requests\Disposition;

use App\Enum\Permission;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Permission::AddDisposition->isAllowed();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'incoming_letter_id' => 'required|string|max:255',
            'assignee_id' => 'nullable',
            'assigner_id' => 'required',
            'description' => 'nullable|string',
            'is_done' => 'boolean',
            'reply_letter' => 'boolean',
            'due_at' => 'nullable|date',
            'parent_id' => 'nullable|string|max:255',
            'urgency' => 'nullable|string|max:255'
        ];
    }
}
