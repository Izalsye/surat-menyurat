<?php

namespace App\Http\Requests\IncomingLetter;

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
        return Permission::AddIncomingLetter->isAllowed();
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
            'sender' => 'nullable|string|max:255',
            'institution' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'summary' => 'nullable|string',
            'is_draft' => 'nullable|boolean',
            'created_at' => 'nullable|date',
            'created_at' => 'nullable|date',
            'sign_letter'=> 'nullable|boolean',
            'file' => 'required|mimes:pdf,jpg,jpeg,png,webp,bmp,gif,svg|max:10240',
        ];
    }
}
