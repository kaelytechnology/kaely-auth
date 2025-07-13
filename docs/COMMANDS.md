# KaelyAuth Commands Documentation

## 📋 Available Commands

### Installation Commands

#### `kaely:install`
Main installation command with interactive wizard and multi-language support.

**Usage:**
```bash
php artisan kaely:install
```

**Options:**
- `--force`: Force installation without confirmation
- `--skip-wizard`: Skip the interactive wizard
- `--language=`: Set installation language (en/es)

**Examples:**
```bash
# Interactive installation with language selection
php artisan kaely:install

# Install with specific language
php artisan kaely:install --language=en
php artisan kaely:install --language=es

# Skip wizard and install with defaults
php artisan kaely:install --skip-wizard

# Force installation
php artisan kaely:install --force
```

**Features:**
- 🌐 Multi-language support (English/Spanish)
- 🔍 Laravel version compatibility check
- 📦 Authentication package detection and installation
- 🗄️ Database connection verification
- 🔐 OAuth provider configuration
- 🏢 Multitenancy setup
- ⚙️ Additional features configuration

**Supported Languages:**
- **English (en)**: Default language
- **Spanish (es)**: Complete Spanish translation

### OAuth Commands

#### `kaely:setup-oauth`
Configure OAuth providers (Google, Facebook).

**Usage:**
```bash
php artisan kaely:setup-oauth
```

**Features:**
- Google OAuth configuration
- Facebook OAuth configuration
- Automatic Socialite installation
- Redirect URI setup

### Multitenancy Commands

#### `kaely:setup-multitenancy`
Configure multitenancy system.

**Usage:**
```bash
php artisan kaely:setup-multitenancy
```

**Features:**
- Tenant mode selection (subdomain/domain)
- Database configuration for tenants
- Tenant resolver setup

#### `kaely:create-tenant`
Create a new tenant.

**Usage:**
```bash
php artisan kaely:create-tenant
```

**Options:**
- `--name=`: Tenant name
- `--domain=`: Tenant domain
- `--database=`: Tenant database name

**Examples:**
```bash
# Interactive tenant creation
php artisan kaely:create-tenant

# Create tenant with specific parameters
php artisan kaely:create-tenant --name="My Company" --domain="mycompany.com"
```

### Maintenance Commands

#### `kaely:cleanup-tokens`
Clean up expired tokens and sessions.

**Usage:**
```bash
php artisan kaely:cleanup-tokens
```

**Options:**
- `--days=`: Number of days to keep tokens (default: 30)

**Examples:**
```bash
# Clean up tokens older than 30 days
php artisan kaely:cleanup-tokens

# Clean up tokens older than 7 days
php artisan kaely:cleanup-tokens --days=7
```

#### `kaely:audit-report`
Generate audit logging reports.

**Usage:**
```bash
php artisan kaely:audit-report
```

**Options:**
- `--days=`: Number of days to include in report (default: 30)
- `--format=`: Output format (json, csv, html)

**Examples:**
```bash
# Generate report for last 30 days
php artisan kaely:audit-report

# Generate report for last 90 days in JSON format
php artisan kaely:audit-report --days=90 --format=json
```

## 🌐 Multi-Language Support

### Language Selection

The installer supports multiple languages and will automatically detect your preference:

**English (Default):**
```bash
php artisan kaely:install --language=en
```

**Spanish:**
```bash
php artisan kaely:install --language=es
```

### Interactive Language Selection

When running the installer without specifying a language, it will prompt you to select your preferred language:

```
🌐 Language Selection
Select your preferred language for the installation:
  [0] English
  [1] Español
```

### Translation Files

Language files are located in:
- English: `packages/kaelyAuth/lang/en/installer.php`
- Spanish: `packages/kaelyAuth/lang/es/installer.php`

### Adding New Languages

To add support for a new language:

1. Create a new language directory: `packages/kaelyAuth/lang/{language_code}/`
2. Create `installer.php` file with translations
3. Update the language selection options in the InstallCommand

## 🔧 Command Development

### Creating New Commands

To create a new command:

1. Create command class in `packages/kaelyAuth/src/Commands/`
2. Extend `Illuminate\Console\Command`
3. Register in `KaelyAuthServiceProvider::registerCommands()`

**Example:**
```php
<?php

namespace Kaely\Auth\Commands;

use Illuminate\Console\Command;

class MyCustomCommand extends Command
{
    protected $signature = 'kaely:my-command';
    protected $description = 'My custom command description';

    public function handle()
    {
        // Command logic here
    }
}
```

### Adding Translations

To add translations for a new command:

1. Add translation keys to language files
2. Use the `trans()` method in your command
3. Support multiple languages

**Example:**
```php
// In your command
$this->info($this->trans('my_command.title'));

// In language files
// en/installer.php
'my_command' => [
    'title' => 'My Command Title',
],

// es/installer.php
'my_command' => [
    'title' => 'Título de Mi Comando',
],
```

## 📚 Best Practices

### Command Structure
- Use descriptive command names with `kaely:` prefix
- Provide clear descriptions
- Include helpful options and examples
- Support both interactive and non-interactive modes

### Error Handling
- Validate inputs thoroughly
- Provide clear error messages
- Use appropriate exit codes
- Log important operations

### User Experience
- Provide progress indicators for long operations
- Use colors and formatting for better readability
- Include confirmation prompts for destructive operations
- Offer help and examples

### Testing
- Write unit tests for commands
- Test both success and failure scenarios
- Test with different input combinations
- Verify output formatting

## 🚀 Scheduled Commands

The package automatically registers scheduled commands:

```php
// Clean up tokens daily at 2:00 AM
$schedule->command('kaely:cleanup-tokens')->daily()->at('02:00');

// Generate audit reports weekly at 3:00 AM
$schedule->command('kaely:audit-report --days=90')->weekly()->at('03:00');
```

## 📖 Help and Documentation

### Getting Help
```bash
# Show command help
php artisan kaely:install --help

# List all available commands
php artisan list kaely
```

### Command Examples
```bash
# Full installation with Spanish language
php artisan kaely:install --language=es

# Quick installation with defaults
php artisan kaely:install --skip-wizard

# Setup OAuth with specific providers
php artisan kaely:setup-oauth

# Create tenant with custom parameters
php artisan kaely:create-tenant --name="My Company" --domain="mycompany.com"

# Generate audit report for last 60 days
php artisan kaely:audit-report --days=60 --format=json
``` 