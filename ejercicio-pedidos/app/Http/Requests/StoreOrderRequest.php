<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Si es update (PUT/PATCH), solo validamos status
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        if ($isUpdate) {
            return [
                'status' => ['required', Rule::in(['pending', 'paid', 'shipped', 'delivered'])],
            ];
        }

        // Si es create (POST), validamos todo
        return [
            'status' => ['required', Rule::in(['pending', 'paid', 'shipped', 'delivered'])],
            'client_id' => ['required', 'exists:clients,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}