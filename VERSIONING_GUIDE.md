# Versioning Guide for Adilisha STEM Lab

This guide explains how to manage application versioning using the Settings system in the admin panel.

## Overview

The versioning system allows you to:
- Track application versions (semantic versioning)
- Manage build numbers
- Control update requirements
- Set minimum supported versions
- Add release notes

## Accessing Version Settings

1. Log in to the admin panel
2. Navigate to **Settings** → **System Settings** in the sidebar
3. Click on the **Versioning** tab

## Version Fields Explained

### App Version (Required)
- **Format**: Semantic versioning (e.g., `1.0.0`, `2.1.3`)
- **Structure**: `MAJOR.MINOR.PATCH`
  - **MAJOR**: Breaking changes or major feature releases
  - **MINOR**: New features (backward compatible)
  - **PATCH**: Bug fixes and small improvements
- **Example**: `1.2.0` means version 1, minor release 2, patch 0

### Version Code
- **Purpose**: Used for mobile app stores (iOS/Android)
- **Format**: Can be any string (e.g., `v1.0.0`, `2024.12.15`)
- **Usage**: Displayed in app stores and internal tracking

### Build Number
- **Purpose**: Incremental number for each build/release
- **Format**: Integer (starts at 1, increments with each build)
- **Usage**: Track how many times the app has been built/released
- **Example**: If you release version 1.0.0, build 1, then fix a bug and release 1.0.1, build 2

### Minimum Supported Version
- **Purpose**: Define the oldest version that is still supported
- **Format**: Same as App Version (semantic versioning)
- **Usage**: Users with versions below this will be prompted to update
- **Example**: If set to `1.0.0`, users on `0.9.5` must update

### Version Notes / Release Notes
- **Purpose**: Document what's new in this version
- **Format**: Plain text (can include markdown-style formatting)
- **Best Practices**:
  - List new features
  - Document bug fixes
  - Mention breaking changes
  - Include migration instructions if needed

### Force Update
- **Purpose**: Require all users to update immediately
- **When to Use**:
  - Critical security patches
  - Breaking API changes
  - Database migrations required
  - Major bugs that affect functionality
- **Note**: When enabled, users cannot use older versions

## Versioning Workflow

### 1. Planning a New Release

Before updating version settings:

1. **Review Changes**: Document all changes since the last version
2. **Determine Version Type**:
   - **PATCH** (1.0.0 → 1.0.1): Bug fixes only
   - **MINOR** (1.0.0 → 1.1.0): New features, backward compatible
   - **MAJOR** (1.0.0 → 2.0.0): Breaking changes

3. **Update Build Number**: Increment by 1

### 2. Updating Version Settings

1. Go to **Settings** → **System Settings** → **Versioning** tab
2. Update the following fields:
   - **App Version**: Enter new version (e.g., `1.0.1`)
   - **Version Code**: Update if needed (e.g., `v1.0.1`)
   - **Build Number**: Increment (e.g., if last was 5, enter 6)
   - **Version Notes**: Add release notes
   - **Minimum Supported Version**: Update if dropping support for older versions
   - **Force Update**: Enable if critical update required

3. Click **Save Settings**

### 3. Version Release Checklist

- [ ] All code changes committed and tested
- [ ] Version number updated in settings
- [ ] Build number incremented
- [ ] Release notes written and saved
- [ ] Minimum supported version reviewed
- [ ] Force update enabled (if needed)
- [ ] Database migrations completed (if any)
- [ ] Frontend/backend compatibility verified
- [ ] Mobile app versions updated (if applicable)

## Best Practices

### Semantic Versioning
- Always follow `MAJOR.MINOR.PATCH` format
- Never skip version numbers (don't go from 1.0.0 to 1.2.0 without 1.1.0)
- Use version numbers consistently across all platforms

### Build Numbers
- Always increment build numbers, even for patch releases
- Build numbers should never decrease
- Use build numbers to track total releases

### Release Notes
- Be clear and concise
- Group changes by type (Features, Fixes, Changes)
- Highlight breaking changes prominently
- Include migration steps if needed

### Force Updates
- Use sparingly - only for critical issues
- Always provide clear communication about why update is required
- Test thoroughly before enabling

### Minimum Supported Version
- Review quarterly to determine if older versions should be dropped
- Consider user base and support resources
- Give advance notice before dropping support

## API Integration

If you need to check version information programmatically:

```php
use App\Models\Setting;

// Get current app version
$version = Setting::get('app_version', '1.0.0');

// Get build number
$build = Setting::get('app_build_number', '1');

// Check if force update is enabled
$forceUpdate = Setting::get('force_update', '0') === '1';

// Get minimum supported version
$minVersion = Setting::get('min_supported_version', '1.0.0');

// Get all versioning settings
$versioning = Setting::getByGroup('versioning');
```

## Frontend Integration Example

```javascript
// Check version and force update
async function checkVersion() {
    const response = await fetch('/api/version/check');
    const data = await response.json();
    
    if (data.forceUpdate) {
        // Show update required modal
        showUpdateModal(data.version, data.releaseNotes);
    } else if (isVersionOutdated(data.currentVersion, data.minVersion)) {
        // Show update recommended notification
        showUpdateNotification(data.version, data.releaseNotes);
    }
}

function isVersionOutdated(current, minimum) {
    // Compare semantic versions
    return compareVersions(current, minimum) < 0;
}
```

## Mobile App Integration

For mobile apps (iOS/Android):

1. **iOS**: Update `CFBundleShortVersionString` (version) and `CFBundleVersion` (build)
2. **Android**: Update `versionName` and `versionCode` in `build.gradle`
3. Sync these with the admin settings after each release

## Troubleshooting

### Version Not Updating
- Clear application cache: `php artisan cache:clear`
- Check database settings table
- Verify route permissions

### Force Update Not Working
- Ensure the setting is saved correctly
- Check frontend version checking logic
- Verify API endpoints are accessible

### Version Comparison Issues
- Use semantic versioning comparison libraries
- Ensure consistent format across all systems
- Test version comparison logic thoroughly

## Additional Resources

- [Semantic Versioning Specification](https://semver.org/)
- [Laravel Documentation](https://laravel.com/docs)
- Internal API Documentation (if available)

## Support

For questions or issues with versioning:
1. Check this guide first
2. Review recent changes in the admin settings
3. Contact the development team

---

**Last Updated**: December 2024
**Maintained By**: Development Team

