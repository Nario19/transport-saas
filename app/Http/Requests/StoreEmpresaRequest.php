<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmpresaRequest extends FormRequest
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
            'nombre'                => 'required|string|max:120',
            'ruc'                   => 'required|string|size:11|unique:empresas,ruc',
            'razon_social'          => 'nullable|string|max:150',
            'telefono'              => 'nullable|string|max:20',
            'direccion'             => 'nullable|string|max:255',
            'plan'                  => 'required|string|in:basico,pro,enterprise',
            'tributo_diario'        => 'required|numeric|min:0',
            'logo'                  => 'nullable|image|max:2048',
            'admin_name'            => 'required|string|max:100',
            'admin_email'           => 'required|email|max:150|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
        ];
    }
}
