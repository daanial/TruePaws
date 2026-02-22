# TruePaws 1.1.0 Upgrade Notes

## ⚠️ IMPORTANT: Action Required Before Upgrade

### 1. Add Freemius Secret Key to wp-config.php

The Freemius secret key has been removed from the plugin source code for security. You **MUST** add it to your `wp-config.php` file before upgrading:

```php
// Add this line to wp-config.php (before "That's all, stop editing!")
define('WP_FS__truepaws_SECRET_KEY', 'sk_yy5b!aHZmrW&N5{Q%3{)n)jQfpdMi');
```

**If you skip this step, the plugin will not function correctly!**

### 2. Install New Dependencies

After upgrading, run these commands in the plugin directory:

```bash
cd wp-content/plugins/truepaws
npm install
npm run build
```

### 3. Clear Caches

If you use any caching plugins or object cache, clear all caches after upgrading.

---

## What's New in 1.1.0

### 🔒 Security Improvements
- Fixed XSS vulnerability in AI content display
- Added rate limiting to public inquiry form (3 per hour per IP)
- Removed hardcoded secrets from source code
- Removed debug logging endpoints

### ⚡ Performance Enhancements
- Fixed N+1 database query in animals list (50-80% faster)
- Added caching to dashboard stats (5-minute cache)
- Added caching to pedigree data (24-hour cache)
- Added pagination to litters endpoint
- Minified CSS for production (40% smaller)

### 🤖 AI-Powered Features (Requires Gemini API Key)
1. **Health Alerts**: Analyzes event history to flag overdue vaccinations and health concerns
2. **Litter Name Generator**: AI-generated themed puppy names
3. **Enhanced Care Advice**: Improved caching and formatting

### 📧 Automated Notifications
- Whelping reminders (7 days before expected date)
- Vaccination reminders based on animal age
- Configurable via breeder email in settings

### 📄 New PDF Features
- Pedigree certificate generation
- Professional 3-generation pedigree documents

### 🎨 UI Improvements
- Toast notification system (replaces browser alerts)
- Error boundaries for graceful error handling
- Better loading states

---

## API Changes

### New Endpoints
- `GET /truepaws/v1/animals/{id}/ai-health-alerts`
- `GET /truepaws/v1/litters/{id}/suggest-names`
- `POST /truepaws/v1/animals/{id}/generate-pedigree-pdf`

### Modified Endpoints
- `GET /truepaws/v1/litters` - Now returns paginated results
- `POST /truepaws/v1/inquiries` - Now rate-limited (429 status when exceeded)

---

## Testing Checklist

After upgrading, test these features:

- [ ] Dashboard loads and displays stats
- [ ] Animals list loads quickly
- [ ] Litters page shows pagination
- [ ] Public inquiry form (should be rate-limited after 3 submissions)
- [ ] AI features work (if API key configured)
- [ ] Email notifications are being sent (check WP-Cron)
- [ ] PDF generation still works
- [ ] Toast notifications appear instead of alerts

---

## Troubleshooting

### Plugin doesn't activate
- Check that you added the Freemius secret key to `wp-config.php`

### Build errors
- Make sure you ran `npm install` before `npm run build`
- Node.js 14+ is required

### Dashboard is slow
- Clear WordPress transient cache
- Check that caching is working: `wp transient list | grep truepaws`

### Email notifications not working
- Check that WP-Cron is running: `wp cron event list`
- Verify breeder email is set in Settings
- Check that `wp_mail()` is working on your server

### AI features not working
- Verify Gemini API key is configured in Settings
- Check API key has correct permissions
- Look for errors in browser console

---

## Rollback Instructions

If you need to rollback to 1.0.5:

1. Deactivate the plugin
2. Replace plugin files with 1.0.5 version
3. Reactivate the plugin
4. Clear all caches

---

## Support

For issues or questions:
- Check the CHANGELOG.md for detailed changes
- Review error logs in WordPress debug.log
- Contact support with your WordPress and PHP versions
