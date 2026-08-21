<?php

declare(strict_types=1);

namespace App\DataTransferObjects;

use App\Http\Requests\UpdateInvoiceRequest;
use Carbon\CarbonImmutable;

final readonly class UpdateInvoiceData
{
    public function __construct(
        public string $netAmount,
        public string $vatAmount,
        public CarbonImmutable $dueDate,
    ) {
    }

    public static function fromRequest(UpdateInvoiceRequest $request): self
    {
        return new self(
            netAmount: (string) $request->string('net_amount'),
            vatAmount: (string) $request->string('vat_amount'),
            dueDate:   CarbonImmutable::parse($request->date('due_date')),
        );
    }
}
