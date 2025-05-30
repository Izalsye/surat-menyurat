<?php

namespace App\Http\Requests\LetterCategory;

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
        return Permission::AddLetterCategory->isAllowed();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:255|unique:letter_categories,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
