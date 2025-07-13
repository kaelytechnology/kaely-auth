# Audit Logging

The KaelyAuth package provides comprehensive audit logging capabilities that track all user activities, system events, and security-related actions for compliance and security monitoring.

## Features

- **Complete Activity Tracking**: Log all user actions and system events
- **Security Monitoring**: Detect and alert on suspicious activities
- **Compliance Support**: Detailed audit trails for regulatory compliance
- **Real-time Alerts**: Immediate notifications for security threats
- **Advanced Reporting**: Comprehensive audit reports and analytics
- **Data Export**: Export audit data in multiple formats
- **Retention Management**: Configurable data retention policies

## Configuration

### Basic Configuration

```php
// config/kaely-auth.php
'audit' => [
    'enabled' => env('KAELY_AUTH_AUDIT_ENABLED', true),
    'retention_days' => env('KAELY_AUTH_AUDIT_RETENTION_DAYS', 90),
    'log_failed_attempts' => env('KAELY_AUTH_LOG_FAILED_ATTEMPTS', true),
    'log_successful_actions' => env('KAELY_AUTH_LOG_SUCCESSFUL_ACTIONS', true),
    'log_suspicious_activity' => env('KAELY_AUTH_LOG_SUSPICIOUS_ACTIVITY', true),
    'security_alerts' => [
        'enabled' => env('KAELY_AUTH_SECURITY_ALERTS_ENABLED', true),
        'failed_login_threshold' => env('KAELY_AUTH_FAILED_LOGIN_THRESHOLD', 5),
        'suspicious_ip_threshold' => env('KAELY_AUTH_SUSPICIOUS_IP_THRESHOLD', 3),
    ],
],
```

### Environment Variables

```env
KAELY_AUTH_AUDIT_ENABLED=true
KAELY_AUTH_AUDIT_RETENTION_DAYS=90
KAELY_AUTH_LOG_FAILED_ATTEMPTS=true
KAELY_AUTH_LOG_SUCCESSFUL_ACTIONS=true
KAELY_AUTH_LOG_SUSPICIOUS_ACTIVITY=true
KAELY_AUTH_SECURITY_ALERTS_ENABLED=true
KAELY_AUTH_FAILED_LOGIN_THRESHOLD=5
KAELY_AUTH_SUSPICIOUS_IP_THRESHOLD=3
```

## API Endpoints

### Get User Timeline

```http
GET /api/v1/audit/timeline?days=30
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
            "action": "login",
            "description": "User logged in successfully",
            "ip_address": "192.168.1.100",
            "status": "success",
            "created_at": "2024-01-15T10:30:00.000000Z"
        }
    ]
}
```

### Get User Activity Summary

```http
GET /api/v1/audit/summary?days=30
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_activities": 150,
        "success_count": 145,
        "failed_count": 5,
        "warning_count": 0,
        "success_rate": 96.67,
        "action_breakdown": {
            "login": 50,
            "logout": 45,
            "profile_updated": 30,
            "password_reset": 25
        },
        "last_activity": "2024-01-15T10:30:00.000000Z"
    }
}
```

### Get Audit Statistics

```http
GET /api/v1/audit/stats?days=30
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_logs": 1500,
        "success_logs": 1450,
        "failed_logs": 50,
        "success_rate": 96.67,
        "action_stats": [
            {
                "action": "login",
                "count": 500
            },
            {
                "action": "logout",
                "count": 450
            }
        ]
    }
}
```

### Get Security Alerts

```http
GET /api/v1/audit/security-alerts?hours=24
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "type": "high_failed_logins",
            "message": "High number of failed login attempts: 15",
            "severity": "high"
        },
        {
            "type": "suspicious_activity",
            "message": "Suspicious activity detected for user 123 from IP 192.168.1.100",
            "severity": "medium",
            "data": {
                "user_id": 123,
                "ip_address": "192.168.1.100",
                "failed_attempts": 8
            }
        }
    ]
}
```

### Generate Audit Report

```http
GET /api/v1/audit/report?days=30
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "period": "30 days",
        "summary": {
            "total_logs": 1500,
            "success_logs": 1450,
            "failed_logs": 50,
            "success_rate": 96.67
        },
        "activity_heatmap": {
            "2024-01-01": 50,
            "2024-01-02": 45,
            "2024-01-03": 60
        },
        "top_actions": [
            {
                "action": "login",
                "count": 500
            }
        ],
        "error_trends": {
            "2024-01-01": 2,
            "2024-01-02": 1,
            "2024-01-03": 3
        },
        "security_alerts": [],
        "security_threats": [],
        "generated_at": "2024-01-15T10:30:00.000000Z"
    }
}
```

### Export Audit Data

```http
GET /api/v1/audit/export?days=30&format=json
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "format": "json",
        "export": "[{\"id\":1,\"user_id\":1,\"action\":\"login\",...}]",
        "days": 30
    }
}
```

## Usage Examples

### Using the Service

```php
use Kaely\Auth\Services\AuditService;

class AuditController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function getUserTimeline(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);
        
        $timeline = $this->auditService->getUserTimeline($user->id, $days);
        
        return response()->json(['timeline' => $timeline]);
    }

    public function getSecurityAlerts()
    {
        $alerts = $this->auditService->getSecurityAlerts(24);
        
        return response()->json(['alerts' => $alerts]);
    }

    public function generateReport(Request $request)
    {
        $days = $request->input('days', 30);
        $report = $this->auditService->generateAuditReport($days);
        
        return response()->json(['report' => $report]);
    }
}
```

### Using the Model

```php
use Kaely\Auth\Models\AuditLog;

// Log a successful action
AuditLog::logSuccess(
    'login',
    'User logged in successfully',
    $user->id,
    ['ip' => request()->ip()],
    ['user_id' => $user->id]
);

// Log a failed action
AuditLog::logFailed(
    'login',
    'Failed login attempt',
    null,
    ['email' => $email, 'ip' => request()->ip()],
    'Invalid credentials'
);

// Log a warning
AuditLog::logWarning(
    'suspicious_activity',
    'Multiple failed login attempts detected',
    $user->id,
    ['ip' => request()->ip()],
    ['attempts' => 5]
);

// Get user logs
$userLogs = AuditLog::getUserLogs($userId, 50);

// Get logs by action
$loginLogs = AuditLog::getLogsByAction('login', 100);

// Get logs by status
$failedLogs = AuditLog::getLogsByStatus('failed', 50);

// Get logs in date range
$logs = AuditLog::getLogsInDateRange(
    now()->subDays(7),
    now()
);

// Get failed login attempts
$failedAttempts = AuditLog::getFailedLoginAttempts($userId, 24);

// Get suspicious activity
$suspiciousActivity = AuditLog::getSuspiciousActivity(1, 5);

// Get audit statistics
$stats = AuditLog::getAuditStats(30);
```

## Security Monitoring

### Security Alerts

```php
// Get security alerts for the last 24 hours
$alerts = AuditService::getSecurityAlerts(24);

foreach ($alerts as $alert) {
    switch ($alert['type']) {
        case 'high_failed_logins':
            // Handle high failed login attempts
            break;
        case 'suspicious_activity':
            // Handle suspicious activity
            break;
    }
}
```

### Security Threats

```php
// Monitor for security threats
$threats = AuditService::monitorSecurityThreats();

foreach ($threats as $threat) {
    switch ($threat['type']) {
        case 'brute_force':
            // Handle brute force attack
            break;
        case 'suspicious_activity':
            // Handle suspicious activity patterns
            break;
        case 'ip_attack':
            // Handle IP-based attacks
            break;
    }
}
```

## Frontend Integration

### React Example

```javascript
const getAuditTimeline = async (days = 30) => {
    try {
        const response = await fetch(`/api/v1/audit/timeline?days=${days}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            setTimeline(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const getSecurityAlerts = async () => {
    try {
        const response = await fetch('/api/v1/audit/security-alerts?hours=24', {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            setAlerts(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const generateReport = async (days = 30) => {
    try {
        const response = await fetch(`/api/v1/audit/report?days=${days}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            setReport(data.data);
        }
    } catch (error) {
        console.error('Error:', error);
    }
};

const exportAuditData = async (days = 30, format = 'json') => {
    try {
        const response = await fetch(`/api/v1/audit/export?days=${days}&format=${format}`, {
            headers: {
                'Authorization': `Bearer ${token}`,
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Download the exported data
            const blob = new Blob([data.data.export], { type: 'application/json' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `audit-report-${days}-days.${format}`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    } catch (error) {
        console.error('Error:', error);
    }
};
```

### Vue.js Example

```javascript
// Audit logging methods
methods: {
    async getTimeline() {
        try {
            const response = await this.$http.get(`/api/v1/audit/timeline?days=${this.days}`, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.timeline = response.data.data;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    },
    
    async getSecurityAlerts() {
        try {
            const response = await this.$http.get('/api/v1/audit/security-alerts?hours=24', {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.alerts = response.data.data;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    },
    
    async generateReport() {
        try {
            const response = await this.$http.get(`/api/v1/audit/report?days=${this.days}`, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                this.report = response.data.data;
            }
        } catch (error) {
            console.error('Error:', error);
        }
    },
    
    async exportData() {
        try {
            const response = await this.$http.get(`/api/v1/audit/export?days=${this.days}&format=${this.exportFormat}`, {
                headers: { 'Authorization': `Bearer ${this.token}` }
            });
            
            if (response.data.success) {
                // Handle export download
                this.downloadExport(response.data.data.export, response.data.data.format);
            }
        } catch (error) {
            console.error('Error:', error);
        }
    },
    
    downloadExport(data, format) {
        const blob = new Blob([data], { type: 'application/octet-stream' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `audit-report-${this.days}-days.${format}`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
}
```

## Audit Dashboard Components

### React Audit Dashboard

```jsx
import React, { useState, useEffect } from 'react';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend } from 'recharts';

const AuditDashboard = () => {
    const [report, setReport] = useState(null);
    const [alerts, setAlerts] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async () => {
        try {
            const [reportResponse, alertsResponse] = await Promise.all([
                fetch('/api/v1/audit/report?days=30', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
                }),
                fetch('/api/v1/audit/security-alerts?hours=24', {
                    headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` }
                })
            ]);

            const reportData = await reportResponse.json();
            const alertsData = await alertsResponse.json();

            if (reportData.success) {
                setReport(reportData.data);
            }

            if (alertsData.success) {
                setAlerts(alertsData.data);
            }
        } catch (error) {
            console.error('Error loading dashboard data:', error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) {
        return <div>Loading audit dashboard...</div>;
    }

    return (
        <div className="audit-dashboard">
            <h1>Audit Dashboard</h1>
            
            {/* Summary Cards */}
            <div className="summary-cards">
                <div className="card">
                    <h3>Total Activities</h3>
                    <p>{report?.summary?.total_logs || 0}</p>
                </div>
                <div className="card">
                    <h3>Success Rate</h3>
                    <p>{report?.summary?.success_rate || 0}%</p>
                </div>
                <div className="card">
                    <h3>Security Alerts</h3>
                    <p>{alerts.length}</p>
                </div>
            </div>

            {/* Activity Chart */}
            {report?.activity_heatmap && (
                <div className="chart-container">
                    <h3>Activity Over Time</h3>
                    <LineChart width={800} height={300} data={Object.entries(report.activity_heatmap).map(([date, count]) => ({ date, count }))}>
                        <CartesianGrid strokeDasharray="3 3" />
                        <XAxis dataKey="date" />
                        <YAxis />
                        <Tooltip />
                        <Legend />
                        <Line type="monotone" dataKey="count" stroke="#8884d8" />
                    </LineChart>
                </div>
            )}

            {/* Security Alerts */}
            {alerts.length > 0 && (
                <div className="alerts-container">
                    <h3>Security Alerts</h3>
                    {alerts.map((alert, index) => (
                        <div key={index} className={`alert alert-${alert.severity}`}>
                            <strong>{alert.type}:</strong> {alert.message}
                        </div>
                    ))}
                </div>
            )}

            {/* Top Actions */}
            {report?.top_actions && (
                <div className="top-actions">
                    <h3>Top Actions</h3>
                    <div className="actions-list">
                        {report.top_actions.map((action, index) => (
                            <div key={index} className="action-item">
                                <span className="action-name">{action.action}</span>
                                <span className="action-count">{action.count}</span>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default AuditDashboard;
```

### Vue.js Audit Dashboard

```vue
<template>
    <div class="audit-dashboard">
        <h1>Audit Dashboard</h1>
        
        <!-- Summary Cards -->
        <div class="summary-cards">
            <div class="card">
                <h3>Total Activities</h3>
                <p>{{ report?.summary?.total_logs || 0 }}</p>
            </div>
            <div class="card">
                <h3>Success Rate</h3>
                <p>{{ report?.summary?.success_rate || 0 }}%</p>
            </div>
            <div class="card">
                <h3>Security Alerts</h3>
                <p>{{ alerts.length }}</p>
            </div>
        </div>

        <!-- Activity Chart -->
        <div class="chart-container" v-if="report?.activity_heatmap">
            <h3>Activity Over Time</h3>
            <canvas ref="activityChart"></canvas>
        </div>

        <!-- Security Alerts -->
        <div class="alerts-container" v-if="alerts.length">
            <h3>Security Alerts</h3>
            <div 
                v-for="alert in alerts" 
                :key="alert.type"
                :class="`alert alert-${alert.severity}`"
            >
                <strong>{{ alert.type }}:</strong> {{ alert.message }}
            </div>
        </div>

        <!-- Top Actions -->
        <div class="top-actions" v-if="report?.top_actions">
            <h3>Top Actions</h3>
            <div class="actions-list">
                <div 
                    v-for="action in report.top_actions" 
                    :key="action.action"
                    class="action-item"
                >
                    <span class="action-name">{{ action.action }}</span>
                    <span class="action-count">{{ action.count }}</span>
                </div>
            </div>
        </div>

        <!-- Export Controls -->
        <div class="export-controls">
            <h3>Export Data</h3>
            <div class="export-form">
                <select v-model="exportDays">
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
                <select v-model="exportFormat">
                    <option value="json">JSON</option>
                    <option value="csv">CSV</option>
                </select>
                <button @click="exportData" class="btn-primary">
                    Export
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import Chart from 'chart.js/auto';

export default {
    data() {
        return {
            report: null,
            alerts: [],
            exportDays: 30,
            exportFormat: 'json',
            activityChart: null
        }
    },
    
    async mounted() {
        await this.loadDashboardData();
        this.initChart();
    },
    
    methods: {
        async loadDashboardData() {
            try {
                const [reportResponse, alertsResponse] = await Promise.all([
                    this.$http.get(`/api/v1/audit/report?days=30`, {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    }),
                    this.$http.get('/api/v1/audit/security-alerts?hours=24', {
                        headers: { 'Authorization': `Bearer ${this.token}` }
                    })
                ]);
                
                if (reportResponse.data.success) {
                    this.report = reportResponse.data.data;
                }
                
                if (alertsResponse.data.success) {
                    this.alerts = alertsResponse.data.data;
                }
            } catch (error) {
                console.error('Error loading dashboard data:', error);
            }
        },
        
        initChart() {
            if (this.report?.activity_heatmap) {
                const ctx = this.$refs.activityChart.getContext('2d');
                const data = Object.entries(this.report.activity_heatmap).map(([date, count]) => ({
                    date,
                    count
                }));
                
                this.activityChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map(item => item.date),
                        datasets: [{
                            label: 'Activities',
                            data: data.map(item => item.count),
                            borderColor: '#8884d8',
                            tension: 0.1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        },
        
        async exportData() {
            try {
                const response = await this.$http.get(`/api/v1/audit/export?days=${this.exportDays}&format=${this.exportFormat}`, {
                    headers: { 'Authorization': `Bearer ${this.token}` }
                });
                
                if (response.data.success) {
                    this.downloadExport(response.data.data.export, response.data.data.format);
                }
            } catch (error) {
                console.error('Error exporting data:', error);
            }
        },
        
        downloadExport(data, format) {
            const blob = new Blob([data], { type: 'application/octet-stream' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `audit-report-${this.exportDays}-days.${format}`;
            a.click();
            window.URL.revokeObjectURL(url);
        }
    }
}
</script>

<style scoped>
.audit-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    text-align: center;
}

.card h3 {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
}

.card p {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.chart-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.alerts-container {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.alert {
    padding: 10px 15px;
    margin-bottom: 10px;
    border-radius: 4px;
    border-left: 4px solid;
}

.alert-high {
    background: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.alert-medium {
    background: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.alert-low {
    background: #d1ecf1;
    border-color: #17a2b8;
    color: #0c5460;
}

.top-actions {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.actions-list {
    display: grid;
    gap: 10px;
}

.action-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.export-controls {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.export-form {
    display: flex;
    gap: 10px;
    align-items: center;
}

.btn-primary {
    background: #007bff;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>
```

## Maintenance

### Cleanup Old Logs

```bash
# Manual cleanup
php artisan kaely:cleanup-tokens --type=audit

# Generate audit report
php artisan kaely:audit-report --days=30 --format=json --output=audit-report.json
```

### Monitoring

```php
// Get audit statistics
$stats = AuditService::getAuditStats(30);

// Monitor security threats
$threats = AuditService::monitorSecurityThreats();
```

## Troubleshooting

### Common Issues

1. **Logs not being created**
   - Check if audit logging is enabled
   - Verify middleware is applied
   - Check database connection

2. **Performance issues**
   - Enable caching for audit data
   - Implement log rotation
   - Use database indexing

3. **Security alerts not working**
   - Check security configuration
   - Verify alert thresholds
   - Monitor alert delivery

### Debug Mode

```php
// Enable debug mode
'debug' => [
    'enabled' => true,
    'log_requests' => true,
],
```

This will log all audit requests for debugging purposes. 