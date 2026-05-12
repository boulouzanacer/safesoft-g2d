<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'telephone' => ['required', 'string', 'max:255'],
            'type_client' => ['nullable', 'in:simple'],
            'adresse' => ['nullable', 'string'],
            'id_wilaya' => ['nullable', 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => ['nullable', 'integer', 'exists:commune,ID_COMMUNE'],
        ];
    }
}
