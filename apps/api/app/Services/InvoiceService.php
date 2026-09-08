<?php

namespace App\Services;

use App\DataTransferObjects\CreateInvoiceData;
use App\DataTransferObjects\InvoiceListQuery;
use App\DataTransferObjects\UpdateInvoiceData;
use App\Enums\InvoiceStatus;
use App\Exceptions\DueDateBeforeIssueDateException;
use App\Exceptions\InvoiceNotEditableException;
use App\Models\Invoice;
use App\ValueObjects\InvoicePeriod;
use App\ValueObjects\Money;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InvoiceService
{
    /** @return LengthAwarePaginator<int, Invoice> */
    public function list(InvoiceListQuery $query): LengthAwarePaginator
    {
        return Invoice::query()
            ->when($query->status, fn ($builder) => $builder->where('status', $query->status))
            ->orderByDesc('created_at')
            ->paginate($query->perPage)
            ->withQueryString();
    }

    /** @throws DueDateBeforeIssueDateException */
    public function create(CreateInvoiceData $data): Invoice
    {
        $net = Money::of($data->netAmount, $data->currency);
        $vat = Money::of($data->vatAmount, $data->currency);
        $period = InvoicePeriod::of($data->issueDate, $data->dueDate);

        $invoice = new Invoice([
            'number' => $data->number,
            'supplier_name' => $data->supplierName,
            'supplier_tax_id' => $data->supplierTaxId,
            'net_amount' => $net->amount,
            'vat_amount' => $vat->amount,
            'gross_amount' => $net->add($vat)->amount,
            'currency' => $net->currency,
            'status' => InvoiceStatus::Pending,
            'issue_date' => $period->issueDate,
            'due_date' => $period->dueDate,
        ]);

        $invoice->save();

        return $invoice;
    }

    /** @throws InvoiceNotEditableException|DueDateBeforeIssueDateException */
    public function update(Invoice $invoice, UpdateInvoiceData $data): Invoice
    {
        if (! $invoice->isEditable()) {
            throw new InvoiceNotEditableException($invoice->status);
        }

        $net = Money::of($data->netAmount, $invoice->currency);
        $vat = Money::of($data->vatAmount, $invoice->currency);
        $period = InvoicePeriod::of($invoice->issue_date, $data->dueDate);

        $invoice->fill([
            'net_amount' => $net->amount,
            'vat_amount' => $vat->amount,
            'gross_amount' => $net->add($vat)->amount,
            'due_date' => $period->dueDate,
        ]);

        $invoice->save();

        return $invoice;
    }
}
