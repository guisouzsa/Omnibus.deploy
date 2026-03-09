<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBusRequest extends FormRequest
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
        $busId = $this->route('buses');

        return [
            'driver_id' => 'sometimes|exists:drivers,id',
            'plate' => [
                'sometimes',
                'required',
                'string',
                'size:7',
                Rule::unique('buses', 'plate')->ignore($busId),
            ],
            'capacity'  => 'sometimes|integer|min:1',
            'mainRoute' => 'sometimes|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'plate.required' => 'O número da placa é obrigatório',
            'plate.unique' => 'Esta placa já está cadastrada.',
            'capacity.required' => 'A capacidade do veículo é obrigatória.',
            'mainRoute.required' => 'A rota do veículo é obrigatória',
        ];
    }
}
