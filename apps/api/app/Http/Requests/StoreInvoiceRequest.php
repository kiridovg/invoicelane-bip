<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number'          => ['required', 'string', 'max:64', 'unique:invoices,number'],
            'supplier_name'   => ['required', 'string', 'max:255'],
            'supplier_tax_id' => ['required', 'string', 'max:32'],

            'net_amount'      => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'vat_amount'      => ['required', 'numeric', 'gte:0', 'decimal:0,2'],
            'currency'        => ['required', 'string', 'size:3'],

            'issue_date'      => ['required', 'date'],
            'due_date'        => ['required', 'date', 'after_or_equal:issue_date'],
        ];
    }
}
