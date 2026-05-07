<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class PmeSyncClientsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clients' => ['required', 'array', 'min:1'],
            'clients.*.code_client' => ['required', 'string', 'max:50'],
            'clients.*.nom' => ['required', 'string', 'max:255'],
            'clients.*.prenom' => ['required', 'string', 'max:255'],
            'clients.*.email' => ['required', 'email', 'max:255'],
            'clients.*.password' => ['required', 'string'],
            'clients.*.telephone' => ['nullable', 'string', 'max:255'],
            'clients.*.id_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'clients.*.id_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
            'clients.*.tarif' => ['nullable', 'integer', 'in:1,2,3'],
        ];
    }
}
