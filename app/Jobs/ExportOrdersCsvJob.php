<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExportOrdersCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;

    public function __construct(
        public readonly ?User $requestedBy = null,
        public readonly ?string $statusFilter = null
    ) {}

    public function handle(): string
    {
        $filename = 'exports/orders_' . now()->format('Y-m-d_His') . '.csv';
        Storage::disk('local')->makeDirectory('exports');
        $fullPath = Storage::disk('local')->path($filename);

        $handle = fopen($fullPath, 'w');
        fputcsv($handle, [
            'Order #',
            'Customer Name',
            'Customer Email',
            'Total (QAR)',
            'Status',
            'Payment Method',
            'Items Count',
            'Created At',
        ]);

        $query = Order::with(['customer', 'items']);
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $query->chunk(200, function ($orders) use ($handle) {
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->customer?->name ?? 'Guest',
                    $order->customer?->email ?? '',
                    number_format((float) $order->total, 2),
                    $order->status?->value ?? (string) $order->status,
                    $order->payment_method?->value ?? (string) $order->payment_method,
                    $order->items->count(),
                    $order->created_at?->toIso8601String(),
                ]);
            }
        });

        fclose($handle);
        Log::info("ExportOrdersCsvJob generated file: {$filename}");

        return $filename;
    }
}

