<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    /**
     * @param  array<int, int>|null  $userIds
     */
    public function __construct(
        public readonly Notification $notification,
        public readonly ?array $userIds = null,
        public readonly ?string $roleFilter = null
    ) {}

    public function handle(): void
    {
        $query = User::query();

        if (! empty($this->userIds)) {
            $query->whereIn('id', $this->userIds);
        }

        if ($this->roleFilter) {
            $query->where('role', $this->roleFilter);
        }

        $sentCount = 0;

        $query->chunkById(100, function ($users) use (&$sentCount) {
            foreach ($users as $user) {
                try {
                    $user->notify($this->notification);
                    $sentCount++;
                } catch (\Throwable $e) {
                    Log::warning("Bulk notification failed for user #{$user->id}: " . $e->getMessage());
                }
            }
        });

        Log::info("SendBulkNotificationJob dispatched {$sentCount} notifications.");
    }
}

