<?php

namespace App\Console\Commands;

use App\Jobs\AI\AnalyzeIncidentImage;
use App\Models\Incident;
use Illuminate\Console\Command;

class ReanalyzeIncidents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incidents:reanalyze 
                            {--status= : Filter by review status (e.g., SEWAGE_CONFIRMED)}
                            {--id= : Reanalyze specific incident ID}
                            {--all : Reanalyze all incidents}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reanalyze incidents with improved AI accuracy settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = Incident::query();

        // Filter by specific ID if provided
        if ($this->option('id')) {
            $query->where('id', $this->option('id'));
        }
        // Filter by status if provided
        elseif ($this->option('status')) {
            $query->where('review_status', $this->option('status'));
        }
        // Reanalyze all if --all flag is set
        elseif ($this->option('all')) {
            // No filter - analyze all
        }
        // Default: only reanalyze SEWAGE_CONFIRMED (likely false positives)
        else {
            $query->where('review_status', 'SEWAGE_CONFIRMED');
            $this->info('Reanalyzing SEWAGE_CONFIRMED incidents (likely false positives)...');
        }

        $incidents = $query->get();

        if ($incidents->isEmpty()) {
            $this->warn('No incidents found to reanalyze.');
            return Command::FAILURE;
        }

        $this->info("Found {$incidents->count()} incident(s) to reanalyze.");

        if (!$this->confirm('Do you want to proceed? This will dispatch background jobs.')) {
            $this->info('Cancelled.');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($incidents->count());
        $bar->start();

        foreach ($incidents as $incident) {
            AnalyzeIncidentImage::dispatch($incident->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Dispatched {$incidents->count()} analysis job(s).");
        $this->info('Make sure queue worker is running: php artisan queue:work');

        return Command::SUCCESS;
    }
}
