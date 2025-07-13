<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportLogsCommand extends Command
{
    protected $signature = 'kaely:export-logs 
                            {type : Type of logs to export (audit, sessions, oauth, all)}
                            {--format=excel : Export format (excel, pdf, json, csv)}
                            {--days=30 : Number of days to include}
                            {--output= : Output file path}
                            {--filters= : Additional filters (JSON format)}';

    protected $description = 'Export logs in various formats (Excel, PDF, JSON, CSV)';

    public function handle(): int
    {
        $type = $this->argument('type');
        $format = $this->option('format');
        $days = $this->option('days');
        $output = $this->option('output');
        $filters = json_decode($this->option('filters') ?? '{}', true);

        $this->info("📊 Exporting {$type} logs for the last {$days} days in {$format} format...");

        try {
            $data = $this->getLogData($type, $days, $filters);
            
            if (empty($data)) {
                $this->warn("No data found for the specified criteria.");
                return Command::SUCCESS;
            }

            $filename = $this->exportData($data, $format, $output, $type);
            
            $this->info("✅ Export completed successfully!");
            $this->info("📁 File saved as: {$filename}");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Export failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Get log data based on type
     */
    protected function getLogData(string $type, int $days, array $filters): array
    {
        $dateFrom = now()->subDays($days);

        switch ($type) {
            case 'audit':
                return $this->getAuditLogs($dateFrom, $filters);
            case 'sessions':
                return $this->getSessionLogs($dateFrom, $filters);
            case 'oauth':
                return $this->getOAuthLogs($dateFrom, $filters);
            case 'all':
                return $this->getAllLogs($dateFrom, $filters);
            default:
                throw new \Exception("Unknown log type: {$type}");
        }
    }

    /**
     * Get audit logs
     */
    protected function getAuditLogs(\Carbon\Carbon $dateFrom, array $filters): array
    {
        $query = DB::table('audit_logs')
            ->where('created_at', '>=', $dateFrom);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }
        if (isset($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Get session logs
     */
    protected function getSessionLogs(\Carbon\Carbon $dateFrom, array $filters): array
    {
        $query = DB::table('session_activities')
            ->where('created_at', '>=', $dateFrom);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }
        if (isset($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Get OAuth logs
     */
    protected function getOAuthLogs(\Carbon\Carbon $dateFrom, array $filters): array
    {
        $query = DB::table('oauth_logs')
            ->where('created_at', '>=', $dateFrom);

        // Apply filters
        if (isset($filters['provider'])) {
            $query->where('provider', $filters['provider']);
        }
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Get all logs combined
     */
    protected function getAllLogs(\Carbon\Carbon $dateFrom, array $filters): array
    {
        $auditLogs = $this->getAuditLogs($dateFrom, $filters);
        $sessionLogs = $this->getSessionLogs($dateFrom, $filters);
        $oauthLogs = $this->getOAuthLogs($dateFrom, $filters);

        return [
            'audit_logs' => $auditLogs,
            'session_logs' => $sessionLogs,
            'oauth_logs' => $oauthLogs,
        ];
    }

    /**
     * Export data in specified format
     */
    protected function exportData(array $data, string $format, ?string $output, string $type): string
    {
        $filename = $output ?? $this->generateFilename($type, $format);

        switch ($format) {
            case 'excel':
                return $this->exportToExcel($data, $filename, $type);
            case 'pdf':
                return $this->exportToPdf($data, $filename, $type);
            case 'json':
                return $this->exportToJson($data, $filename);
            case 'csv':
                return $this->exportToCsv($data, $filename, $type);
            default:
                throw new \Exception("Unsupported format: {$format}");
        }
    }

    /**
     * Export to Excel
     */
    protected function exportToExcel(array $data, string $filename, string $type): string
    {
        $spreadsheet = new Spreadsheet();
        
        if ($type === 'all') {
            $this->createExcelSheets($spreadsheet, $data);
        } else {
            $this->createExcelSheet($spreadsheet, $data, $type);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return $filename;
    }

    /**
     * Create Excel sheets for all log types
     */
    protected function createExcelSheets(Spreadsheet $spreadsheet, array $data): void
    {
        $spreadsheet->removeSheetByIndex(0); // Remove default sheet

        foreach ($data as $type => $logs) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle(ucfirst($type));
            $this->populateExcelSheet($sheet, $logs, $type);
        }
    }

    /**
     * Create single Excel sheet
     */
    protected function createExcelSheet(Spreadsheet $spreadsheet, array $data, string $type): void
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(ucfirst($type));
        $this->populateExcelSheet($sheet, $data, $type);
    }

    /**
     * Populate Excel sheet with data
     */
    protected function populateExcelSheet($sheet, array $data, string $type): void
    {
        if (empty($data)) {
            $sheet->setCellValue('A1', 'No data available');
            return;
        }

        // Headers
        $headers = array_keys((array) $data[0]);
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', ucfirst(str_replace('_', ' ', $header)));
            $col++;
        }

        // Data
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ((array) $item as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    /**
     * Export to PDF
     */
    protected function exportToPdf(array $data, string $filename, string $type): string
    {
        $html = $this->generatePdfHtml($data, $type);
        
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        
        $output = $dompdf->output();
        File::put($filename, $output);

        return $filename;
    }

    /**
     * Generate HTML for PDF
     */
    protected function generatePdfHtml(array $data, string $type): string
    {
        $html = '<html><head><style>';
        $html .= 'body { font-family: Arial, sans-serif; font-size: 10px; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background-color: #f2f2f2; font-weight: bold; }';
        $html .= 'h1 { color: #333; text-align: center; }';
        $html .= '</style></head><body>';
        
        $html .= "<h1>KaelyAuth {$type} Logs Report</h1>";
        $html .= "<p>Generated on: " . now()->format('Y-m-d H:i:s') . "</p>";

        if ($type === 'all') {
            foreach ($data as $logType => $logs) {
                $html .= "<h2>" . ucfirst($logType) . " Logs</h2>";
                $html .= $this->generateTableHtml($logs);
            }
        } else {
            $html .= $this->generateTableHtml($data);
        }

        $html .= '</body></html>';
        
        return $html;
    }

    /**
     * Generate table HTML
     */
    protected function generateTableHtml(array $data): string
    {
        if (empty($data)) {
            return '<p>No data available</p>';
        }

        $html = '<table>';
        
        // Headers
        $headers = array_keys((array) $data[0]);
        $html .= '<tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . ucfirst(str_replace('_', ' ', $header)) . '</th>';
        }
        $html .= '</tr>';

        // Data
        foreach ($data as $item) {
            $html .= '<tr>';
            foreach ((array) $item as $value) {
                $html .= '<td>' . htmlspecialchars($value) . '</td>';
            }
            $html .= '</tr>';
        }

        $html .= '</table>';
        
        return $html;
    }

    /**
     * Export to JSON
     */
    protected function exportToJson(array $data, string $filename): string
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        File::put($filename, $json);
        
        return $filename;
    }

    /**
     * Export to CSV
     */
    protected function exportToCsv(array $data, string $filename, string $type): string
    {
        if (empty($data)) {
            File::put($filename, "No data available\n");
            return $filename;
        }

        $handle = fopen($filename, 'w');
        
        // Headers
        $headers = array_keys((array) $data[0]);
        fputcsv($handle, $headers);

        // Data
        foreach ($data as $item) {
            fputcsv($handle, (array) $item);
        }

        fclose($handle);
        
        return $filename;
    }

    /**
     * Generate filename
     */
    protected function generateFilename(string $type, string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $extension = $format === 'excel' ? 'xlsx' : $format;
        
        return storage_path("logs/kaely_{$type}_logs_{$timestamp}.{$extension}");
    }

    /**
     * Get supported providers for OAuth logs
     */
    protected function getSupportedProviders(): array
    {
        return [
            'google',
            'facebook',
            'github',
            'linkedin',
            'microsoft',
            'twitter',
            'apple',
            'discord',
            'slack',
            'bitbucket',
            'gitlab',
            'dropbox',
            'box',
            'salesforce',
            'hubspot',
            'zoom',
            'stripe',
            'paypal',
            'twitch',
            'reddit'
        ];
    }

    /**
     * Get log statistics
     */
    protected function getLogStatistics(array $data, string $type): array
    {
        $stats = [
            'total_records' => count($data),
            'period' => $this->option('days') . ' days',
            'exported_at' => now()->format('Y-m-d H:i:s'),
            'type' => $type
        ];

        if ($type === 'audit') {
            $stats['actions'] = collect($data)->groupBy('action')->map->count();
            $stats['users'] = collect($data)->pluck('user_id')->unique()->count();
            $stats['ip_addresses'] = collect($data)->pluck('ip_address')->unique()->count();
        } elseif ($type === 'sessions') {
            $stats['sessions'] = collect($data)->pluck('session_id')->unique()->count();
            $stats['users'] = collect($data)->pluck('user_id')->unique()->count();
            $stats['actions'] = collect($data)->groupBy('action')->map->count();
        } elseif ($type === 'oauth') {
            $stats['providers'] = collect($data)->groupBy('provider')->map->count();
            $stats['users'] = collect($data)->pluck('user_id')->unique()->count();
            $stats['actions'] = collect($data)->groupBy('action')->map->count();
        }

        return $stats;
    }

    /**
     * Add statistics to Excel
     */
    protected function addStatisticsToExcel(Spreadsheet $spreadsheet, array $stats): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Statistics');
        
        $row = 1;
        foreach ($stats as $key => $value) {
            if (is_array($value)) {
                $sheet->setCellValue("A{$row}", ucfirst($key));
                $row++;
                foreach ($value as $subKey => $subValue) {
                    $sheet->setCellValue("A{$row}", "  {$subKey}");
                    $sheet->setCellValue("B{$row}", $subValue);
                    $row++;
                }
            } else {
                $sheet->setCellValue("A{$row}", ucfirst($key));
                $sheet->setCellValue("B{$row}", $value);
                $row++;
            }
        }
        
        // Auto-size columns
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
    }

    /**
     * Add statistics to PDF
     */
    protected function addStatisticsToPdf(string &$html, array $stats): void
    {
        $html .= "<h2>Statistics</h2>";
        $html .= "<table>";
        $html .= "<tr><th>Metric</th><th>Value</th></tr>";
        
        foreach ($stats as $key => $value) {
            if (is_array($value)) {
                $html .= "<tr><td><strong>" . ucfirst($key) . "</strong></td><td></td></tr>";
                foreach ($value as $subKey => $subValue) {
                    $html .= "<tr><td style='padding-left: 20px;'>{$subKey}</td><td>{$subValue}</td></tr>";
                }
            } else {
                $html .= "<tr><td>" . ucfirst($key) . "</td><td>{$value}</td></tr>";
            }
        }
        
        $html .= "</table>";
    }

    /**
     * Validate export parameters
     */
    protected function validateExportParameters(): void
    {
        $type = $this->argument('type');
        $format = $this->option('format');
        $days = $this->option('days');
        
        // Validate type
        $validTypes = ['audit', 'sessions', 'oauth', 'all'];
        if (!in_array($type, $validTypes)) {
            throw new \InvalidArgumentException("Invalid type: {$type}. Valid types: " . implode(', ', $validTypes));
        }
        
        // Validate format
        $validFormats = ['excel', 'pdf', 'json', 'csv'];
        if (!in_array($format, $validFormats)) {
            throw new \InvalidArgumentException("Invalid format: {$format}. Valid formats: " . implode(', ', $validFormats));
        }
        
        // Validate days
        if ($days < 1 || $days > 365) {
            throw new \InvalidArgumentException("Days must be between 1 and 365");
        }
    }

    /**
     * Create output directory
     */
    protected function createOutputDirectory(): void
    {
        $outputDir = storage_path('logs');
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }
    }

    /**
     * Get filters from command options
     */
    protected function parseFilters(string $filtersJson): array
    {
        $filters = json_decode($filtersJson, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException("Invalid JSON format in filters");
        }
        
        return $filters ?? [];
    }

    /**
     * Apply filters to query
     */
    protected function applyFilters($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Format data for export
     */
    protected function formatDataForExport(array $data): array
    {
        return collect($data)->map(function ($item) {
            $formatted = (array) $item;
            
            // Format dates
            if (isset($formatted['created_at'])) {
                $formatted['created_at'] = \Carbon\Carbon::parse($formatted['created_at'])->format('Y-m-d H:i:s');
            }
            if (isset($formatted['updated_at'])) {
                $formatted['updated_at'] = \Carbon\Carbon::parse($formatted['updated_at'])->format('Y-m-d H:i:s');
            }
            
            // Format metadata
            if (isset($formatted['metadata'])) {
                $metadata = json_decode($formatted['metadata'], true);
                if ($metadata) {
                    $formatted['metadata'] = json_encode($metadata, JSON_PRETTY_PRINT);
                }
            }
            
            return $formatted;
        })->toArray();
    }

    /**
     * Get export options help
     */
    protected function getExportOptionsHelp(): string
    {
        return "
Export Options:
  --format=excel|pdf|json|csv  Export format (default: excel)
  --days=30                    Number of days to include (default: 30)
  --output=/path/to/file       Custom output file path
  --filters='{\"user_id\":123}' Additional filters in JSON format

Examples:
  php artisan kaely:export-logs audit --format=excel --days=7
  php artisan kaely:export-logs sessions --format=pdf --output=/tmp/report.pdf
  php artisan kaely:export-logs oauth --format=json --filters='{\"provider\":\"google\"}'
  php artisan kaely:export-logs all --format=csv --days=90
        ";
    }
} 