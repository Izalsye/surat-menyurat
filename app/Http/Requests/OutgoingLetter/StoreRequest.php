<?php

namespace App\Http\Requests\OutgoingLetter;

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
        return Permission::AddOutgoingLetter->isAllowed();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'letter_number' => 'nullable|string|max:255',
            'letter_date' => 'nullable|date',
            'recipient' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'summary' => 'nullable|string',
            'is_draft' => 'nullable|boolean',
            'file' => 'required|mimes:pdf,jpg,jpeg,png,webp,bmp,gif,svg|max:10240',
            'disposition_id' => 'nullable|string|max:255',
            'incoming_letter_id' => 'nullable|string|max:255'
        ];
    }
}
