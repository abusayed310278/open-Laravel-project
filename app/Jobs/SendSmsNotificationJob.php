<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public readonly string $toPhone,
        public readonly string $message,
        public readonly ?string $provider = null
    ) {}

    public function handle(): void
    {
        $provider = $this->provider ?: config('services.sms.default', 'log');

        try {
            match ($provider) {
                'twilio' => $this->sendTwilio(),
                'vonage' => $this->sendVonage(),
                'custom' => $this->sendCustomGateway(),
                default => Log::info("Queued SMS [{$provider}] to {$this->toPhone}: {$this->message}"),
            };
        } catch (\Throwable $e) {
            Log::error("SendSmsNotificationJob failed to {$this->toPhone}: " . $e->getMessage());
            throw $e;
        }
    }

    private function sendTwilio(): void
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (! $sid || ! $token) {
            Log::info("Twilio SMS credentials missing. Simulating SMS to {$this->toPhone}: {$this->message}");
            return;
        }

        Http::withBasicAuth($sid, $token)
            ->asForm()
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                'To' => $this->toPhone,
                'From' => $from,
                'Body' => $this->message,
            ])
            ->throw();
    }

    private function sendVonage(): void
    {
        $key = config('services.vonage.key');
        $secret = config('services.vonage.secret');
        $from = config('services.vonage.from', config('app.name'));

        if (! $key || ! $secret) {
            Log::info("Vonage SMS credentials missing. Simulating SMS to {$this->toPhone}: {$this->message}");
            return;
        }

        Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => $key,
            'api_secret' => $secret,
            'to' => $this->toPhone,
            'from' => $from,
            'text' => $this->message,
        ])->throw();
    }

    private function sendCustomGateway(): void
    {
        $endpoint = config('services.sms.endpoint');
        $apiKey = config('services.sms.key');

        if (! $endpoint) {
            Log::info("Custom SMS gateway missing endpoint. Simulating SMS to {$this->toPhone}: {$this->message}");
            return;
        }

        Http::withToken($apiKey)
            ->post($endpoint, [
                'recipient' => $this->toPhone,
                'message' => $this->message,
            ])
            ->throw();
    }
}

