# Changelog

All notable changes to the TruePaws plugin will be documented in this file.

## [Unreleased]

### New Features
#### Breed Management System
- **Breed Dropdown**: Changed breed field from text input to dropdown menu on animal forms
- **Default Breeds**: Added 40 default breeds (20 top dog breeds + 20 top cat breeds) automatically initialized on plugin activation
- **Custom Breeds**: Users can add custom breeds in Settings > Breeds tab
- **Breed Settings Page**: Enhanced breeds management interface with species labels and helpful descriptions

### Bug Fixes
- **Fixed microchip_id UNIQUE constraint error**: Empty microchip_id fields now properly save as NULL instead of empty strings, preventing duplicate key errors
- **Dashboard cache invalidation**: Dashboard statistics now automatically refresh when animals are created, updated, or deleted
- **Fixed dashboard litters query**: Corrected query to use `actual_whelping_date` instead of non-existent `whelped` column
- **Better error messages**: Duplicate microchip ID errors now show which animal is using that ID

### UI/UX Improvements
- **Animal Form**: Breed field now shows a dropdown with all available breeds instead of free-text input
- **Settings Page**: Improved breeds management section with better visual feedback and species indicators

### Database Changes
- Default breeds are automatically initialized when the plugin is activated
- Breeds include species metadata (dog/cat) for better organization
- Microchip_id now properly handles NULL values for empty fields

## [1.1.0] - 2026-02-13

### Security Fixes
- **CRITICAL**: Removed hardcoded Freemius secret key from source code - now must be defined in `wp-config.php`
- **HIGH**: Fixed XSS vulnerability by adding DOMPurify sanitization for AI-generated content
- **HIGH**: Added rate limiting to public inquiry endpoint (3 submissions per IP per hour)
- **MEDIUM**: Removed debug logging endpoints from production code
- **MEDIUM**: Removed hardcoded file paths from debug logging

### Performance Improvements
- **Fixed N+1 query issue** in `get_animals()` endpoint by using SQL JOINs for parent names
- **Added caching** to dashboard statistics (5-minute cache)
- **Added caching** to pedigree data (24-hour cache with invalidation on parent changes)
- **Added pagination** to litters endpoint (20 items per page by default)
- **Optimized CSS** - Added minification for production builds
- **Improved database queries** - All dashboard queries now properly use prepared statements

### Code Quality
- **Added database transactions** to `whelp_litter()` function to prevent partial data on failures
- **Replaced browser alerts** with professional toast notification system
- **Added React Error Boundaries** to gracefully handle component crashes
- **Updated webpack configuration** for production optimization with CSS extraction and minification
- **Removed all console.log statements** from production code paths

### New Features

#### AI-Powered Features (Gemini Integration)
- **AI Health Alerts**: New endpoint `/animals/{id}/ai-health-alerts` analyzes event history and provides proactive health warnings
- **AI Litter Name Generator**: New endpoint `/litters/{id}/suggest-names` generates themed puppy names based on breed and parents
- **Enhanced AI Care Advice**: Existing feature now with improved caching (30-day cache)

#### Automated Notifications
- **Email Notifications System**: Daily WP-Cron job sends automated emails for:
  - Upcoming whelping dates (7-day advance notice)
  - Vaccination reminders based on animal age (6, 9, 12, 16 weeks, and annual boosters)
- **Configurable**: Uses breeder email from settings

#### PDF Generation
- **Pedigree Certificate PDF**: New endpoint `/animals/{id}/generate-pedigree-pdf` generates professional 3-generation pedigree certificates
- **Enhanced handover documents**: Existing feature with improved formatting

#### UI/UX Improvements
- **Toast Notification System**: Modern, non-intrusive notifications replace browser alerts
- **Error Boundaries**: Graceful error handling with user-friendly error messages
- **Loading States**: Improved loading indicators throughout the app

### Developer Experience
- **Improved build process**: Separate development and production webpack configs
- **Better error messages**: More descriptive error responses from API endpoints
- **Cache invalidation**: Automatic cache clearing on data updates

### Breaking Changes
- **Freemius Secret Key**: Must now be defined in `wp-config.php` as `WP_FS__truepaws_SECRET_KEY`
- **Litters API**: Now returns paginated results with `pagination` object in response

### Dependencies
- Added: `dompurify` (3.2.2) - For XSS protection
- Added: `mini-css-extract-plugin` (2.9.2) - For CSS optimization
- Added: `css-minimizer-webpack-plugin` (7.0.0) - For CSS minification

### Database Changes
- No schema changes in this release
- Added transient caching for dashboard stats and pedigree data
- Added rate limiting tracking via WordPress transients

### API Changes

#### New Endpoints
- `GET /truepaws/v1/animals/{id}/ai-health-alerts` - Get AI-powered health alerts
- `GET /truepaws/v1/litters/{id}/suggest-names` - Get AI-generated name suggestions
- `POST /truepaws/v1/animals/{id}/generate-pedigree-pdf` - Generate pedigree certificate PDF

#### Modified Endpoints
- `GET /truepaws/v1/animals` - Now uses optimized JOIN query (no breaking changes to response format)
- `GET /truepaws/v1/litters` - Now returns paginated results
- `POST /truepaws/v1/inquiries` - Now rate-limited (returns 429 status when limit exceeded)
- `GET /truepaws/v1/dashboard/stats` - Now cached for 5 minutes

### Installation Notes

#### For New Installations
1. Install and activate the plugin as normal
2. Define the Freemius secret key in `wp-config.php`:
   ```php
   define('WP_FS__truepaws_SECRET_KEY', 'your-secret-key-here');
   ```
3. Configure Gemini API key in Settings for AI features
4. Set breeder email in Settings for automated notifications

#### For Upgrades from 1.0.5
1. **IMPORTANT**: Add the Freemius secret key to `wp-config.php` before upgrading
2. Run `npm install` in the plugin directory to install new dependencies
3. Run `npm run build` to rebuild assets with new optimizations
4. Clear any WordPress object cache if using persistent caching
5. Test the following:
   - Dashboard loads correctly (cache is working)
   - Litters page pagination works
   - Public inquiry form (rate limiting)
   - AI features (if API key configured)

### Known Issues
- None at this time

### Upgrade Path
This is a minor version upgrade with no database migrations required. All changes are backward compatible except for the Freemius secret key requirement.

### Testing Performed
- ✅ Security: XSS prevention, rate limiting, SQL injection prevention
- ✅ Performance: N+1 query fixes, caching, pagination
- ✅ Features: AI endpoints, email notifications, PDF generation
- ✅ UI: Toast notifications, error boundaries, loading states
- ✅ Compatibility: WordPress 5.0+, PHP 7.4+

### Credits
- Security audit and improvements
- Performance optimization
- AI feature integration with Google Gemini
- UI/UX enhancements

---

## [1.0.5] - Previous Release
- Initial stable release
- Core animal management features
- Litter tracking and whelping wizard
- Contact management
- Basic PDF generation
- Pedigree tracking
- Event timeline
- Public shortcodes
