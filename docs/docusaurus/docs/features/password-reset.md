# Password Reset

The KaelyAuth package provides a comprehensive password reset system that works seamlessly with multiple databases and tenant support.

## Features

- **Secure Token Generation**: Uses cryptographically secure tokens
- **Configurable Expiration**: Customizable token expiration time
- **Email Notifications**: Professional email templates
- **Audit Logging**: Complete audit trail of reset attempts
- **Multi-database Support**: Works with single and multiple database setups
- **Tenant Support**: Tenant-aware password reset functionality

## Configuration

### Basic Configuration

```php
// config/kaely-auth.php
'password_reset' => [
    'enabled' => env('KAELY_AUTH_PASSWORD_RESET_ENABLED', true),
    'expiration_hours' => env('KAELY_AUTH_PASSWORD_RESET_EXPIRATION', 24),
    'frontend_url' => env('KAELY_AUTH_PASSWORD_RESET_FRONTEND_URL'),
    'email_template' => env('KAELY_AUTH_PASSWORD_RESET_EMAIL_TEMPLATE', 'kaely-auth::emails.password-reset'),
],
```

### Environment Variables

```env
KAELY_AUTH_PASSWORD_RESET_ENABLED=true
KAELY_AUTH_PASSWORD_RESET_EXPIRATION=24
KAELY_AUTH_PASSWORD_RESET_FRONTEND_URL=https://your-app.com/reset-password
```

## API Endpoints

### Send Password Reset Email

```http
POST /api/v1/password/reset
Content-Type: application/json

{
    "email": "user@example.com"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Password reset link sent successfully"
}
```

### Validate Reset Token

```http
POST /api/v1/password/reset/validate
Content-Type: application/json

{
    "email": "user@example.com",
    "token": "reset-token-here"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Token is valid"
}
```

### Reset Password

```http
POST /api/v1/password/reset/confirm
Content-Type: application/json

{
    "email": "user@example.com",
    "token": "reset-token-here",
    "password": "new-password",
    "password_confirmation": "new-password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Password reset successfully"
}
```

## Usage Examples

### Using the Service

```php
use Kaely\Auth\Services\PasswordResetService;

class AuthController extends Controller
{
    protected PasswordResetService $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function forgotPassword(Request $request)
    {
        $email = $request->input('email');
        
        $success = $this->passwordResetService->sendResetEmail($email);
        
        if ($success) {
            return response()->json(['message' => 'Reset email sent']);
        }
        
        return response()->json(['message' => 'Unable to send reset email'], 400);
    }

    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');
        
        $success = $this->passwordResetService->resetPassword($email, $token, $password);
        
        if ($success) {
            return response()->json(['message' => 'Password reset successfully']);
        }
        
        return response()->json(['message' => 'Invalid or expired token'], 400);
    }
}
```

### Using the Model

```php
use Kaely\Auth\Models\PasswordReset;

// Create a reset token
$reset = PasswordReset::createReset('user@example.com');

// Check if token is expired
if ($reset->isExpired()) {
    // Handle expired token
}

// Find valid token
$validReset = PasswordReset::findValidToken('user@example.com', 'token-here');

if ($validReset) {
    // Token is valid
} else {
    // Token is invalid or expired
}
```

## Email Templates

The package includes professional email templates for password reset notifications. You can customize these templates by publishing them:

```bash
php artisan vendor:publish --tag=kaely-auth-views
```

### Customizing Email Templates

```php
// config/kaely-auth.php
'password_reset' => [
    'email_template' => 'emails.custom-password-reset',
],
```

## Security Features

### Token Security

- Tokens are cryptographically secure (60 characters)
- Automatic expiration after configured time
- One-time use tokens
- Automatic cleanup of expired tokens

### Rate Limiting

```php
// routes/api.php
Route::middleware('throttle:6,1')->group(function () {
    Route::post('password/reset', [PasswordResetController::class, 'sendResetLink']);
});
```

### Audit Logging

All password reset attempts are logged for security monitoring:

```php
// Check failed attempts
$failedAttempts = AuditLog::getFailedLoginAttempts($userId, 24);
```

## Maintenance

### Cleanup Expired Tokens

```bash
# Manual cleanup
php artisan kaely:cleanup-tokens --type=password-reset

# Automated cleanup (daily at 2:00 AM)
# Already configured in the service provider
```

### Monitoring

```php
// Get password reset statistics
$stats = PasswordResetService::getStats();

// Monitor suspicious activity
$suspiciousActivity = AuditService::getSuspiciousActivity();
```

## Frontend Integration

### React Example

```javascript
const requestPasswordReset = async (email) => {
    try {
        const response = await fetch('/api/v1/password/reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Password reset email sent!');
        } else {
            alert('Error sending reset email');
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const resetPassword = async (email, token, password) => {
    try {
        const response = await fetch('/api/v1/password/reset/confirm', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, token, password }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Password reset successfully!');
            // Redirect to login
        } else {
            alert('Invalid or expired token');
        }
    } catch (error) {
        console.error('Error:', error);
    }
};
```

### Vue.js Example

```javascript
// Password reset methods
methods: {
    async requestReset() {
        try {
            const response = await this.$http.post('/api/v1/password/reset', {
                email: this.email
            });
            
            if (response.data.success) {
                this.$toast.success('Password reset email sent!');
            }
        } catch (error) {
            this.$toast.error('Error sending reset email');
        }
    },
    
    async confirmReset() {
        try {
            const response = await this.$http.post('/api/v1/password/reset/confirm', {
                email: this.email,
                token: this.token,
                password: this.password,
                password_confirmation: this.password_confirmation
            });
            
            if (response.data.success) {
                this.$toast.success('Password reset successfully!');
                this.$router.push('/login');
            }
        } catch (error) {
            this.$toast.error('Invalid or expired token');
        }
    }
}
```

## Troubleshooting

### Common Issues

1. **Emails not sending**
   - Check mail configuration in `.env`
   - Verify SMTP settings
   - Check email template exists

2. **Tokens not working**
   - Verify token expiration time
   - Check if cleanup command is running
   - Ensure database connection is correct

3. **Frontend URL issues**
   - Set `KAELY_AUTH_PASSWORD_RESET_FRONTEND_URL` in `.env`
   - Ensure URL is accessible
   - Check CORS settings

### Debug Mode

```php
// Enable debug mode
'debug' => [
    'enabled' => true,
    'log_requests' => true,
],
```

This will log all password reset requests for debugging purposes. 