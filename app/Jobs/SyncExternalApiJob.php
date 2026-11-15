<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncExternalApiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;
    public array $backoff = [10, 60, 300];

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $endpoint,
        public readonly string $method = 'POST',
        public readonly array $payload = [],
        public readonly array $headers = []
    ) {}

    public function handle(): void
    {
        try {
            $client = Http::timeout(30)->withHeaders($this->headers);

            $response = match (strtoupper($this->method)) {
                'GET' => $client->get($this->endpoint, $this->payload),
                'PUT' => $client->put($this->endpoint, $this->payload),
                'PATCH' => $client->patch($this->endpoint, $this->payload),
                'DELETE' => $client->delete($this->endpoint, $this->payload),
                default => $client->post($this->endpoint, $this->payload),
            };

            if (! $response->successful()) {
                Log::warning("SyncExternalApiJob HTTP {$response->status()} to {$this->endpoint}: " . $response->body());
                $response->throw();
            }

            Log::info("SyncExternalApiJob successfully sent to {$this->endpoint}");
        } catch (\Throwable $e) {
            Log::error("SyncExternalApiJob failed for {$this->endpoint}: " . $e->getMessage());
            throw $e;
        }
    }
}

