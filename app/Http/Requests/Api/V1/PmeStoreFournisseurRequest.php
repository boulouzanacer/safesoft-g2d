<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PmeStoreFournisseurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom_boutique' => ['required', 'string', 'max:255'],
            'boutique_category_id' => ['required', 'integer', 'exists:boutique_categories,id'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('frs', 'email')->whereNull('deleted_at'),
            ],
            'telephone' => ['required', 'string', 'max:255'],
            'code_wilaya' => ['required', 'integer', 'exists:wilaya,ID_WILAYA'],
            'code_commune' => ['required', 'integer', 'exists:commune,ID_COMMUNE'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $wilaya = (int) $this->input('code_wilaya');
            $commune = (int) $this->input('code_commune');

            if ($wilaya <= 0 || $commune <= 0) {
                return;
            }

            $belongs = \App\Models\Commune::query()
                ->where('ID_COMMUNE', $commune)
                ->where('ID_WILAYA', $wilaya)
                ->exists();

            if (! $belongs) {
                $validator->errors()->add('code_commune', 'La commune ne correspond pas a la wilaya fournie.');
            }
        });
    }
}
