<?php

declare(strict_types=1);

namespace App\Services;

use App\DataTransferObjects\CreateInvoiceData;
use App\DataTransferObjects\UpdateInvoiceData;
use App\Enums\InvoiceStatus;
use App\Exceptions\InvoiceNotEditableException;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class InvoiceService
{
    public function __construct(private readonly InvoiceRepository $invoices)
    {
    }

    /** @return LengthAwarePaginator<int, Invoice> */
    public function list(?InvoiceStatus $status, int $perPage): LengthAwarePaginator
    {
        return $this->invoices->paginateLatest($status, $perPage);
    }

    public function calculateGross(string $net, string $vat): string
    {
        return bcadd($net, $vat, 2);
    }

    public function create(CreateInvoiceData $data): Invoice
    {
        $invoice = new Invoice([
            'number'          => $data->number,
            'supplier_name'   => $data->supplierName,
            'supplier_tax_id' => $data->supplierTaxId,
            'net_amount'      => $data->netAmount,
            'vat_amount'      => $data->vatAmount,
            'gross_amount'    => $this->calculateGross($data->netAmount, $data->vatAmount),
            'currency'        => $data->currency,
            'status'          => InvoiceStatus::Pending,
            'issue_date'      => $data->issueDate,
            'due_date'        => $data->dueDate,
        ]);

        return $this->invoices->persist($invoice);
    }

    /** @throws InvoiceNotEditableException */
    public function update(Invoice $invoice, UpdateInvoiceData $data): Invoice
    {
        if (! $invoice->isEditable()) {
            throw new InvoiceNotEditableException($invoice->status);
        }

        $invoice->fill([
            'net_amount'   => $data->netAmount,
            'vat_amount'   => $data->vatAmount,
            'gross_amount' => $this->calculateGross($data->netAmount, $data->vatAmount),
            'due_date'     => $data->dueDate,
        ]);

        return $this->invoices->persist($invoice);
    }
}
