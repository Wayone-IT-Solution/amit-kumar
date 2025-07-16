# Subscription Auto-Expiration Cron Job Setup

## Overview
This guide explains how to set up automatic subscription expiration using cron jobs.

## Cron Job Commands

### 1. Daily Auto-Expiration (Recommended)
Run this command daily to check and expire all expired subscriptions:

```bash
# Run daily at 2:00 AM
0 2 * * * /usr/bin/php /path/to/your/project/admin/inc/auto_subscription_end.php

# Example for XAMPP on Windows (using Task Scheduler)
# Create a batch file (.bat) with:
# cd /d C:\xampp\htdocs\amit-kumar
# php admin\inc\auto_subscription_end.php
```

### 2. Hourly Check (Optional)
For more frequent checks, run every hour:

```bash
# Run every hour
0 * * * * /usr/bin/php /path/to/your/project/admin/inc/auto_subscription_end.php
```

### 3. Specific 30-Day Subscription Check
To specifically check 30-day subscriptions:

```bash
# Run daily at 3:00 AM
0 3 * * * /usr/bin/php /path/to/your/project/admin/inc/auto_subscription_end.php?action=30day
```

## Manual Execution

### Via Web Browser
You can manually trigger the auto-expiration by visiting:
- `http://your-domain.com/admin/inc/auto_subscription_end.php?run_auto_end=1`

### Via Command Line
```bash
cd /path/to/your/project
php admin/inc/auto_subscription_end.php
```

## Windows Task Scheduler Setup

1. Open Task Scheduler (taskschd.msc)
2. Create Basic Task
3. Set trigger (daily at 2:00 AM)
4. Action: Start a program
5. Program: `C:\xampp\php\php.exe`
6. Arguments: `C:\xampp\htdocs\amit-kumar\admin\inc\auto_subscription_end.php`

## Linux/Unix Cron Setup

1. Open crontab: `crontab -e`
2. Add the cron job line
3. Save and exit

## Testing the Cron Job

### Test via Web Interface
1. Go to Admin Panel → Subscription List
2. Click "Run Auto Expiration" button
3. Check the results

### Test via Command Line
```bash
php admin/inc/auto_subscription_end.php
```

## Logging

The system automatically logs all subscription activities in the `subscription_logs` table:

- `created`: When subscription is created
- `activated`: When subscription becomes active
- `expired`: When subscription expires automatically
- `manually_expired`: When admin manually ends subscription
- `setup_30day`: When 30-day subscription is set up

## Monitoring

### Check Expired Subscriptions
```sql
SELECT 
    so.id,
    so.order_code,
    u.fullname as customer_name,
    s.title as subscription_title,
    so.created_at,
    so.expiry_date,
    DATEDIFF(so.expiry_date, NOW()) as days_remaining
FROM subscription_orders so
JOIN users u ON so.user_id = u.id
JOIN subscriptions s ON so.subscription_id = s.id
WHERE so.status = 'active'
AND so.expiry_date <= NOW();
```

### Check 30-Day Subscriptions
```sql
SELECT 
    so.id,
    so.order_code,
    u.fullname as customer_name,
    so.created_at,
    so.expiry_date,
    DATEDIFF(so.expiry_date, NOW()) as days_remaining
FROM subscription_orders so
JOIN users u ON so.user_id = u.id
JOIN subscriptions s ON so.subscription_id = s.id
WHERE so.status = 'active'
AND s.valid_days = 30
AND so.expiry_date <= NOW();
```

## Troubleshooting

### Common Issues

1. **Permission Denied**
   - Ensure PHP has read/write permissions to the project directory
   - Check file permissions: `chmod 755 admin/inc/auto_subscription_end.php`

2. **Database Connection Error**
   - Verify database credentials in `inc/db.php`
   - Check if database server is running

3. **Cron Job Not Running**
   - Check cron service: `service cron status`
   - Verify cron logs: `tail -f /var/log/cron`
   - Test manually first

4. **No Subscriptions Expired**
   - Check if there are any active subscriptions
   - Verify expiry dates are in the past
   - Check subscription status

### Debug Mode

Add debug logging to the auto-expiration script:

```php
// Add this at the beginning of auto_subscription_end.php
error_log("Auto subscription end script started at " . date('Y-m-d H:i:s'));
```

## Security Considerations

1. **File Permissions**
   - Restrict access to the auto-expiration script
   - Use proper file permissions (644 or 755)

2. **Database Security**
   - Use prepared statements (already implemented)
   - Limit database user permissions

3. **Logging**
   - Monitor logs for unusual activity
   - Rotate log files regularly

## Performance Optimization

1. **Database Indexes**
   - Ensure proper indexes on `subscription_orders` table
   - Index on `status`, `created_at`, `expiry_date`

2. **Batch Processing**
   - Process subscriptions in batches for large datasets
   - Add LIMIT clause to queries

3. **Caching**
   - Cache frequently accessed subscription data
   - Use Redis or Memcached if available 