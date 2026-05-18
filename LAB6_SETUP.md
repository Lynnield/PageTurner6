# PageTurner Laboratory Activity 6 - Implementation Guide

## Overview

This is a production-grade Laravel 11 e-commerce bookstore system with enterprise features including:

- Real-time Notification System
- Event-driven Architecture
- Import/Export System
- Automated Backups
- Comprehensive Audit Logging
- Advanced API Rate Limiting
- GDPR-compliant Data Exports
- Job Queue System
- Scheduled Tasks

## Architecture

### Directory Structure

```
app/
  ├── Events/          # Domain events
  ├── Listeners/       # Event listeners (queued)
  ├── Jobs/            # Queued jobs
  ├── Services/        # Business logic
  ├── Http/
  │   ├── Controllers/ # Request handlers
  │   ├── Requests/    # Form validation
  │   └── Middleware/  # Request/response middleware
  ├── Models/          # Eloquent models
  ├── Notifications/   # Mail/database notifications
  └── Policies/        # Authorization
resources/views/
  ├── notifications/   # Notification views
  ├── admin/           # Admin panel
  └── components/      # Reusable components
database/
  ├── migrations/      # Schema changes
  ├── seeders/         # Test data
  └── factories/       # Model factories
```

## Setup Instructions

### 1. Initial Setup

```bash
# Install dependencies
composer install
npm install

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed test data
php artisan db:seed --class=TestDataSeeder

# Build frontend assets
npm run build
```

### 2. Queue Configuration

```bash
# Configure for your environment in .env
QUEUE_CONNECTION=database  # or redis

# Start queue worker (development)
php artisan queue:work

# Or use supervisor in production
```

### 3. Scheduler Configuration

```bash
# Add to system crontab
* * * * * cd /path/to/pageturner && php artisan schedule:run >> /dev/null 2>&1
```

## Core Features

### Part 1: Notification Bell System

**Features:**
- Real-time notification dropdown
- Unread count badge
- Mark as read functionality
- Notification index page with pagination
- AJAX polling (30-second intervals)

**Routes:**
- `GET /notifications` - View all notifications
- `GET /api/notifications/latest` - Get latest 5 (AJAX)
- `GET /api/notifications/unread-count` - Get count (AJAX)
- `POST /api/notifications/{id}/read` - Mark as read
- `POST /api/notifications/read-all` - Mark all as read
- `DELETE /api/notifications/{id}` - Delete notification

### Part 2: Event System Wiring

**Events:**
- `OrderPlaced` - Triggered when checkout completes
- `OrderStatusChanged` - When admin updates order status
- `ReviewSubmitted` - When customer submits review
- `BackupFailed` - When backup fails
- `BackupSucceeded` - When backup completes
- `ImportCompleted` - When import finishes
- `ExportCompleted` - When export finishes
- `TwoFactorEnabled` - When 2FA is enabled
- `TwoFactorDisabled` - When 2FA is disabled

**Listeners:** All listeners are queued and implement `ShouldQueue`

### Part 3: Import/Export System

**Book Import:**
```bash
POST /admin/books/import
- file: CSV or XLSX
- mode: create|update|upsert
```

**Book Export:**
```bash
POST /admin/books/export
- format: csv|xlsx|pdf
- columns: array of field names
- category: optional filter
- price_min, price_max: optional filters
```

**Features:**
- Validation with detailed error reporting
- Chunked processing (1000 rows)
- Progress tracking
- Large exports queued automatically (>10k rows)
- Supports skip/update/upsert modes

### Part 4: Backup System

**Automated Backups:**
- Daily at 2:00 AM
- Database + files + configs
- 7 daily, 4 weekly, 12 monthly retention

**Manual Backup:**
```
POST /admin/backup/run
```

**Monitoring:**
- Backup status in admin dashboard
- Failed backup alerts
- Disk usage tracking

### Part 5: Scheduled Tasks

All tasks use `withoutOverlapping()` and callbacks:

- `backup:run` - Daily 02:00
- `backup:clean` - Daily 03:00
- `order:cleanup-pending` - Hourly
- `session:cleanup` - Daily
- `log:rotate` - Weekly
- `report:generate-daily` - Daily 06:00
- `notification:prune` - Weekly
- `audit:archive` - Monthly

### Part 6: Audit Logging

**Tracked Events:**
- User login/logout
- Failed login attempts
- Password changes
- 2FA enable/disable
- CRUD operations
- Permission changes
- Import/export operations
- Backup operations

**Metadata Captured:**
- IP address
- User agent
- Request URL
- HTTP method
- Timestamp
- Changes diff

**Routes:**
- `GET /admin/audits` - View audit logs
- `GET /admin/audits/{id}` - View details
- `GET /admin/audits/export` - Export as CSV

### Part 7: API Rate Limiting

**Tiers:**
| Tier | Requests/min | Use Case |
|------|---|---|
| Public | 30 | Unauthenticated |
| Standard | 60 | Regular users |
| Premium | 300 | Premium customers |
| Admin | 1000 | Administrators |

**Headers:**
- `X-RateLimit-Limit` - Total requests allowed
- `X-RateLimit-Remaining` - Requests left
- `X-RateLimit-Reset` - Unix timestamp of reset
- `Retry-After` - Seconds to wait (429 response)

**Response:**
```json
{
  "message": "Too many requests. Please try again later.",
  "retry_after": 45
}
```

### Part 8: API Transformations

**Middleware:** `TransformApiResponseMiddleware`

**Features:**
- Snake case → camelCase conversion
- Field filtering: `?fields=id,title,price`
- ETag support for caching
- 304 Not Modified responses

### Part 9: Database Optimization

**Indexes Added:**
- `isbn` (unique)
- `category_id`
- `user_id`
- `created_at`

**Configuration:**
```php
config('pageturner.database')
```

**Services:**
- `DatabaseOptimizationService`
- Query statistics
- N+1 detection
- Slow query logging

### Part 10: Admin Dashboard

**Widgets:**
- System health status
- Import/export status
- Queue health
- API usage statistics
- Audit summary
- Backup disk usage

**Route:** `GET /admin/dashboard`

### Part 11: User Dashboard

**Features:**
- Export personal data (GDPR)
- Order history export
- Reading history
- Notification center
- Security activity log

**Routes:**
- `GET /customer/dashboard`
- `GET /customer/export/data`
- `GET /customer/export/orders`
- `GET /customer/export/reading-history`

### Part 12: Testing

**Test Coverage:**
- Notification system tests
- Event dispatch tests
- Rate limiting tests
- Service unit tests
- Import/export tests

**Run Tests:**
```bash
php artisan test

# Specific test file
php artisan test tests/Feature/NotificationSystemTest.php

# With coverage
php artisan test --coverage
```

## Configuration

### Environment Variables

```env
# Queue
QUEUE_CONNECTION=database

# Rate Limiting
API_RATE_LIMIT_PUBLIC=30
API_RATE_LIMIT_STANDARD=60
API_RATE_LIMIT_PREMIUM=300
API_RATE_LIMIT_ADMIN=1000

# Backup
BACKUP_ENABLED=true

# Audit
AUDIT_ENABLED=true
AUDIT_ARCHIVE_AFTER_DAYS=365

# Database
DB_QUERY_CACHING=false
DB_READ_WRITE_SPLIT=false
```

### Configuration Files

Main config: `config/pageturner.php`

## Services

### NotificationService
```php
use App\Services\NotificationService;

// Get unread count
$count = NotificationService::getUnreadCount($user);

// Get paginated notifications
$notifications = NotificationService::getNotifications($user, 15);

// Mark as read
NotificationService::markAsRead($notificationId);

// Mark all as read
NotificationService::markAllAsRead($user);
```

### AuditService
```php
use App\Services\AuditService;

// Log audit event
AuditService::log('action', 'description', $user, 'Model', $modelId, $changes);

// Log security event
AuditService::logSecurityEvent('2fa_enabled', 'Email method');

// Get audit trail
$audits = AuditService::getTrail(['action' => 'login']);
```

### BackupService
```php
use App\Services\BackupService;

// Run backup
BackupService::runBackup();

// Clean old backups
BackupService::cleanOldBackups();

// Get backup list
$backups = BackupService::getBackupList();

// Get disk usage
$usage = BackupService::getBackupDiskUsage();
```

### DashboardService
```php
use App\Services\DashboardService;

// System health
$health = DashboardService::getSystemHealth();

// Import/export status
$status = DashboardService::getImportExportStatus();

// Queue health
$queue = DashboardService::getQueueHealth();
```

## Best Practices

1. **Always use transactions** for multi-step operations
2. **Queue heavy operations** - imports, exports, backups
3. **Enable query logging** in development to catch N+1 queries
4. **Use eager loading** - `with()` to prevent N+1
5. **Cache expensive queries** - use `DatabaseOptimizationService::withCache()`
6. **Validate imports thoroughly** - detailed error reporting
7. **Log all security events** - use `AuditService`
8. **Monitor queue jobs** - watch for failures
9. **Test rate limits** - ensure tier separation
10. **Backup regularly** - verify restore capability

## Troubleshooting

### Queue Jobs Not Processing
```bash
# Check queue:
php artisan queue:work --daemon

# Debug job:
php artisan queue:failed

# Retry failed jobs:
php artisan queue:retry all
```

### Events Not Firing
```bash
# Verify EventServiceProvider is loaded in config/app.php
# Check Providers array includes EventServiceProvider

# Debug events:
php artisan tinker
> Event::listen('*', fn($name) => dump($name));
```

### Rate Limiting Issues
```bash
# Clear rate limit cache:
php artisan cache:clear

# Check cache configuration:
php artisan config:show cache
```

### Audit Logs Not Recording
```bash
# Verify auditable models include trait:
use OwenIt\Auditing\Auditable;

# Check audit config:
php artisan config:show audit
```

## Production Checklist

- [ ] Set `APP_DEBUG=false`
- [ ] Configure queue connection (Redis recommended)
- [ ] Set up supervisor for queue worker
- [ ] Add cron job for scheduler
- [ ] Configure backup storage (S3 recommended)
- [ ] Enable HTTPS
- [ ] Configure CORS properly
- [ ] Set rate limits appropriately
- [ ] Monitor log files
- [ ] Set up monitoring alerts
- [ ] Test backup restore process
- [ ] Configure logging rotation
- [ ] Set up error tracking (Sentry)
- [ ] Enable database query logging monitoring

## API Documentation

Full API endpoints available in `routes/web.php`

### Notification Endpoints

| Method | Endpoint | Auth | Rate | Purpose |
|--------|----------|------|------|---------|
| GET | /notifications | Yes | 60 | List all notifications |
| GET | /api/notifications/latest | Yes | 60 | Get latest 5 |
| GET | /api/notifications/unread-count | Yes | 60 | Get count |
| POST | /api/notifications/{id}/read | Yes | 60 | Mark as read |
| POST | /api/notifications/read-all | Yes | 60 | Mark all read |
| DELETE | /api/notifications/{id} | Yes | 60 | Delete |

## Contributing

Follow Laravel 11 conventions:
- Use PSR-12 style guide
- Write tests for new features
- Document new services
- Use meaningful commit messages

## License

Proprietary - PageTurner Inc.
