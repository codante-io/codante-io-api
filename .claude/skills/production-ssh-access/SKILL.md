---
name: Production SSH Access
description: Provides guidance and utilities for securely accessing the Codante production server via SSH. Use this when you need to connect to the production server, run commands, check logs, manage services, troubleshoot issues, or work with Laravel and Supervisor.
---

# Production SSH Access Skill

## Overview

This skill guides you through accessing and working with the Codante.io API production server hosted on Digital Ocean.

## Server Information

- **Host**: 216.238.103.47
- **User**: robertotcestari
- **OS**: Linux (Ubuntu/Debian)
- **Web Server**: nginx
- **Application Server**: Laravel Octane (RoadRunner on port 8089)
- **Backend**: PHP 8.1+
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis
- **Application Root**: `/var/www/codante-io-api/current` (symlink to active release)
- **Deployment Structure**: Capistrano-style with `current`, `releases/`, and `shared/` directories
- **Process Manager**: Supervisor (manages Octane and queue jobs)

## Quick Access

### Connect to Production Server

```bash
ssh robertotcestari@216.238.103.47
```

### Common SSH Commands

**List nginx sites:**
```bash
ssh robertotcestari@216.238.103.47 "ls /etc/nginx/sites-enabled/"
```

**Check nginx configuration:**
```bash
ssh robertotcestari@216.238.103.47 "sudo nginx -t"
```

**Check service status:**
```bash
ssh robertotcestari@216.238.103.47 "sudo supervisorctl status codante-octane"
ssh robertotcestari@216.238.103.47 "systemctl status nginx"
ssh robertotcestari@216.238.103.47 "systemctl status mysql"
ssh robertotcestari@216.238.103.47 "systemctl status redis-server"
```

**Check Supervisor status:**
```bash
ssh robertotcestari@216.238.103.47 "sudo supervisorctl status"
```

## File Transfer

WARNING: do not transfer files - this is used only for emergency debugging. To change files in the production server, use the deployment pipeline.

### Copy files from production to local:
```bash
scp robertotcestari@216.238.103.47:/path/on/server /local/path
```

### Copy files from local to production:
```bash
scp /local/path robertotcestari@216.238.103.47:/path/on/server
```

## Production Application Paths

- **Application Current**: `/var/www/codante-io-api/current`
- **Releases**: `/var/www/codante-io-api/releases/` (numbered directories: 1, 2, 3, 4...)
- **Shared Files**: `/var/www/codante-io-api/shared/` (persistent data between releases)
- **Logs Directory**: `/var/www/codante-io-api/current/storage/logs/`
- **Public**: `/var/www/codante-io-api/current/public` (served by nginx)
- **.env Config**: `/var/www/codante-io-api/shared/.env`

## Log Access in Production

Codante uses multiple logging channels with all logs stored in `/var/www/codante-io-api/current/storage/logs/`.

### Main Application Logs

**Laravel Main Log:**
```bash
# View live logs (follow mode)
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/laravel.log"

# View last 100 lines
ssh robertotcestari@216.238.103.47 "tail -100 /var/www/codante-io-api/current/storage/logs/laravel.log"

# Get file size and info
ssh robertotcestari@216.238.103.47 "ls -lh /var/www/codante-io-api/current/storage/logs/laravel.log"
```

### Specialized Log Channels

**Deprecation Logs** (PHP deprecation warnings):
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/deprecations.log"
```

**Security Logs** (daily files, kept for 14 days):
```bash
ssh robertotcestari@216.238.103.47 "ls -lh /var/www/codante-io-api/current/storage/logs/security*.log"
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/security-2025-10-22.log"
```

**Payment Logs** (Pagarme integration):
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/payments.log"
```

**Discord Logs** (Discord integration):
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/discord.log"
```

### nginx Logs

**nginx access logs:**
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/log/nginx/access.log"
```

**nginx error logs:**
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/log/nginx/error.log"
```

**Site-specific error logs:**
```bash
ssh robertotcestari@216.238.103.47 "ls -lh /var/log/nginx/*.log"
```

### System Logs - Services

**Octane service logs:**
```bash
ssh robertotcestari@216.238.103.47 "journalctl -u codante-octane -n 100 -f"
```

**nginx service logs:**
```bash
ssh robertotcestari@216.238.103.47 "journalctl -u nginx -n 100"
```

**MySQL service logs:**
```bash
ssh robertotcestari@216.238.103.47 "journalctl -u mysql -n 100"
```

**Redis logs:**
```bash
ssh robertotcestari@216.238.103.47 "journalctl -u redis-server -n 100"
```

**Supervisor logs:**
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/log/supervisor/supervisord.log"
```

### View All Logs in Directory

```bash
# List all log files with sizes
ssh robertotcestari@216.238.103.47 "ls -lh /var/www/codante-io-api/current/storage/logs/"

# Count lines in each log file
ssh robertotcestari@216.238.103.47 "wc -l /var/www/codante-io-api/current/storage/logs/*"

# Get total size
ssh robertotcestari@216.238.103.47 "du -sh /var/www/codante-io-api/current/storage/logs/"
```

### Search and Filter Logs

**Find errors in Laravel logs:**
```bash
ssh robertotcestari@216.238.103.47 "grep 'ERROR\|Exception' /var/www/codante-io-api/current/storage/logs/laravel.log | head -50"
```

**Find errors from specific date:**
```bash
ssh robertotcestari@216.238.103.47 "grep '\[2025-10-22' /var/www/codante-io-api/current/storage/logs/laravel.log | grep 'ERROR'"
```

**Search for specific errors:**
```bash
ssh robertotcestari@216.238.103.47 "grep 'payment\|webhook\|queue\|notification' /var/www/codante-io-api/current/storage/logs/laravel.log"
```

### Log Format and Structure

Laravel logs use Monolog format:
```
[YYYY-MM-DD HH:MM:SS] CHANNEL.LEVEL: MESSAGE {"context": "data"}
```

Example:
```
[2025-10-22 14:32:15] laravel.ERROR: Exception occurred {"exception":"[object] (Exception(code: 0): User not found at /app/User.php:123)"...}
```

### Download Logs Locally

**Copy single log file:**
```bash
scp robertotcestari@216.238.103.47:/var/www/codante-io-api/current/storage/logs/laravel.log ./laravel-backup.log
```

**Copy all logs:**
```bash
scp -r robertotcestari@216.238.103.47:/var/www/codante-io-api/current/storage/logs/ ./production-logs/
```

**Copy security logs:**
```bash
scp robertotcestari@216.238.103.47:/var/www/codante-io-api/current/storage/logs/security*.log ./
```

### Manage Large Log Files

**Check log sizes:**
```bash
ssh robertotcestari@216.238.103.47 "du -h /var/www/codante-io-api/current/storage/logs/"
```

**Rotate/Clear logs via Artisan:**
```bash
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan log:clear"
```

**Manually rotate specific log:**
```bash
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current/storage/logs && mv laravel.log laravel.log.$(date +%Y%m%d) && gzip laravel.log.* && ls -lh"
```

### Real-time Log Monitoring

**Watch multiple logs simultaneously:**
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/laravel.log /var/log/nginx/error.log"
```

**Monitor with grep filter:**
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/laravel.log | grep -E 'ERROR|Exception|Warning'"
```

**Watch payment logs in real-time:**
```bash
ssh robertotcestari@216.238.103.47 "tail -f /var/www/codante-io-api/current/storage/logs/payments.log"
```

## Supervisor Process Management

Codante uses **Supervisor** to manage Laravel Octane and queue workers.

### Check Supervisor Status

```bash
# Status of all programs
ssh robertotcestari@216.238.103.47 "sudo supervisorctl status"

# Status of Octane specifically
ssh robertotcestari@216.238.103.47 "sudo supervisorctl status codante-octane"

# Status of queue workers (if configured)
ssh robertotcestari@216.238.103.47 "sudo supervisorctl status codante-worker"
```

### View Supervisor Logs

```bash
# Main supervisor log
ssh robertotcestari@216.238.103.47 "tail -f /var/log/supervisor/supervisord.log"

# Octane logs
ssh robertotcestari@216.238.103.47 "tail -f /var/log/supervisor/codante-octane.log"

# Queue worker logs (if configured)
ssh robertotcestari@216.238.103.47 "tail -f /var/log/supervisor/codante-worker.log"
```

### Restart Supervisor Programs

```bash
# Restart Octane
ssh robertotcestari@216.238.103.47 "sudo supervisorctl restart codante-octane"

# Restart all programs
ssh robertotcestari@216.238.103.47 "sudo supervisorctl restart all"

# Start/stop Octane
ssh robertotcestari@216.238.103.47 "sudo supervisorctl start codante-octane"
ssh robertotcestari@216.238.103.47 "sudo supervisorctl stop codante-octane"
```

### Monitor Queue Jobs

```bash
# View active queue jobs
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan queue:failed"

# Monitor queue in real-time
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan queue:monitor redis:default --timeout=300"

# Retry failed jobs
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan queue:retry all"
```

### Check Queue Configuration

```bash
ssh robertotcestari@216.238.103.47 "cat /etc/supervisor/conf.d/codante-worker.conf"
```

## Common Production Tasks

### Check Disk Space

```bash
ssh robertotcestari@216.238.103.47 "df -h"
```

### Monitor CPU/Memory

```bash
ssh robertotcestari@216.238.103.47 "top -b -n 1 | head -20"
```

### Check Process Memory Usage

```bash
# Octane processes
ssh robertotcestari@216.238.103.47 "ps aux | grep octane"

# nginx processes
ssh robertotcestari@216.238.103.47 "ps aux | grep nginx"

# Check memory by Octane workers
ssh robertotcestari@216.238.103.47 "ps aux | grep octane | awk '{print \$2, \$4, \$6, \$11}'"
```

### Restart Services

```bash
# Restart Octane (via Supervisor)
ssh robertotcestari@216.238.103.47 "sudo supervisorctl restart codante-octane"

# Restart nginx
ssh robertotcestari@216.238.103.47 "sudo systemctl restart nginx"

# Restart MySQL
ssh robertotcestari@216.238.103.47 "sudo systemctl restart mysql"

# Restart Redis
ssh robertotcestari@216.238.103.47 "sudo systemctl restart redis-server"

# Restart Supervisor (and all managed programs)
ssh robertotcestari@216.238.103.47 "sudo systemctl restart supervisor"

# Restart all services
ssh robertotcestari@216.238.103.47 "sudo systemctl restart nginx mysql redis-server && sudo supervisorctl restart all"
```

### Clear Cache

```bash
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan cache:clear && php artisan optimize:clear"
```

### Database Operations

```bash
# Run migrations
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan migrate"

# Run seeders
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan db:seed"

# Check database connection
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && php artisan tinker"
```

### Check Current Deployment

```bash
# See current release
ssh robertotcestari@216.238.103.47 "ls -l /var/www/codante-io-api/current"

# Check recent commits
ssh robertotcestari@216.238.103.47 "cd /var/www/codante-io-api/current && git log -5 --oneline"

# Check recent releases
ssh robertotcestari@216.238.103.47 "ls -la /var/www/codante-io-api/releases/ | tail -5"
```

### Deploy New Release

When deploying via GitHub Actions + Deployer:
1. Merge to `main` branch
2. GitHub Actions triggers automated deployment
3. Deployer handles:
   - Code checkout to new release directory
   - Dependency installation
   - Cache clearing
   - Database migrations
   - Asset compilation
   - Service restarts (PHP-FPM, nginx, Supervisor)

Check deployment status:
```bash
ssh robertotcestari@216.238.103.47 "ls -la /var/www/codante-io-api/releases/ | tail -5"
```

### Health Check

```bash
# Quick API health check
ssh robertotcestari@216.238.103.47 "curl -s http://127.0.0.1/health"

# Detailed status
ssh robertotcestari@216.238.103.47 "curl -s http://127.0.0.1/api/status | head -20"

# Check if PHP-FPM is responding
ssh robertotcestari@216.238.103.47 "curl -s -o /dev/null -w '%{http_code}' http://127.0.0.1/"
```

## Security Considerations

1. **SSH Keys**: Ensure your SSH key is added to `~/.ssh/authorized_keys` on the server
2. **sudo Access**: Use `sudo` for privileged commands (requires password or sudo setup)
3. **Environment Variables**: Never expose sensitive data in logs or console output
4. **Log Files**: Check logs for errors, security warnings, and performance issues
5. **Backups**: Always ensure backups exist before making changes to production
6. **Worker Cache**: Be aware that Supervisor-managed workers may cache code; restart after major changes
7. **Log Retention**: Security logs kept for 14 days; archive important logs locally
8. **Shared Config**: `.env` and other shared config in `/var/www/codante-io-api/shared/` - changes survive deployments
9. **Queue Jobs**: Failed jobs are retained; monitor and clean up periodically

## Troubleshooting

### Connection Issues

If you can't connect:
1. Verify SSH key is configured: `ssh-keyscan 216.238.103.47`
2. Check your SSH config: `~/.ssh/config`
3. Test connectivity: `ping 216.238.103.47`

### Permission Denied

If you get "Permission denied":
1. Verify the username is `robertotcestari`
2. Check SSH key permissions: `chmod 600 ~/.ssh/id_rsa`
3. Ensure public key is on server: `cat ~/.ssh/id_rsa.pub`

### Service Issues

If services are not running:
1. Check service status: `sudo systemctl status SERVICE_NAME`
2. View service logs: `sudo journalctl -u SERVICE_NAME -n 50`
3. Restart service: `sudo systemctl restart SERVICE_NAME`

### Octane Not Responding

If Octane isn't responding:
1. Check if Octane is running: `sudo supervisorctl status codante-octane`
2. View recent logs: `tail -f /var/log/supervisor/codante-octane.log`
3. Restart Octane: `sudo supervisorctl restart codante-octane`
4. Check if bound to port 8089: `sudo netstat -tlnp | grep 8089` or `sudo ss -tlnp | grep 8089`
5. Check Octane service via journalctl: `journalctl -u supervisor -n 100 | grep octane`

### Supervisor Worker Issues

If queue workers are not processing:
1. Check supervisor status: `sudo supervisorctl status`
2. View worker logs: `tail -f /var/log/supervisor/codante-worker.log`
3. Restart workers: `sudo supervisorctl restart codante-worker`
4. Check for failed jobs: `cd /var/www/codante-io-api/current && php artisan queue:failed`

### High Memory Usage

If Octane is consuming too much memory:
1. Check current memory usage: `free -h`
2. Check Octane memory: `ps aux | grep octane | awk '{print $2, $4, $6, $11}'`
3. Current config should trigger garbage collection at 50MB
4. Restart Octane: `sudo supervisorctl restart codante-octane`
5. Monitor with: `watch -n 1 'ps aux | grep octane'`
6. Review logs for memory leak patterns in `/var/log/supervisor/codante-octane.log`

### Queue Job Issues

If queue jobs are failing or not processing:
1. Check queue status: `cd /var/www/codante-io-api/current && php artisan queue:failed`
2. View worker logs: `tail -f /var/log/supervisor/codante-worker.log`
3. Retry failed jobs: `cd /var/www/codante-io-api/current && php artisan queue:retry all`
4. Clear failed jobs: `cd /var/www/codante-io-api/current && php artisan queue:clear`
5. Monitor queue: `cd /var/www/codante-io-api/current && php artisan queue:monitor redis:default`

### Log Files Growing Too Large

If log files are consuming too much disk space:
1. Check size: `du -sh /var/www/codante-io-api/current/storage/logs/`
2. Clear logs: `cd /var/www/codante-io-api/current && php artisan log:clear`
3. Archive old logs: `gzip /var/www/codante-io-api/current/storage/logs/*.log.old`
4. Download and remove: `scp ... && ssh ... rm`

### nginx 502 Bad Gateway

If you see 502 errors in nginx:
1. Check if Octane is running: `sudo supervisorctl status codante-octane`
2. Check nginx error log: `tail -f /var/log/nginx/error.log`
3. Test Octane is bound to port 8089: `curl http://127.0.0.1:8089`
4. Restart Octane: `sudo supervisorctl restart codante-octane`
5. Check Octane logs: `tail -f /var/log/supervisor/codante-octane.log`

### Payment Processing Issues

If Pagarme webhooks are failing:
1. Check payment logs: `tail -f /var/www/codante-io-api/current/storage/logs/payments.log`
2. Verify webhook configuration in `.env`
3. Check failed queue jobs: `cd /var/www/codante-io-api/current && php artisan queue:failed`
4. Retry webhook jobs: `cd /var/www/codante-io-api/current && php artisan queue:retry all`

## When to Use This Skill

Use this skill when you need to:
- ✅ Connect to the production server
- ✅ Debug production issues
- ✅ Access and analyze production logs
- ✅ Monitor real-time log streams
- ✅ Check server logs and metrics
- ✅ Manage services and processes
- ✅ Work with Supervisor and queue jobs
- ✅ Transfer files to/from production
- ✅ Run maintenance commands
- ✅ Troubleshoot deployment issues
- ✅ Monitor performance and resource usage
- ✅ Manage Laravel queue workers
- ✅ Handle payment and webhook issues

## Related Resources

- CLAUDE.md deployment section for CI/CD information
- Server management documentation
- Deployer configuration for automated deployments
- Supervisor documentation: http://supervisord.org/
- Laravel Queue documentation: https://laravel.com/docs/queues
- Laravel Commands documentation: https://laravel.com/docs/artisan
