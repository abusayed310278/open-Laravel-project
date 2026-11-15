<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateInvoicePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(public readonly Invoice $invoice) {}

    public function handle(InvoiceService $invoiceService): void
    {
        if (! $this->invoice->exists) {
            return;
        }

        try {
            $invoiceService->pdf($this->invoice);
        } catch (\Throwable $e) {
            Log::error("GenerateInvoicePdfJob failed for invoice #{$this->invoice->id}: " . $e->getMessage());
        }
    }
}

