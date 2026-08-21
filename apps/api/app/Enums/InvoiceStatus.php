<?php

declare(strict_types=1);

namespace App\Enums;

enum InvoiceStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function isEditable(): bool
    {
        return $this === self::Pending;
    }
}
