# Email Verification

The KaelyAuth package provides a robust email verification system that ensures user account security and compliance with email verification requirements.

## Features

- **Secure Verification Tokens**: Cryptographically secure verification tokens
- **Configurable Expiration**: Customizable token expiration time
- **Professional Email Templates**: Beautiful, responsive email templates
- **Audit Logging**: Complete audit trail of verification attempts
- **Multi-database Support**: Works with single and multiple database setups
- **Tenant Support**: Tenant-aware email verification
- **Optional/Required**: Configurable verification requirements

## Configuration

### Basic Configuration

```php
// config/kaely-auth.php
'email_verification' => [
    'enabled' => env('KAELY_AUTH_EMAIL_VERIFICATION_ENABLED', true),
    'expiration_hours' => env('KAELY_AUTH_EMAIL_VERIFICATION_EXPIRATION', 24),
    'frontend_url' => env('KAELY_AUTH_EMAIL_VERIFICATION_FRONTEND_URL'),
    'email_template' => env('KAELY_AUTH_EMAIL_VERIFICATION_EMAIL_TEMPLATE', 'kaely-auth::emails.email-verification'),
    'required' => env('KAELY_AUTH_EMAIL_VERIFICATION_REQUIRED', false),
],
```

### Environment Variables

```env
KAELY_AUTH_EMAIL_VERIFICATION_ENABLED=true
KAELY_AUTH_EMAIL_VERIFICATION_EXPIRATION=24
KAELY_AUTH_EMAIL_VERIFICATION_FRONTEND_URL=https://your-app.com/verify-email
KAELY_AUTH_EMAIL_VERIFICATION_REQUIRED=false
```

## API Endpoints

### Send Verification Email

```http
POST /api/v1/email/verify/send
Authorization: Bearer {token}
Content-Type: application/json

{}
```

**Response:**
```json
{
    "success": true,
    "message": "Verification email sent successfully"
}
```

### Verify Email with Token

```http
POST /api/v1/email/verify/confirm
Content-Type: application/json

{
    "token": "verification-token-here"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Email verified successfully"
}
```

### Resend Verification Email

```http
POST /api/v1/email/verify/resend
Authorization: Bearer {token}
Content-Type: application/json

{}
```

**Response:**
```json
{
    "success": true,
    "message": "Verification email resent successfully"
}
```

### Check Verification Status

```http
GET /api/v1/email/verify/status
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "is_verified": true,
        "email": "user@example.com",
        "verified_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

## Usage Examples

### Using the Service

```php
use Kaely\Auth\Services\EmailVerificationService;

class AuthController extends Controller
{
    protected EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    public function sendVerification(Request $request)
    {
        $user = Auth::user();
        
        if ($this->emailVerificationService->isEmailVerified($user)) {
            return response()->json(['message' => 'Email already verified']);
        }
        
        $success = $this->emailVerificationService->sendVerificationEmail($user);
        
        if ($success) {
            return response()->json(['message' => 'Verification email sent']);
        }
        
        return response()->json(['message' => 'Unable to send verification email'], 400);
    }

    public function verifyEmail(Request $request)
    {
        $token = $request->input('token');
        
        $success = $this->emailVerificationService->verifyEmail($token);
        
        if ($success) {
            return response()->json(['message' => 'Email verified successfully']);
        }
        
        return response()->json(['message' => 'Invalid or expired token'], 400);
    }
}
```

### Using the Model

```php
use Kaely\Auth\Models\EmailVerification;

// Create verification for user
$verification = EmailVerification::createVerification($user->id, $user->email);

// Check if verification is expired
if ($verification->isExpired()) {
    // Handle expired verification
}

// Check if email is verified
if ($verification->isVerified()) {
    // Email is verified
}

// Mark as verified
$verification->markAsVerified();

// Find valid verification token
$validVerification = EmailVerification::findValidToken('token-here');

if ($validVerification) {
    // Token is valid
} else {
    // Token is invalid or expired
}
```

## Middleware Integration

### Email Verification Middleware

```php
// routes/api.php
Route::middleware(['auth:sanctum', 'kaely.verified'])->group(function () {
    // Routes that require email verification
    Route::get('protected-route', [Controller::class, 'method']);
});
```

### Custom Middleware

```php
use Kaely\Auth\Middleware\EmailVerificationMiddleware;

// Register middleware
Route::middleware(['auth:sanctum', EmailVerificationMiddleware::class])->group(function () {
    // Protected routes
});
```

## Email Templates

The package includes professional email templates for verification notifications. You can customize these templates by publishing them:

```bash
php artisan vendor:publish --tag=kaely-auth-views
```

### Customizing Email Templates

```php
// config/kaely-auth.php
'email_verification' => [
    'email_template' => 'emails.custom-email-verification',
],
```

### Custom Email Template Example

```php
// resources/views/emails/custom-email-verification.blade.php
<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
</head>
<body>
    <h1>Welcome to {{ config('app.name') }}!</h1>
    <p>Please verify your email address by clicking the link below:</p>
    <a href="{{ $verificationUrl }}">Verify Email</a>
    <p>This link expires at: {{ $expiresAt->format('F j, Y \a\t g:i A') }}</p>
</body>
</html>
```

## Security Features

### Token Security

- Tokens are cryptographically secure (64 characters)
- Automatic expiration after configured time
- One-time use tokens
- Automatic cleanup of expired tokens

### Rate Limiting

```php
// routes/api.php
Route::middleware('throttle:6,1')->group(function () {
    Route::post('email/verify/send', [EmailVerificationController::class, 'sendVerificationEmail']);
    Route::post('email/verify/resend', [EmailVerificationController::class, 'resendVerificationEmail']);
});
```

### Audit Logging

All email verification attempts are logged for security monitoring:

```php
// Check verification attempts
$verificationLogs = AuditLog::getLogsByAction('email_verification');
```

## Frontend Integration

### React Example

```javascript
const sendVerificationEmail = async () => {
    try {
        const response = await fetch('/api/v1/email/verify/send', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Verification email sent!');
        } else {
            alert('Error sending verification email');
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const verifyEmail = async (token) => {
    try {
        const response = await fetch('/api/v1/email/verify/confirm', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ token }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Email verified successfully!');
            // Redirect or update UI
        } else {
            alert('Invalid or expired token');
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const checkVerificationStatus = async () => {
    try {
        const response = await fetch('/api/v1/email/verify/status', {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            return data.data.is_verified;
        }
    } catch (error) {
        console.error('Error:', error);
    }
    
    return false;
};
```

### Vue.js Example

```javascript
// Email verification methods
methods: {
    async sendVerification() {
        try {
            const response = await this.$http.post('/api/v1/email/verify/send', {}, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.$toast.success('Verification email sent!');
            }
        } catch (error) {
            this.$toast.error('Error sending verification email');
        }
    },
    
    async verifyEmail() {
        try {
            const response = await this.$http.post('/api/v1/email/verify/confirm', {
                token: this.token
            });
            
            if (response.data.success) {
                this.$toast.success('Email verified successfully!');
                this.$router.push('/dashboard');
            }
        } catch (error) {
            this.$toast.error('Invalid or expired token');
        }
    },
    
    async checkStatus() {
        try {
            const response = await this.$http.get('/api/v1/email/verify/status', {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.isVerified = response.data.data.is_verified;
            }
        } catch (error) {
            console.error('Error checking status:', error);
        }
    }
}
```

## Maintenance

### Cleanup Expired Tokens

```bash
# Manual cleanup
php artisan kaely:cleanup-tokens --type=email-verification

# Automated cleanup (daily at 2:00 AM)
# Already configured in the service provider
```

### Monitoring

```php
// Get email verification statistics
$stats = EmailVerificationService::getStats();

// Monitor verification rates
$verificationRate = AuditService::getVerificationRate();
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

3. **Verification not required**
   - Set `KAELY_AUTH_EMAIL_VERIFICATION_REQUIRED=true` in `.env`
   - Apply middleware to protected routes

4. **Frontend URL issues**
   - Set `KAELY_AUTH_EMAIL_VERIFICATION_FRONTEND_URL` in `.env`
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

This will log all email verification requests for debugging purposes.

## Best Practices

### Security

1. **Always verify email ownership** before allowing account creation
2. **Use HTTPS** for all verification links
3. **Implement rate limiting** to prevent abuse
4. **Log all verification attempts** for security monitoring
5. **Set appropriate expiration times** (24 hours recommended)

### User Experience

1. **Clear messaging** about verification requirements
2. **Resend functionality** for expired tokens
3. **Progress indicators** during verification process
4. **Helpful error messages** for failed verifications
5. **Mobile-friendly** email templates

### Performance

1. **Use queue jobs** for email sending
2. **Cache verification status** to reduce database queries
3. **Clean up expired tokens** regularly
4. **Monitor verification rates** and success rates 