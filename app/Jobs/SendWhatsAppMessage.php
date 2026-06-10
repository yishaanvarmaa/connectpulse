<?php

namespace App\Jobs;

use App\Models\MessageLog;
use App\Services\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;

    public int $backoff = 5;

    public function __construct(
        public int $messageLogId
    ) {
        $this->tries = config('connectpulse.message_queue_retries', 3);
    }

    public function middleware(): array
    {
        return [new RateLimited('whatsapp-messages')];
    }

    public function handle(MessageService $messageService): void
    {
        $log = MessageLog::with('organization.whatsappConnection')->find($this->messageLogId);

        if (! $log || $log->status !== MessageLog::STATUS_QUEUED) {
            return;
        }

        $messageService->processMessage($log);
    }

    public function failed(?\Throwable $exception): void
    {
        $log = MessageLog::find($this->messageLogId);

        if ($log && $log->status === MessageLog::STATUS_QUEUED) {
            $log->update([
                'status' => MessageLog::STATUS_FAILED,
                'error_message' => 'Message could not be delivered after multiple attempts.',
            ]);
        }

        Log::error('WhatsApp message job failed', [
            'message_log_id' => $this->messageLogId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
