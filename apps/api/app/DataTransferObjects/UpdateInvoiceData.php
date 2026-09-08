<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use Carbon\CarbonImmutable;

final readonly class UpdateInvoiceData
{
    public function __construct(
        public string $netAmount,
        public string $vatAmount,
        public CarbonImmutable $dueDate,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            netAmount: (string) $payload['net_amount'],
            vatAmount: (string) $payload['vat_amount'],
            dueDate:   CarbonImmutable::parse((string) $payload['due_date']),
        );
    }
}
