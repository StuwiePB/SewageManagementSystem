<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Services\Reports\CustomerReportDrainageScan;
use Illuminate\Console\Command;
use Throwable;

class RescanCustomerReportDrainageCommand extends Command
{
    protected $signature = 'reports:rescan-drainage
        {--no-reset : Do not reset existing drainage_ai_verdict values before scanning}
        {--chunk=100 : Chunk size for database iteration}';

    protected $description = 'Rescan customer report photos and refresh drainage AI verdicts';

    public function handle(CustomerReportDrainageScan $scanner): int
    {
        $chunk = max(10, (int) $this->option('chunk'));
        $reset = ! (bool) $this->option('no-reset');

        if ($reset) {
            $updated = Report::query()
                ->whereNotNull('photo_path')
                ->whereNotNull('drainage_ai_verdict')
                ->update(['drainage_ai_verdict' => null]);

            $this->info("Reset {$updated} existing verdict(s) to NULL.");
        } else {
            $this->line('Skipping verdict reset (--no-reset).');
        }

        $total = Report::query()
            ->whereNotNull('photo_path')
            ->whereNull('drainage_ai_verdict')
            ->count();

        if ($total === 0) {
            $this->info('No reports need scanning.');

            return self::SUCCESS;
        }

        $this->line("Scanning {$total} report photo(s)...");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $scanned = 0;
        $failed = 0;

        Report::query()
            ->whereNotNull('photo_path')
            ->whereNull('drainage_ai_verdict')
            ->orderBy('id')
            ->chunkById($chunk, function ($reports) use ($scanner, $bar, &$scanned, &$failed): void {
                foreach ($reports as $report) {
                    try {
                        $scanner->scanAndPersist($report);
                        $scanned++;
                    } catch (Throwable $e) {
                        $failed++;
                        $this->newLine();
                        $this->warn("Report #{$report->id} scan failed: {$e->getMessage()}");
                    } finally {
                        $bar->advance();
                    }
                }
            });

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Scanned: {$scanned}. Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}

