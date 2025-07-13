# Session Management

The KaelyAuth package provides comprehensive session management capabilities that allow users to view, manage, and control their active sessions across multiple devices.

## Features

- **Multi-device Support**: Track sessions across different devices and browsers
- **Session Security**: Monitor and revoke suspicious sessions
- **Activity Tracking**: Real-time session activity monitoring
- **Security Reports**: Detailed security analysis and recommendations
- **Audit Logging**: Complete audit trail of session activities
- **Automatic Cleanup**: Scheduled cleanup of expired sessions

## Configuration

### Basic Configuration

```php
// config/kaely-auth.php
'sessions' => [
    'enabled' => env('KAELY_AUTH_SESSION_MANAGEMENT_ENABLED', true),
    'lifetime_hours' => env('KAELY_AUTH_SESSION_LIFETIME', 24 * 30), // 30 days
    'max_active_sessions' => env('KAELY_AUTH_MAX_ACTIVE_SESSIONS', 5),
    'track_activity' => env('KAELY_AUTH_TRACK_SESSION_ACTIVITY', true),
    'auto_cleanup' => env('KAELY_AUTH_AUTO_CLEANUP_SESSIONS', true),
],
```

### Environment Variables

```env
KAELY_AUTH_SESSION_MANAGEMENT_ENABLED=true
KAELY_AUTH_SESSION_LIFETIME=720
KAELY_AUTH_MAX_ACTIVE_SESSIONS=5
KAELY_AUTH_TRACK_SESSION_ACTIVITY=true
KAELY_AUTH_AUTO_CLEANUP_SESSIONS=true
```

## API Endpoints

### Get Active Sessions

```http
GET /api/v1/sessions
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "token_id": "token-123",
            "device_name": "Chrome on Windows",
            "ip_address": "192.168.1.100",
            "user_agent": "Mozilla/5.0...",
            "last_activity_at": "2024-01-15T10:30:00.000000Z",
            "expires_at": "2024-02-15T10:30:00.000000Z",
            "is_active": true,
            "created_at": "2024-01-15T10:30:00.000000Z"
        }
    ]
}
```

### Get Session Statistics

```http
GET /api/v1/sessions/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "active_sessions": 3,
        "total_sessions": 5,
        "expired_sessions": 2
    }
}
```

### Get Security Report

```http
GET /api/v1/sessions/security-report
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "active_sessions": 3,
        "unique_ips": 2,
        "unique_devices": 2,
        "suspicious_sessions": 0,
        "security_score": 85,
        "recommendations": [
            "Consider logging out from unused devices"
        ]
    }
}
```

### Revoke Specific Session

```http
DELETE /api/v1/sessions/revoke/{tokenId}
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Session revoked successfully"
}
```

### Revoke Other Sessions

```http
DELETE /api/v1/sessions/revoke-others
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Revoked 2 other sessions",
    "data": {
        "sessions_revoked": 2
    }
}
```

### Revoke All Sessions

```http
DELETE /api/v1/sessions/revoke-all
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Revoked 3 sessions",
    "data": {
        "sessions_revoked": 3
    }
}
```

### Force Logout All Devices

```http
POST /api/v1/sessions/force-logout-all
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "message": "Logged out from 3 devices",
    "data": {
        "sessions_revoked": 3
    }
}
```

## Usage Examples

### Using the Service

```php
use Kaely\Auth\Services\SessionManagementService;

class SessionController extends Controller
{
    protected SessionManagementService $sessionManagementService;

    public function __construct(SessionManagementService $sessionManagementService)
    {
        $this->sessionManagementService = $sessionManagementService;
    }

    public function getSessions()
    {
        $user = Auth::user();
        $sessions = $this->sessionManagementService->getActiveSessions($user->id);
        
        return response()->json(['sessions' => $sessions]);
    }

    public function revokeSession(Request $request)
    {
        $tokenId = $request->input('token_id');
        $success = $this->sessionManagementService->revokeSession($tokenId);
        
        if ($success) {
            return response()->json(['message' => 'Session revoked']);
        }
        
        return response()->json(['message' => 'Session not found'], 404);
    }

    public function getSecurityReport()
    {
        $user = Auth::user();
        $report = $this->sessionManagementService->getSecurityReport($user->id);
        
        return response()->json(['report' => $report]);
    }
}
```

### Using the Model

```php
use Kaely\Auth\Models\UserSession;

// Get active sessions for user
$sessions = UserSession::getActiveSessions($userId);

// Get session statistics
$stats = UserSession::getSessionStats($userId);

// Revoke all sessions for user
$revokedCount = UserSession::revokeAllSessions($userId);

// Revoke other sessions (keep current)
$currentToken = 'current-token-id';
$revokedCount = UserSession::revokeOtherSessions($userId, $currentToken);

// Check if user has too many sessions
if (UserSession::hasTooManySessions($userId)) {
    // Handle too many sessions
}

// Get suspicious sessions
$suspiciousSessions = UserSession::getSuspiciousSessions($userId);
```

## Security Features

### Session Security

- **Automatic Expiration**: Sessions expire after configured time
- **Activity Tracking**: Monitor last activity for each session
- **IP Tracking**: Track IP addresses for security analysis
- **Device Identification**: Identify devices and browsers
- **Suspicious Activity Detection**: Detect unusual session patterns

### Security Monitoring

```php
// Get security alerts
$alerts = SessionManagementService::getSecurityAlerts();

// Monitor suspicious activity
$suspiciousSessions = SessionManagementService::getSuspiciousSessions($userId);

// Get security score
$securityScore = SessionManagementService::calculateSecurityScore($sessions);
```

## Frontend Integration

### React Example

```javascript
const getActiveSessions = async () => {
    try {
        const response = await fetch('/api/v1/sessions', {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            setSessions(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const revokeSession = async (tokenId) => {
    try {
        const response = await fetch(`/api/v1/sessions/revoke/${tokenId}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Session revoked successfully');
            getActiveSessions(); // Refresh sessions
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const revokeOtherSessions = async () => {
    try {
        const response = await fetch('/api/v1/sessions/revoke-others', {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`Revoked ${data.data.sessions_revoked} other sessions`);
            getActiveSessions(); // Refresh sessions
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const getSecurityReport = async () => {
    try {
        const response = await fetch('/api/v1/sessions/security-report', {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            setSecurityReport(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
};
```

### Vue.js Example

```javascript
// Session management methods
methods: {
    async getSessions() {
        try {
            const response = await this.$http.get('/api/v1/sessions', {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.sessions = response.data.data;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    },
    
    async revokeSession(tokenId) {
        try {
            const response = await this.$http.delete(`/api/v1/sessions/revoke/${tokenId}`, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.$toast.success('Session revoked successfully');
                this.getSessions(); // Refresh sessions
            }
        } catch (error) {
            this.$toast.error('Error revoking session');
        }
    },
    
    async revokeOtherSessions() {
        try {
            const response = await this.$http.delete('/api/v1/sessions/revoke-others', {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.$toast.success(`Revoked ${response.data.data.sessions_revoked} other sessions`);
                this.getSessions(); // Refresh sessions
            }
        } catch (error) {
            this.$toast.error('Error revoking sessions');
        }
    },
    
    async getSecurityReport() {
        try {
            const response = await this.$http.get('/api/v1/sessions/security-report', {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.securityReport = response.data.data;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
}
```

## Session Components

### React Session List Component

```jsx
import React, { useState, useEffect } from 'react';

const SessionList = () => {
    const [sessions, setSessions] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        getActiveSessions();
    }, []);

    const getActiveSessions = async () => {
        try {
            const response = await fetch('/api/v1/sessions', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                setSessions(data.data);
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            setLoading(false);
        }
    };

    const revokeSession = async (tokenId) => {
        try {
            const response = await fetch(`/api/v1/sessions/revoke/${tokenId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`,
                },
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert('Session revoked successfully');
                getActiveSessions(); // Refresh sessions
            }
        } catch (error) {
            console.error('Error:', error);
        }
    };

    if (loading) {
        return <div>Loading sessions...</div>;
    }

    return (
        <div className="session-list">
            <h2>Active Sessions</h2>
            {sessions.map(session => (
                <div key={session.id} className="session-item">
                    <div className="session-info">
                        <h3>{session.device_name || 'Unknown Device'}</h3>
                        <p>IP: {session.ip_address}</p>
                        <p>Last Activity: {new Date(session.last_activity_at).toLocaleString()}</p>
                        <p>Expires: {new Date(session.expires_at).toLocaleString()}</p>
                    </div>
                    <button 
                        onClick={() => revokeSession(session.token_id)}
                        className="revoke-btn"
                    >
                        Revoke Session
                    </button>
                </div>
            ))}
        </div>
    );
};

export default SessionList;
```

### Vue.js Session Management Component

```vue
<template>
    <div class="session-management">
        <h2>Session Management</h2>
        
        <!-- Security Report -->
        <div class="security-report" v-if="securityReport">
            <h3>Security Report</h3>
            <div class="report-grid">
                <div class="report-item">
                    <span class="label">Active Sessions:</span>
                    <span class="value">{{ securityReport.active_sessions }}</span>
                </div>
                <div class="report-item">
                    <span class="label">Security Score:</span>
                    <span class="value">{{ securityReport.security_score }}/100</span>
                </div>
                <div class="report-item">
                    <span class="label">Unique IPs:</span>
                    <span class="value">{{ securityReport.unique_ips }}</span>
                </div>
            </div>
            
            <div class="recommendations" v-if="securityReport.recommendations.length">
                <h4>Recommendations:</h4>
                <ul>
                    <li v-for="rec in securityReport.recommendations" :key="rec">
                        {{ rec }}
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Session List -->
        <div class="session-list">
            <h3>Active Sessions</h3>
            <div class="session-item" v-for="session in sessions" :key="session.id">
                <div class="session-info">
                    <h4>{{ session.device_name || 'Unknown Device' }}</h4>
                    <p><strong>IP:</strong> {{ session.ip_address }}</p>
                    <p><strong>Last Activity:</strong> {{ formatDate(session.last_activity_at) }}</p>
                    <p><strong>Expires:</strong> {{ formatDate(session.expires_at) }}</p>
                </div>
                <div class="session-actions">
                    <button @click="revokeSession(session.token_id)" class="btn-danger">
                        Revoke
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="session-actions">
            <button @click="revokeOtherSessions" class="btn-warning">
                Revoke Other Sessions
            </button>
            <button @click="revokeAllSessions" class="btn-danger">
                Revoke All Sessions
            </button>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return {
            sessions: [],
            securityReport: null,
            loading: false
        }
    },
    
    async mounted() {
        await this.getSessions();
        await this.getSecurityReport();
    },
    
    methods: {
        async getSessions() {
            try {
                const response = await this.$http.get('/api/v1/sessions', {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                
                if (response.data.success) {
                    this.sessions = response.data.data;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },
        
        async getSecurityReport() {
            try {
                const response = await this.$http.get('/api/v1/sessions/security-report', {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                
                if (response.data.success) {
                    this.securityReport = response.data.data;
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },
        
        async revokeSession(tokenId) {
            if (confirm('Are you sure you want to revoke this session?')) {
                try {
                    const response = await this.$http.delete(`/api/v1/sessions/revoke/${tokenId}`, {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    });
                    
                    if (response.data.success) {
                        this.$toast.success('Session revoked successfully');
                        await this.getSessions();
                    }
                } catch (error) {
                    this.$toast.error('Error revoking session');
                }
            }
        },
        
        async revokeOtherSessions() {
            if (confirm('Are you sure you want to revoke all other sessions?')) {
                try {
                    const response = await this.$http.delete('/api/v1/sessions/revoke-others', {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    });
                    
                    if (response.data.success) {
                        this.$toast.success(`Revoked ${response.data.data.sessions_revoked} other sessions`);
                        await this.getSessions();
                    }
                } catch (error) {
                    this.$toast.error('Error revoking sessions');
                }
            }
        },
        
        async revokeAllSessions() {
            if (confirm('Are you sure you want to revoke ALL sessions? You will be logged out.')) {
                try {
                    const response = await this.$http.delete('/api/v1/sessions/revoke-all', {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    });
                    
                    if (response.data.success) {
                        this.$toast.success(`Revoked ${response.data.data.sessions_revoked} sessions`);
                        // Redirect to login
                        this.$router.push('/login');
                    }
                } catch (error) {
                    this.$toast.error('Error revoking sessions');
                }
            }
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleString();
        }
    }
}
</script>

<style scoped>
.session-management {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.security-report {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin: 15px 0;
}

.report-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: white;
    border-radius: 4px;
}

.session-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    margin-bottom: 10px;
}

.session-info h4 {
    margin: 0 0 10px 0;
}

.session-info p {
    margin: 5px 0;
    font-size: 14px;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 10px;
}

.session-actions {
    margin-top: 20px;
}
</style>
```

## Maintenance

### Cleanup Expired Sessions

```bash
# Manual cleanup
php artisan kaely:cleanup-tokens --type=sessions

# Automated cleanup (daily at 2:00 AM)
# Already configured in the service provider
```

### Monitoring

```php
// Get session statistics
$stats = SessionManagementService::getSessionStats($userId);

// Monitor security threats
$threats = SessionManagementService::monitorSecurityThreats();
```

## Troubleshooting

### Common Issues

1. **Sessions not tracking**
   - Check if session management is enabled
   - Verify middleware is applied
   - Check database connection

2. **Sessions not expiring**
   - Verify session lifetime configuration
   - Check if cleanup command is running
   - Ensure proper timezone settings

3. **Security alerts not working**
   - Check security configuration
   - Verify audit logging is enabled
   - Monitor security thresholds

### Debug Mode

```php
// Enable debug mode
'debug' => [
    'enabled' => true,
    'log_requests' => true,
],
```

This will log all session management requests for debugging purposes. 