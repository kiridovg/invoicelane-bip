<?php

namespace App\DataTransferObjects;

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
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            number: (string) $payload['number'],
            supplierName: (string) $payload['supplier_name'],
            supplierTaxId: (string) $payload['supplier_tax_id'],
            netAmount: (string) $payload['net_amount'],
            vatAmount: (string) $payload['vat_amount'],
            currency: (string) $payload['currency'],
            issueDate: CarbonImmutable::parse((string) $payload['issue_date']),
            dueDate: CarbonImmutable::parse((string) $payload['due_date']),
        );
    }
}
