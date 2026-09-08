<?php

namespace App\Http\Controllers\Api;

use App\DataTransferObjects\CreateInvoiceData;
use App\DataTransferObjects\InvoiceListQuery;
use App\DataTransferObjects\UpdateInvoiceData;
use App\Http\Controllers\Controller;
use App\Http\Requests\IndexInvoiceRequest;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function __construct(private readonly InvoiceService $invoices) {}

    public function index(IndexInvoiceRequest $request): AnonymousResourceCollection
    {
        return InvoiceResource::collection(
            $this->invoices->list(InvoiceListQuery::fromArray($request->validated()))
        );
    }

    public function show(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice);
    }

    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $invoice = $this->invoices->create(CreateInvoiceData::fromArray($request->validated()));

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource(
            $this->invoices->update($invoice, UpdateInvoiceData::fromArray($request->validated()))
        );
    }
}
