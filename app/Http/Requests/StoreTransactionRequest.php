<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'compte_source_id' => ['required', 'uuid', 'exists:comptes,id'],
            'compte_destination_id' => ['nullable', 'uuid', 'exists:comptes,id'],
            'type' => ['required', 'in:depot,retrait,transfert,scan'],
            'montant' => ['required', 'numeric', 'min:0.01'],
            'status' => ['nullable', 'in:pending,success,failed,cancelled'],
            'mode' => ['nullable', 'in:ussd,qr,mobile_app,api'],
        ];
    }

    public function messages(): array
    {
        return [
            'compte_source_id.required' => 'Le compte source est requis.',
            'compte_source_id.exists' => 'Le compte source n\'existe pas.',
            'compte_destination_id.exists' => 'Le compte destination n\'existe pas.',
            'type.in' => 'Le type de transaction est invalide.',
            'montant.min' => 'Le montant doit être supérieur à 0.',
            'mode.in' => 'Le mode de transaction est invalide.',
        ];
    }
}
