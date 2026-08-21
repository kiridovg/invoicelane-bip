<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        if (Invoice::query()->exists()) {
            return;
        }

        Invoice::factory()->create(['number' => 'INV-2026-0001', 'status' => InvoiceStatus::Pending]);
        Invoice::factory()->create(['number' => 'INV-2026-0002', 'status' => InvoiceStatus::Approved]);
        Invoice::factory()->create(['number' => 'INV-2026-0003', 'status' => InvoiceStatus::Rejected]);

        Invoice::factory()->count(12)->create();
    }
}
