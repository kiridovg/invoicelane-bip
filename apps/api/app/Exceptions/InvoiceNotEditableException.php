<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\InvoiceStatus;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InvoiceNotEditableException extends Exception
{
    public function __construct(public readonly InvoiceStatus $status)
    {
        parent::__construct(
            sprintf('Invoice in status "%s" can no longer be modified.', $status->value)
        );
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors'  => ['status' => [$this->getMessage()]],
        ], Response::HTTP_CONFLICT);
    }
}
