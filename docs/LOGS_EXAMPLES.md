# KaelyAuth Logs Examples

## 📊 Audit Logs Examples

### User Authentication Events
```json
{
  "id": 1,
  "user_id": 123,
  "action": "user.login",
  "description": "User logged in successfully",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "session_id": "abc123def456",
  "tenant_id": 1,
  "metadata": {
    "login_method": "email",
    "two_factor_enabled": false,
    "location": "New York, US"
  },
  "created_at": "2024-01-15T10:30:00Z"
}
```

### Failed Login Attempts
```json
{
  "id": 2,
  "user_id": null,
  "action": "user.login_failed",
  "description": "Failed login attempt with invalid credentials",
  "ip_address": "192.168.1.101",
  "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X)",
  "session_id": null,
  "tenant_id": 1,
  "metadata": {
    "attempted_email": "user@example.com",
    "reason": "invalid_credentials",
    "location": "Los Angeles, US"
  },
  "created_at": "2024-01-15T10:35:00Z"
}
```

### OAuth Authentication
```json
{
  "id": 3,
  "user_id": 124,
  "action": "oauth.login",
  "description": "User logged in via Google OAuth",
  "ip_address": "192.168.1.102",
  "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
  "session_id": "xyz789abc123",
  "tenant_id": 1,
  "metadata": {
    "provider": "google",
    "oauth_id": "google_123456789",
    "email": "user@gmail.com",
    "first_login": true
  },
  "created_at": "2024-01-15T11:00:00Z"
}
```

### Permission Changes
```json
{
  "id": 4,
  "user_id": 125,
  "action": "permission.granted",
  "description": "Admin granted 'edit_users' permission to user",
  "ip_address": "192.168.1.103",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
  "session_id": "def456ghi789",
  "tenant_id": 1,
  "metadata": {
    "admin_user_id": 1,
    "target_user_id": 126,
    "permission": "edit_users",
    "role": "editor"
  },
  "created_at": "2024-01-15T11:15:00Z"
}
```

### Role Assignments
```json
{
  "id": 5,
  "user_id": 127,
  "action": "role.assigned",
  "description": "User assigned to 'manager' role",
  "ip_address": "192.168.1.104",
  "user_agent": "Mozilla/5.0 (Linux; Android 11)",
  "session_id": "jkl012mno345",
  "tenant_id": 1,
  "metadata": {
    "admin_user_id": 1,
    "target_user_id": 127,
    "role": "manager",
    "previous_role": "user"
  },
  "created_at": "2024-01-15T11:30:00Z"
}
```

### Password Changes
```json
{
  "id": 6,
  "user_id": 128,
  "action": "password.changed",
  "description": "User changed their password",
  "ip_address": "192.168.1.105",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
  "session_id": "pqr678stu901",
  "tenant_id": 1,
  "metadata": {
    "change_method": "current_password",
    "password_strength": "strong",
    "two_factor_required": false
  },
  "created_at": "2024-01-15T12:00:00Z"
}
```

### Account Deletions
```json
{
  "id": 7,
  "user_id": 129,
  "action": "user.deleted",
  "description": "Admin deleted user account",
  "ip_address": "192.168.1.106",
  "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
  "session_id": "vwx234yza567",
  "tenant_id": 1,
  "metadata": {
    "admin_user_id": 1,
    "deleted_user_email": "deleted@example.com",
    "reason": "inactive_account",
    "data_retention_days": 30
  },
  "created_at": "2024-01-15T12:30:00Z"
}
```

## 🔄 Session Activity Logs Examples

### Session Started
```json
{
  "id": 1,
  "user_id": 123,
  "session_id": "abc123def456",
  "action": "session.started",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "tenant_id": 1,
  "metadata": {
    "login_method": "email",
    "device_type": "desktop",
    "browser": "Chrome",
    "os": "Windows 10"
  },
  "created_at": "2024-01-15T10:30:00Z"
}
```

### Page Visits
```json
{
  "id": 2,
  "user_id": 123,
  "session_id": "abc123def456",
  "action": "page.visited",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "tenant_id": 1,
  "metadata": {
    "url": "/api/users",
    "method": "GET",
    "response_time": 245,
    "status_code": 200
  },
  "created_at": "2024-01-15T10:32:00Z"
}
```

### API Calls
```json
{
  "id": 3,
  "user_id": 123,
  "session_id": "abc123def456",
  "action": "api.called",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "tenant_id": 1,
  "metadata": {
    "endpoint": "/api/users/456",
    "method": "PUT",
    "request_size": 1024,
    "response_size": 512,
    "response_time": 180,
    "status_code": 200
  },
  "created_at": "2024-01-15T10:35:00Z"
}
```

### Session Extended
```json
{
  "id": 4,
  "user_id": 123,
  "session_id": "abc123def456",
  "action": "session.extended",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "tenant_id": 1,
  "metadata": {
    "extension_reason": "user_activity",
    "new_expiry": "2024-01-15T18:30:00Z",
    "previous_expiry": "2024-01-15T16:30:00Z"
  },
  "created_at": "2024-01-15T10:40:00Z"
}
```

### Session Expired
```json
{
  "id": 5,
  "user_id": 123,
  "session_id": "abc123def456",
  "action": "session.expired",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "tenant_id": 1,
  "metadata": {
    "expiry_reason": "timeout",
    "session_duration": "8 hours",
    "last_activity": "2024-01-15T18:25:00Z"
  },
  "created_at": "2024-01-15T18:30:00Z"
}
```

### Session Terminated
```json
{
  "id": 6,
  "user_id": 123,
  "session_id": "abc123def456",
  "action": "session.terminated",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
  "tenant_id": 1,
  "metadata": {
    "termination_reason": "user_logout",
    "session_duration": "6 hours",
    "total_requests": 45
  },
  "created_at": "2024-01-15T16:30:00Z"
}
```

## 🔐 OAuth Logs Examples

### OAuth Login Initiated
```json
{
  "id": 1,
  "user_id": null,
  "provider": "google",
  "action": "oauth.initiated",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
  "tenant_id": 1,
  "metadata": {
    "state": "random_state_string",
    "redirect_uri": "https://app.example.com/oauth/google/callback",
    "scopes": ["email", "profile"]
  },
  "created_at": "2024-01-15T10:30:00Z"
}
```

### OAuth Login Successful
```json
{
  "id": 2,
  "user_id": 124,
  "provider": "google",
  "action": "oauth.successful",
  "ip_address": "192.168.1.100",
  "user_agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64)",
  "tenant_id": 1,
  "metadata": {
    "oauth_id": "google_123456789",
    "email": "user@gmail.com",
    "name": "John Doe",
    "picture": "https://lh3.googleusercontent.com/photo.jpg",
    "is_new_user": true
  },
  "created_at": "2024-01-15T10:31:00Z"
}
```

### OAuth Login Failed
```json
{
  "id": 3,
  "user_id": null,
  "provider": "facebook",
  "action": "oauth.failed",
  "ip_address": "192.168.1.101",
  "user_agent": "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)",
  "tenant_id": 1,
  "metadata": {
    "error": "access_denied",
    "error_description": "User denied access",
    "state": "random_state_string"
  },
  "created_at": "2024-01-15T10:35:00Z"
}
```

### OAuth Account Linked
```json
{
  "id": 4,
  "user_id": 125,
  "provider": "github",
  "action": "oauth.linked",
  "ip_address": "192.168.1.102",
  "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
  "tenant_id": 1,
  "metadata": {
    "oauth_id": "github_987654321",
    "username": "johndoe",
    "email": "john@github.com",
    "linked_to_existing": true
  },
  "created_at": "2024-01-15T11:00:00Z"
}
```

### OAuth Account Unlinked
```json
{
  "id": 5,
  "user_id": 125,
  "provider": "linkedin",
  "action": "oauth.unlinked",
  "ip_address": "192.168.1.102",
  "user_agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)",
  "tenant_id": 1,
  "metadata": {
    "oauth_id": "linkedin_456789123",
    "reason": "user_request",
    "linked_accounts_remaining": 2
  },
  "created_at": "2024-01-15T11:15:00Z"
}
```

## 📊 Dashboard Examples

### Audit Dashboard Summary
```json
{
  "period": "last_30_days",
  "total_events": 15420,
  "unique_users": 1250,
  "failed_logins": 342,
  "oauth_logins": 892,
  "permission_changes": 45,
  "role_assignments": 23,
  "account_deletions": 5,
  "top_actions": [
    {"action": "user.login", "count": 8234},
    {"action": "page.visited", "count": 4567},
    {"action": "api.called", "count": 2341},
    {"action": "oauth.login", "count": 892},
    {"action": "user.logout", "count": 386}
  ],
  "security_alerts": [
    {
      "type": "multiple_failed_logins",
      "user_id": 123,
      "ip_address": "192.168.1.100",
      "count": 15,
      "timeframe": "1 hour"
    },
    {
      "type": "unusual_activity",
      "user_id": 456,
      "ip_address": "203.0.113.1",
      "description": "Login from new location"
    }
  ]
}
```

### Session Activity Summary
```json
{
  "period": "last_24_hours",
  "active_sessions": 234,
  "total_sessions": 456,
  "average_session_duration": "2 hours 15 minutes",
  "peak_concurrent_users": 89,
  "session_terminations": 123,
  "expired_sessions": 45,
  "top_pages": [
    {"url": "/api/users", "visits": 1234},
    {"url": "/api/dashboard", "visits": 987},
    {"url": "/api/reports", "visits": 654},
    {"url": "/api/settings", "visits": 432}
  ],
  "device_distribution": [
    {"device": "desktop", "percentage": 65},
    {"device": "mobile", "percentage": 25},
    {"device": "tablet", "percentage": 10}
  ]
}
```

## 🔍 Query Examples

### Find Failed Login Attempts
```sql
SELECT * FROM audit_logs 
WHERE action = 'user.login_failed' 
AND created_at >= NOW() - INTERVAL 24 HOUR
ORDER BY created_at DESC;
```

### Find User Session Activity
```sql
SELECT * FROM session_activities 
WHERE user_id = 123 
AND created_at >= NOW() - INTERVAL 7 DAY
ORDER BY created_at DESC;
```

### Find OAuth Logins by Provider
```sql
SELECT * FROM oauth_logs 
WHERE provider = 'google' 
AND action = 'oauth.successful'
AND created_at >= NOW() - INTERVAL 30 DAY;
```

### Find Permission Changes
```sql
SELECT * FROM audit_logs 
WHERE action IN ('permission.granted', 'permission.revoked')
AND created_at >= NOW() - INTERVAL 7 DAY
ORDER BY created_at DESC;
```

### Find Unusual Activity
```sql
SELECT user_id, ip_address, COUNT(*) as attempts
FROM audit_logs 
WHERE action = 'user.login_failed'
AND created_at >= NOW() - INTERVAL 1 HOUR
GROUP BY user_id, ip_address
HAVING COUNT(*) > 5;
``` 