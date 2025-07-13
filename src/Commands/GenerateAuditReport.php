<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Kaely\Auth\Services\AuditService;
use Illuminate\Support\Facades\Storage;

class GenerateAuditReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:audit-report 
                            {--days=30 : Number of days to include in the report}
                            {--format=json : Export format (json, csv)}
                            {--output= : Output file path (optional)}
                            {--user-id= : Generate report for specific user only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate audit report and export audit data';

    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        parent::__construct();
        $this->auditService = $auditService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $format = $this->option('format');
        $output = $this->option('output');
        $userId = $this->option('user-id');

        $this->info("Generating audit report for the last {$days} days...");

        try {
            if ($userId) {
                $this->generateUserReport($userId, $days, $format, $output);
            } else {
                $this->generateSystemReport($days, $format, $output);
            }

            $this->info('Audit report generated successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error generating audit report: {$e->getMessage()}");
            return Command::FAILURE;
        }
    }

    /**
     * Generate system-wide audit report
     */
    protected function generateSystemReport(int $days, string $format, ?string $output): void
    {
        $report = $this->auditService->generateAuditReport($days);
        
        $this->displaySystemReport($report);
        
        if ($output) {
            $this->exportReport($report, $format, $output);
        }
    }

    /**
     * Generate user-specific audit report
     */
    protected function generateUserReport(int $userId, int $days, string $format, ?string $output): void
    {
        $summary = $this->auditService->getUserActivitySummary($userId, $days);
        $timeline = $this->auditService->getUserTimeline($userId, $days);
        
        $report = [
            'user_id' => $userId,
            'period' => $days . ' days',
            'summary' => $summary,
            'timeline' => $timeline,
            'generated_at' => now(),
        ];

        $this->displayUserReport($report);
        
        if ($output) {
            $this->exportReport($report, $format, $output);
        }
    }

    /**
     * Display system report in console
     */
    protected function displaySystemReport(array $report): void
    {
        $this->info("\n=== AUDIT REPORT ===");
        $this->info("Period: {$report['period']}");
        $this->info("Generated: {$report['generated_at']}");
        
        $this->info("\n=== SUMMARY ===");
        $summary = $report['summary'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Logs', $summary['total_logs']],
                ['Success Logs', $summary['success_logs']],
                ['Failed Logs', $summary['failed_logs']],
                ['Success Rate', $summary['success_rate'] . '%'],
            ]
        );

        $this->info("\n=== TOP ACTIONS ===");
        $actions = $report['top_actions'];
        if ($actions->count() > 0) {
            $this->table(
                ['Action', 'Count'],
                $actions->map(fn($action) => [$action->action, $action->count])->toArray()
            );
        }

        $this->info("\n=== SECURITY ALERTS ===");
        $alerts = $report['security_alerts'];
        if (count($alerts) > 0) {
            foreach ($alerts as $alert) {
                $this->warn("- {$alert['message']}");
            }
        } else {
            $this->info("No security alerts detected.");
        }

        $this->info("\n=== SECURITY THREATS ===");
        $threats = $report['security_threats'];
        if (count($threats) > 0) {
            foreach ($threats as $threat) {
                $this->error("- {$threat['message']} (Severity: {$threat['severity']})");
            }
        } else {
            $this->info("No security threats detected.");
        }
    }

    /**
     * Display user report in console
     */
    protected function displayUserReport(array $report): void
    {
        $this->info("\n=== USER AUDIT REPORT ===");
        $this->info("User ID: {$report['user_id']}");
        $this->info("Period: {$report['period']}");
        $this->info("Generated: {$report['generated_at']}");
        
        $this->info("\n=== ACTIVITY SUMMARY ===");
        $summary = $report['summary'];
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Activities', $summary['total_activities']],
                ['Success Count', $summary['success_count']],
                ['Failed Count', $summary['failed_count']],
                ['Warning Count', $summary['warning_count']],
                ['Success Rate', $summary['success_rate'] . '%'],
            ]
        );

        if ($summary['last_activity']) {
            $this->info("Last Activity: {$summary['last_activity']}");
        }

        $this->info("\n=== ACTION BREAKDOWN ===");
        $breakdown = $summary['action_breakdown'];
        if (count($breakdown) > 0) {
            $this->table(
                ['Action', 'Count'],
                collect($breakdown)->map(fn($count, $action) => [$action, $count])->toArray()
            );
        }

        $this->info("\n=== RECENT ACTIVITY ===");
        $timeline = $report['timeline'];
        if ($timeline->count() > 0) {
            $this->table(
                ['Date', 'Action', 'Status', 'Description'],
                $timeline->take(10)->map(fn($log) => [
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->action,
                    $log->status,
                    substr($log->description, 0, 50) . '...'
                ])->toArray()
            );
        }
    }

    /**
     * Export report to file
     */
    protected function exportReport(array $report, string $format, string $output): void
    {
        $content = $format === 'json' ? json_encode($report, JSON_PRETTY_PRINT) : $this->convertToCsv($report);
        
        if (Storage::disk('local')->put($output, $content)) {
            $this->info("Report exported to: {$output}");
        } else {
            $this->error("Failed to export report to: {$output}");
        }
    }

    /**
     * Convert report to CSV format
     */
    protected function convertToCsv(array $report): string
    {
        $csv = "Report Type,Value\n";
        
        foreach ($report as $key => $value) {
            if (is_array($value)) {
                $csv .= "{$key}," . json_encode($value) . "\n";
            } else {
                $csv .= "{$key},{$value}\n";
            }
        }
        
        return $csv;
    }
} 