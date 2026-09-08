<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'net_amount' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'vat_amount' => ['required', 'numeric', 'gte:0', 'decimal:0,2'],
            'due_date' => ['required', 'date'],
        ];
    }
}
