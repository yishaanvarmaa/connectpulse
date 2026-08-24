<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCampaignRecipientJob;
use App\Models\Campaign;
use Illuminate\Console\Command;

class StartScheduledCampaigns extends Command
{
    protected $signature = 'connectpulse:start-scheduled-campaigns';

    protected $description = 'Start campaigns that are scheduled and due';

    public function handle(): int
    {
        $campaigns = Campaign::query()
            ->where('status', Campaign::STATUS_SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($campaigns as $campaign) {
            $campaign->update([
                'status' => Campaign::STATUS_RUNNING,
                'started_at' => now(),
            ]);

            ProcessCampaignRecipientJob::dispatch($campaign->id)
                ->onConnection('database')
                ->onQueue('campaigns');
        }

        $this->info("Started {$campaigns->count()} scheduled campaign(s).");

        return self::SUCCESS;
    }
}
