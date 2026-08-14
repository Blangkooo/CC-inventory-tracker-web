<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:purge-stale-receipts {--dry-run : List what would be purged without deleting anything}')]
#[Description('Delete receipt/payment-proof image files older than the configured retention period, keeping the DB rows for audit history. Legal documents are never touched.')]
class PurgeStaleReceipts extends Command
{
    public function handle(): int
    {
        // A malformed/zero/blank setting must never resolve to a same-day
        // cutoff — that would purge every current receipt in one run.
        $months = max(1, (int) AppSetting::get('receipt_retention_months', 24));
        $cutoff = now()->subMonths($months);
        $dryRun = (bool) $this->option('dry-run');

        $this->info("Retention: {$months} months — purging files created before {$cutoff->toDateString()}".($dryRun ? ' (dry run)' : ''));

        $purged = 0;
        $purged += $this->purge(Receipt::where('created_at', '<', $cutoff)->whereNotNull('image_path')->get(), 'image_path', $dryRun);
        $purged += $this->purge(Payment::where('created_at', '<', $cutoff)->whereNotNull('receipt_photo')->get(), 'receipt_photo', $dryRun);

        $this->info($dryRun ? "{$purged} file(s) would be purged." : "{$purged} file(s) purged.");

        return self::SUCCESS;
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \Illuminate\Database\Eloquent\Model>  $records
     */
    private function purge($records, string $column, bool $dryRun): int
    {
        $count = 0;

        foreach ($records as $record) {
            $path = $record->{$column};

            if ($dryRun) {
                $this->line("  would purge: {$path}");
                $count++;
                continue;
            }

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            $record->update([$column => null]);
            $count++;
        }

        return $count;
    }
}
