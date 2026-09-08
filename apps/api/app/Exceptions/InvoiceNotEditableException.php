<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\InvoiceStatus;
use Exception;

class InvoiceNotEditableException extends Exception
{
    public function __construct(public readonly InvoiceStatus $status)
    {
        parent::__construct(
            sprintf('Invoice in status "%s" can no longer be modified.', $status->value)
        );
    }
}
