<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Http\Requests\StoreInvoiceRequest;
use Carbon\CarbonImmutable;

final readonly class CreateInvoiceData
{
    public function __construct(
        public string $number,
        public string $supplierName,
        public string $supplierTaxId,
        public string $netAmount,
        public string $vatAmount,
        public string $currency,
        public CarbonImmutable $issueDate,
        public CarbonImmutable $dueDate,
    ) {
    }

    public static function fromRequest(StoreInvoiceRequest $request): self
    {
        return new self(
            number:        (string) $request->string('number'),
            supplierName:  (string) $request->string('supplier_name'),
            supplierTaxId: (string) $request->string('supplier_tax_id'),
            netAmount:     (string) $request->string('net_amount'),
            vatAmount:     (string) $request->string('vat_amount'),
            currency:      (string) $request->string('currency'),
            issueDate:     CarbonImmutable::parse($request->date('issue_date')),
            dueDate:       CarbonImmutable::parse($request->date('due_date')),
        );
    }
}
