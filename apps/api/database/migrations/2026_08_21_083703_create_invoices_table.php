<?php

declare(strict_types=1);

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('number', 64)->unique();
            $table->string('supplier_name');
            $table->string('supplier_tax_id', 32);

            $table->decimal('net_amount', 15);
            $table->decimal('vat_amount', 15);
            $table->decimal('gross_amount', 15);
            $table->char('currency', 3)->default('UAH');

            $table->string('status', 16)->default(InvoiceStatus::Pending->value);

            $table->date('issue_date');
            $table->date('due_date');

            $table->timestamps();

            $table->index('created_at');
            $table->index('status');
        });

        $constraints = [
            'invoices_net_positive'     => 'net_amount > 0',
            'invoices_vat_non_negative' => 'vat_amount >= 0',
            'invoices_gross_consistent' => 'gross_amount = net_amount + vat_amount',
            'invoices_dates_ordered'    => 'due_date >= issue_date',
        ];

        foreach ($constraints as $name => $expression) {
            DB::statement("ALTER TABLE invoices ADD CONSTRAINT {$name} CHECK ({$expression})");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
