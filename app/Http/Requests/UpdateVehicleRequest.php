<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
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
        $vehicleId = $this->route('vehicle');

        return [
            'driver_id' => [
                'sometimes',
                Rule::exists('drivers', 'id')->where('user_id', $this->user()->id),
            ],
            'plate' => [
                'sometimes',
                'required',
                'string',
                'size:7',
                Rule::unique('vehicles', 'plate')->ignore($vehicleId),
            ],
            'capacity'  => 'sometimes|integer|min:1',
            'mainRoute' => 'sometimes|string|max:255',
            'route_id'  => [
                'sometimes',
                'nullable',
                Rule::exists('routes', 'id')->where('user_id', $this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'plate.required' => 'O número da placa é obrigatório',
            'plate.unique' => 'Esta placa já está cadastrada.',
            'capacity.required' => 'A capacidade do veículo é obrigatória.',
            'mainRoute.required' => 'A rota do veículo é obrigatória',
            'route_id.exists' => 'A rota informada não existe ou não pertence ao usuário autenticado.',
        ];
    }
}