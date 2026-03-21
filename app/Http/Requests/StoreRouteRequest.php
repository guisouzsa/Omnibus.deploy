<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRouteRequest extends FormRequest
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
            'start_point' => 'required|string|max:255',
            'start_point_cep' => 'nullable|string|min:8|max:9',
            'start_point_reference' => 'nullable|string|max:255',
            'start_point_lat' => 'nullable|numeric|between:-33.7205,5.2718',
            'start_point_lng' => 'nullable|numeric|between:-73.9898,-34.7975',
            'end_point' => 'required|string|max:255',
            'end_point_lat' => 'nullable|numeric|between:-33.7205,5.2718',
            'end_point_lng' => 'nullable|numeric|between:-73.9898,-34.7975',
            'departure_time' => 'required|string|max:10',
        ];
    }
}
