<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
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
        return [
            'driver_id' => [
                'required',
                Rule::exists('drivers', 'id')->where('user_id', $this->user()->id),
            ],
            'plate'     => 'required|string|size:7|unique:vehicles,plate',
            'capacity'  => 'required|integer|min:1',
            'mainRoute' => 'required|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'driver_id.exists' => 'O motorista informado não existe ou não pertence ao usuário autenticado.',
            'plate.required' => 'O número da placa é obrigatório',
            'plate.unique' => 'Esta placa já está cadastrada.',
            'capacity.required' => 'A capacidade do veículo é obrigatória.',
            'mainRoute.required' => 'A rota do veículo é obrigatória',
        ];
    }
}