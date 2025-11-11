<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVillageStatisticRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxYear = (int) date('Y') + 1;

        return [
            'statistic_type_id' => ['sometimes', 'required', 'exists:statistic_types,id'],
            'indicator_name' => ['sometimes', 'required', 'string', 'max:255'],
            'value' => ['sometimes', 'required', 'numeric'],
            'unit' => ['sometimes', 'nullable', 'string', 'max:50'],
            'year' => ['sometimes', 'required', 'integer', 'min:2000', 'max:' . $maxYear],
            'period' => ['sometimes', 'nullable', 'string', 'max:50'],
            'source' => ['sometimes', 'nullable', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
