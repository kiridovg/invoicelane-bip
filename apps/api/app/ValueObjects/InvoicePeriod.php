<?php

namespace App\ValueObjects;

use App\Exceptions\DueDateBeforeIssueDateException;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

final readonly class InvoicePeriod
{
    private function __construct(
        public CarbonImmutable $issueDate,
        public CarbonImmutable $dueDate,
    ) {}

    /** @throws DueDateBeforeIssueDateException */
    public static function of(CarbonInterface $issueDate, CarbonInterface $dueDate): self
    {
        $issue = CarbonImmutable::instance($issueDate)->startOfDay();
        $due = CarbonImmutable::instance($dueDate)->startOfDay();

        if ($due->lt($issue)) {
            throw new DueDateBeforeIssueDateException($issue, $due);
        }

        return new self($issue, $due);
    }
}
