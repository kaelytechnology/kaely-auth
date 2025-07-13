<?php

namespace Kaely\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:main_roles'],
            'description' => ['nullable', 'string'],
            'role_category_id' => ['nullable', 'exists:main_role_categories,id'],
            'is_active' => ['boolean'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['exists:main_permissions,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del rol es obligatorio.',
            'slug.required' => 'El slug del rol es obligatorio.',
            'slug.unique' => 'El slug del rol ya existe.',
            'role_category_id.exists' => 'La categoría de rol seleccionada no existe.',
            'permission_ids.*.exists' => 'Uno o más permisos seleccionados no existen.',
        ];
    }
} 