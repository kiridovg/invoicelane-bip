<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentInvoiceRepository implements InvoiceRepository
{
    public function paginateLatest(?InvoiceStatus $status, int $perPage): LengthAwarePaginator
    {
        return Invoice::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function persist(Invoice $invoice): Invoice
    {
        $invoice->save();

        return $invoice->refresh();
    }
}
