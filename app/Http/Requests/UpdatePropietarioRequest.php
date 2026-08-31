<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePropietarioRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre'         => 'required|string|max:100',
            'apellidos'      => 'required|string|max:100',
            'dni'            => 'nullable|string|min:8|max:11',
            'tipo_persona'   => 'required|string|in:personal_normal,socio',
            'telefono'       => 'nullable|string|max:15',
            'email'          => 'nullable|email|max:150',
            'direccion'      => 'nullable|string|max:255',
            'activo'         => 'nullable|boolean',
            'notas'          => 'nullable|string',
            'monto_inicial'        => 'nullable|numeric|min:0|max:600',
            'fecha_monto_inicial'  => 'nullable|date',
            'cuota_1'              => 'nullable|numeric|min:0|max:600',
            'fecha_cuota_1'        => 'nullable|date',
            'cuota_2'              => 'nullable|numeric|min:0|max:600',
            'fecha_cuota_2'        => 'nullable|date',
            'cuota_3'              => 'nullable|numeric|min:0|max:600',
            'fecha_cuota_3'        => 'nullable|date',
            'vehiculos'            => 'nullable|array',
            'vehiculos.*.monto_inicial'        => 'nullable|numeric|min:0|max:600',
            'vehiculos.*.fecha_monto_inicial'  => 'nullable|date',
            'vehiculos.*.cuota_1'              => 'nullable|numeric|min:0|max:600',
            'vehiculos.*.fecha_cuota_1'        => 'nullable|date',
            'vehiculos.*.cuota_2'              => 'nullable|numeric|min:0|max:600',
            'vehiculos.*.fecha_cuota_2'        => 'nullable|date',
            'vehiculos.*.cuota_3'              => 'nullable|numeric|min:0|max:600',
            'vehiculos.*.fecha_cuota_3'        => 'nullable|date',
        ];
    }
}
