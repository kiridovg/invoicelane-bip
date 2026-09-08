<?php

namespace App\DataTransferObjects;

use App\Enums\InvoiceStatus;

final readonly class InvoiceListQuery
{
    public const DEFAULT_PER_PAGE = 20;

    public function __construct(
        public ?InvoiceStatus $status,
        public int $perPage,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            status:  isset($payload['status']) ? InvoiceStatus::from((string) $payload['status']) : null,
            perPage: (int) ($payload['per_page'] ?? self::DEFAULT_PER_PAGE),
        );
    }
}
