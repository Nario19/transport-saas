<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmpresaRequest extends FormRequest
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
        $empresaId = $this->route('empresa')?->id ?? $this->route('empresa');

        return [
            'nombre'         => 'required|string|max:120',
            'ruc'            => 'required|string|size:11|unique:empresas,ruc,' . $empresaId,
            'razon_social'   => 'nullable|string|max:150',
            'telefono'       => 'nullable|string|max:20',
            'direccion'      => 'nullable|string|max:255',
            'plan'           => 'required|string|in:basico,pro,enterprise',
            'tributo_diario' => 'required|numeric|min:0',
            'activa'         => 'nullable|boolean',
            'logo'           => 'nullable|image|max:2048',
            'admin_name'     => 'nullable|string|max:100',
            'admin_email'    => 'nullable|email|max:150',
            'admin_password' => 'nullable|string|min:6|confirmed',
        ];
    }
}
