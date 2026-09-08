<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InvoiceRepository
{
    /**
     * @return LengthAwarePaginator<int, Invoice>
     */
    public function paginateLatest(?InvoiceStatus $status, int $perPage): LengthAwarePaginator;

    public function persist(Invoice $invoice): Invoice;
}
