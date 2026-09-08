<?php

declare(strict_types=1);

namespace App\Exceptions;

use Carbon\CarbonImmutable;
use Exception;

class DueDateBeforeIssueDateException extends Exception
{
    public function __construct(
        public readonly CarbonImmutable $issueDate,
        public readonly CarbonImmutable $dueDate,
    ) {
        parent::__construct(
            sprintf('The due date must be a date after or equal to %s.', $issueDate->toDateString())
        );
    }
}
