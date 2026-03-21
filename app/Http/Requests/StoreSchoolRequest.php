<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSchoolRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'cep' => 'required|string|min:8|max:9',
            'address' => 'required|string|max:255',
            'reference_point' => 'nullable|string|max:255',
            'lat' => 'nullable|numeric|between:-33.7205,5.2718',
            'lng' => 'nullable|numeric|between:-73.9898,-34.7975',
        ];
    }
}
