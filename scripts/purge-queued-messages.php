<?php

/**
 * List and cancel queued WhatsApp messages (no deploy dependency on MessageService helpers).
 *
 *   php scripts/purge-queued-messages.php
 *   php scripts/purge-queued-messages.php --purge
 *   php scripts/purge-queued-messages.php --purge --org=1
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MessageLog;
use App\Models\Organization;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;

$purge = in_array('--purge', $argv, true);
$orgFilter = null;

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--org=')) {
        $orgFilter = (int) substr($arg, 5);
    }
}

$organizations = Organization::query()
    ->when($orgFilter, fn ($q) => $q->where('id', $orgFilter))
    ->orderBy('id')
    ->get();

if ($organizations->isEmpty()) {
    fwrite(STDERR, 'No organizations found.'.($orgFilter ? " (id={$orgFilter})" : '')."\n");
    exit(1);
}

$queuedTotal = MessageLog::query()->where('status', MessageLog::STATUS_QUEUED)->count();
$pendingJobs = DB::table('jobs')->where('queue', 'messages')->count();

echo "ConnectPulse queue audit\n";
echo "========================\n";
echo "Queued message logs (all orgs): {$queuedTotal}\n";
echo "Pending jobs on queue 'messages': {$pendingJobs}\n\n";

foreach ($organizations as $organization) {
    $queued = MessageLog::query()
        ->where('organization_id', $organization->id)
        ->where('status', MessageLog::STATUS_QUEUED)
        ->orderBy('id')
        ->get();

    echo "#{$organization->id} {$organization->company_name}\n";
    echo "  queued logs: {$queued->count()}\n";

    foreach ($queued as $log) {
        $preview = str_replace("\n", ' ', mb_substr($log->message, 0, 60));
        echo "    - log #{$log->id} → {$log->mobile} | {$preview}\n";
    }

    echo "\n";
}

if (! $purge) {
    echo "No changes made. Run with --purge to cancel queued logs, refund credits, and delete pending jobs.\n";
    exit(0);
}

$creditService = app(CreditService::class);
$totalLogs = 0;
$totalCredits = 0;

foreach ($organizations as $organization) {
    $queued = MessageLog::query()
        ->where('organization_id', $organization->id)
        ->where('status', MessageLog::STATUS_QUEUED)
        ->orderBy('id')
        ->get();

    $orgCredits = 0;

    DB::transaction(function () use ($organization, $queued, $creditService, &$orgCredits) {
        foreach ($queued as $log) {
            $log->update([
                'status' => MessageLog::STATUS_FAILED,
                'error_message' => 'Cancelled — removed from queue before send.',
            ]);

            if ($log->credits_used > 0) {
                $creditService->addCredits(
                    $organization,
                    $log->credits_used,
                    "Refund cancelled queued message #{$log->id}"
                );
                $orgCredits += $log->credits_used;
            }
        }
    });

    $totalLogs += $queued->count();
    $totalCredits += $orgCredits;

    echo "Purged #{$organization->id} {$organization->company_name}: {$queued->count()} log(s), {$orgCredits} credit(s) refunded\n";
}

$deletedJobs = 0;
DB::table('jobs')
    ->where('queue', 'messages')
    ->orderBy('id')
    ->chunkById(200, function ($jobs) use (&$deletedJobs) {
        foreach ($jobs as $job) {
            if (str_contains((string) $job->payload, 'SendWhatsAppMessage')) {
                DB::table('jobs')->where('id', $job->id)->delete();
                $deletedJobs++;
            }
        }
    });

$deletedFailed = 0;
if (DB::getSchemaBuilder()->hasTable('failed_jobs')) {
    DB::table('failed_jobs')
        ->orderBy('id')
        ->chunkById(200, function ($jobs) use (&$deletedFailed) {
            foreach ($jobs as $job) {
                if (str_contains((string) $job->payload, 'SendWhatsAppMessage')) {
                    DB::table('failed_jobs')->where('id', $job->id)->delete();
                    $deletedFailed++;
                }
            }
        });
}

echo "\nDeleted {$deletedJobs} pending job(s), {$deletedFailed} failed job(s).\n";
echo "Done. Total: {$totalLogs} log(s) cancelled, {$totalCredits} credit(s) refunded.\n";
