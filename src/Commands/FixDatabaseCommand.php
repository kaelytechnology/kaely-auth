<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kaely:fix-database {--connection= : Database connection to use}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix database issues related to MySQL key length limits';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $connection = $this->option('connection') ?: config('database.default');
        
        $this->info('🔧 Fixing database issues for KaelyAuth...');
        $this->info('==========================================');

        try {
            // Check if we're using MySQL
            if (config("database.connections.{$connection}.driver") !== 'mysql') {
                $this->info('✅ Not using MySQL, no key length issues expected.');
                return Command::SUCCESS;
            }

            $this->info("📊 Using connection: {$connection}");

            // Check MySQL version and settings
            $this->checkMySQLSettings($connection);

            // Fix database charset if needed
            $this->fixDatabaseCharset($connection);

            // Check for existing tables with issues
            $this->checkExistingTables($connection);

            $this->info('✅ Database fixes completed successfully!');
            $this->displayNextSteps();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Database fix failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Check MySQL settings
     */
    protected function checkMySQLSettings(string $connection): void
    {
        $this->info('🔍 Checking MySQL settings...');

        try {
            $settings = DB::connection($connection)->select("SHOW VARIABLES LIKE 'innodb_file_format'");
            $fileFormat = $settings[0]->Value ?? 'Not set';

            $settings = DB::connection($connection)->select("SHOW VARIABLES LIKE 'innodb_large_prefix'");
            $largePrefix = $settings[0]->Value ?? 'Not set';

            $settings = DB::connection($connection)->select("SHOW VARIABLES LIKE 'innodb_file_per_table'");
            $filePerTable = $settings[0]->Value ?? 'Not set';

            $this->info("  📁 InnoDB File Format: {$fileFormat}");
            $this->info("  🔑 InnoDB Large Prefix: {$largePrefix}");
            $this->info("  📄 InnoDB File Per Table: {$filePerTable}");

            if ($fileFormat !== 'Barracuda' || $largePrefix !== 'ON' || $filePerTable !== 'ON') {
                $this->warn('  ⚠️  MySQL settings may need adjustment for optimal performance');
                $this->info('  💡 Consider running these MySQL commands as root:');
                $this->info('     SET GLOBAL innodb_file_format=Barracuda;');
                $this->info('     SET GLOBAL innodb_file_per_table=1;');
                $this->info('     SET GLOBAL innodb_large_prefix=1;');
            } else {
                $this->info('  ✅ MySQL settings are optimal');
            }
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Could not check MySQL settings: {$e->getMessage()}");
        }
    }

    /**
     * Fix database charset
     */
    protected function fixDatabaseCharset(string $connection): void
    {
        $this->info('🔤 Checking database charset...');

        try {
            $database = config("database.connections.{$connection}.database");
            
            $result = DB::connection($connection)->select("SELECT DEFAULT_CHARACTER_SET_NAME, DEFAULT_COLLATION_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$database]);
            
            if (!empty($result)) {
                $charset = $result[0]->DEFAULT_CHARACTER_SET_NAME;
                $collation = $result[0]->DEFAULT_COLLATION_NAME;

                $this->info("  📊 Database charset: {$charset}");
                $this->info("  📝 Database collation: {$collation}");

                if ($charset !== 'utf8mb4') {
                    $this->warn("  ⚠️  Database is not using utf8mb4 charset");
                    $this->info("  💡 Consider running: ALTER DATABASE {$database} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
                } else {
                    $this->info('  ✅ Database charset is correct (utf8mb4)');
                }
            }
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Could not check database charset: {$e->getMessage()}");
        }
    }

    /**
     * Check existing tables for issues
     */
    protected function checkExistingTables(string $connection): void
    {
        $this->info('📋 Checking existing tables...');

        try {
            $tables = DB::connection($connection)->select("SHOW TABLES");
            
            $kaelyTables = array_filter($tables, function($table) {
                $tableName = array_values((array) $table)[0];
                return str_starts_with($tableName, 'main_') || 
                       str_starts_with($tableName, 'user_') || 
                       str_starts_with($tableName, 'audit_') ||
                       str_starts_with($tableName, 'email_');
            });

            if (empty($kaelyTables)) {
                $this->info('  ✅ No KaelyAuth tables found (this is normal for fresh installations)');
                return;
            }

            $this->info('  📊 Found ' . count($kaelyTables) . ' KaelyAuth tables');

            foreach ($kaelyTables as $table) {
                $tableName = array_values((array) $table)[0];
                $this->info("  📄 Table: {$tableName}");
            }
        } catch (\Exception $e) {
            $this->warn("  ⚠️  Could not check existing tables: {$e->getMessage()}");
        }
    }

    /**
     * Display next steps
     */
    protected function displayNextSteps(): void
    {
        $this->info("\n🚀 Next Steps:");
        $this->info("==============");
        $this->info("1. Run migrations: php artisan migrate");
        $this->info("2. If you still get key length errors, try:");
        $this->info("   - php artisan migrate:fresh");
        $this->info("   - Or manually adjust MySQL settings as root");
        $this->info("3. For production, ensure MySQL is configured with:");
        $this->info("   - innodb_file_format=Barracuda");
        $this->info("   - innodb_large_prefix=ON");
        $this->info("   - innodb_file_per_table=ON");
        
        $this->info("\n💡 Alternative solutions:");
        $this->info("=======================");
        $this->info("- Use SQLite for development: DB_CONNECTION=sqlite");
        $this->info("- Use PostgreSQL: DB_CONNECTION=pgsql");
        $this->info("- Limit string lengths in migrations to 191 characters");
    }
} 