<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'due_date'   => ['required', 'date'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                /** @var Invoice $invoice */
                $invoice = $this->route('invoice');

                if ($this->date('due_date')->lt($invoice->issue_date)) {
                    $validator->errors()->add(
                        'due_date',
                        'The due date must be a date after or equal to '
                        . $invoice->issue_date->toDateString() . '.'
                    );
                }
            },
        ];
    }
}
