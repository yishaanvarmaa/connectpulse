<?php

use App\Models\MessageLog;
use App\Models\Organization;
use App\Services\MessageService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('connectpulse:purge-queued {--org= : Organization ID (optional)} {--dry-run : List only, do not cancel}', function () {
    $orgFilter = $this->option('org') ? (int) $this->option('org') : null;
    $dryRun = (bool) $this->option('dry-run');

    $organizations = Organization::query()
        ->when($orgFilter, fn ($q) => $q->where('id', $orgFilter))
        ->orderBy('id')
        ->get();

    if ($organizations->isEmpty()) {
        $this->error('No organizations found.');

        return 1;
    }

    $queuedTotal = MessageLog::query()->where('status', MessageLog::STATUS_QUEUED)->count();
    $pendingJobs = DB::table('jobs')->where('queue', 'messages')->count();

    $this->info("Queued message logs: {$queuedTotal}");
    $this->info("Pending jobs (messages queue): {$pendingJobs}");

    foreach ($organizations as $organization) {
        $queued = MessageLog::query()
            ->where('organization_id', $organization->id)
            ->where('status', MessageLog::STATUS_QUEUED)
            ->orderBy('id')
            ->get();

        $this->line("#{$organization->id} {$organization->company_name} — {$queued->count()} queued");

        foreach ($queued as $log) {
            $preview = str_replace("\n", ' ', mb_substr($log->message, 0, 72));
            $this->line("  log #{$log->id} → {$log->mobile} | {$preview}");
        }
    }

    if ($dryRun) {
        $this->warn('Dry run — no changes made. Omit --dry-run to purge.');

        return 0;
    }

    $messageService = app(MessageService::class);
    $logs = 0;
    $credits = 0;
    $jobs = 0;
    $failed = 0;

    foreach ($organizations as $organization) {
        $result = $messageService->purgeQueuedMessages($organization, refundCredits: true);
        $logs += $result['queued_logs_cancelled'];
        $credits += $result['credits_refunded'];
        $jobs += $result['pending_jobs_deleted'];
        $failed += $result['failed_jobs_deleted'];
    }

    $this->info("Cancelled {$logs} queued log(s), refunded {$credits} credit(s).");
    $this->info("Deleted {$jobs} pending job(s), {$failed} failed job(s).");

    return 0;
})->purpose('Cancel queued WhatsApp messages and drop pending send jobs');

Schedule::command('connectpulse:start-scheduled-campaigns')->everyMinute();
