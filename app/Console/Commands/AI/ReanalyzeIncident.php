<?php

namespace App\Console\Commands\AI;

use App\Jobs\AI\AnalyzeIncidentImage;
use App\Models\Incident;
use Illuminate\Console\Command;

class ReanalyzeIncident extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'incidents:reanalyze 
                            {--id= : Specific incident ID to reanalyze}
                            {--status= : Reanalyze incidents with specific status}
                            {--all : Reanalyze all incidents}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reanalyze incident(s) using the Sewage Sentinel pipeline';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $incidentId = $this->option('id');
        $status = $this->option('status');
        $all = $this->option('all');
        $force = $this->option('force');

        if ($incidentId) {
            // Reanalyze specific incident
            $incident = Incident::find($incidentId);
            
            if (!$incident) {
                $this->error("Incident #{$incidentId} not found.");
                return Command::FAILURE;
            }

            $this->info("Reanalyzing incident #{$incidentId}...");
            AnalyzeIncidentImage::dispatch($incident->id);
            $this->info("Job dispatched for incident #{$incidentId}.");
            
            return Command::SUCCESS;
        }

        if ($status) {
            // Reanalyze incidents with specific status
            $incidents = Incident::where('review_status', $status)->get();
            
            if ($incidents->isEmpty()) {
                $this->warn("No incidents found with status: {$status}");
                return Command::SUCCESS;
            }

            $this->info("Found {$incidents->count()} incident(s) with status: {$status}");
            
            if (!$force && !$this->confirm('Do you want to reanalyze these incidents?')) {
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
            $this->info("Dispatched {$incidents->count()} job(s) for reanalysis.");
            
            return Command::SUCCESS;
        }

        if ($all) {
            // Reanalyze all incidents
            $incidents = Incident::all();
            
            if ($incidents->isEmpty()) {
                $this->warn('No incidents found.');
                return Command::SUCCESS;
            }

            $this->info("Found {$incidents->count()} total incident(s)");
            
            if (!$force && !$this->confirm('Do you want to reanalyze ALL incidents? This may take a while.')) {
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
            $this->info("Dispatched {$incidents->count()} job(s) for reanalysis.");
            
            return Command::SUCCESS;
        }

        // No options provided - show help
        $this->error('Please specify --id, --status, or --all option.');
        $this->line('');
        $this->line('Examples:');
        $this->line('  php artisan incidents:reanalyze --id=30');
        $this->line('  php artisan incidents:reanalyze --status=NEEDS_REVIEW');
        $this->line('  php artisan incidents:reanalyze --all');
        
        return Command::FAILURE;
    }
}
