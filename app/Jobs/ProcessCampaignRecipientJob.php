<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Services\CampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCampaignRecipientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 120;

    public function __construct(
        public int $campaignId
    ) {}

    public function handle(CampaignService $campaignService): void
    {
        $campaign = Campaign::with('organization')->find($this->campaignId);

        if (! $campaign) {
            return;
        }

        if ($campaign->status !== Campaign::STATUS_RUNNING) {
            return;
        }

        try {
            $campaignService->processNextRecipient($campaign);
        } catch (\Throwable $e) {
            Log::error('Campaign recipient processing failed', [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
