<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRouteRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'driver_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('drivers', 'id')->where(fn ($query) => $query->where('user_id', $this->user()->id)),
            ],
            'school_id' => 'sometimes|nullable|integer|exists:schools,id',
            'start_point' => 'sometimes|required|string|max:255',
            'start_point_cep' => 'sometimes|nullable|string|min:8|max:9',
            'start_point_reference' => 'sometimes|nullable|string|max:255',
            'start_point_lat' => 'sometimes|nullable|numeric|between:-33.7205,5.2718',
            'start_point_lng' => 'sometimes|nullable|numeric|between:-73.9898,-34.7975',
            'end_point' => 'sometimes|required|string|max:255',
            'end_point_lat' => 'sometimes|nullable|numeric|between:-33.7205,5.2718',
            'end_point_lng' => 'sometimes|nullable|numeric|between:-73.9898,-34.7975',
            'departure_time' => 'sometimes|required|string|max:10',
        ];
    }
}
