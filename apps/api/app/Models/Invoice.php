<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'number', 'supplier_name', 'supplier_tax_id',
        'net_amount', 'vat_amount', 'gross_amount',
        'currency', 'status', 'issue_date', 'due_date',
    ];

    protected function casts(): array
    {
        return [
            'net_amount'   => 'decimal:2',
            'vat_amount'   => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'issue_date'   => 'date',
            'due_date'     => 'date',
            'status'       => InvoiceStatus::class,
        ];
    }

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }
}
