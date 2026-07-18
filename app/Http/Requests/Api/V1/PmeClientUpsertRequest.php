<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class PmeClientUpsertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes'];

        return [
            'code_client' => ['nullable', 'string', 'max:50'],
            'nom' => ['required', 'string', 'max:255'],
            'email' => [...$required, 'email', 'max:255'],
            'password' => $this->isMethod('post')
                ? ['required', 'string', 'min:8']
                : ['nullable', 'string', 'min:8'],
            'telephone' => ['nullable', 'string', 'max:255'],
            'adresse' => ['nullable', 'string'],
            'id_wilaya' => [...$required, 'integer', 'exists:wilaya,ID_WILAYA'],
            'id_commune' => [...$required, 'integer', 'exists:commune,ID_COMMUNE'],
            'type_client' => ['nullable', 'in:simple,abonne'],
            'tarif' => ['nullable', 'integer', 'in:1,2,3'],
            'synced_pme' => ['nullable', 'integer', 'in:0,1'],
            'actif' => ['nullable', 'integer', 'in:0,1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $frs = $this->attributes->get('fournisseur');
            if (! $frs) {
                return;
            }

            $clientId = (int) $this->route('id');
            $email = trim((string) $this->input('email', ''));
            $codeClient = trim((string) $this->input('code_client', ''));

            if ($email !== '') {
                $emailExists = Client::withTrashed()
                    ->where('id_frs', $frs->id)
                    ->where('email', $email)
                    ->when($clientId > 0, fn ($q) => $q->where('id', '!=', $clientId))
                    ->exists();

                if ($emailExists) {
                    $validator->errors()->add('email', 'Cet email existe deja pour ce fournisseur.');
                }
            }

            if ($codeClient !== '') {
                $codeExists = Client::withTrashed()
                    ->where('id_frs', $frs->id)
                    ->where('code_client', $codeClient)
                    ->when($clientId > 0, fn ($q) => $q->where('id', '!=', $clientId))
                    ->exists();

                if ($codeExists) {
                    $validator->errors()->add('code_client', 'Ce code client existe deja pour ce fournisseur.');
                }
            }
        });
    }
}
