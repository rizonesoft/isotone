# Vendor JavaScript Libraries

This directory contains third-party JavaScript libraries used by Isotone.

## Libraries

### Chart.js
- **Version**: 4.4.0
- **License**: MIT
- **Website**: https://www.chartjs.org/
- **Files**:
  - `chart.min.js` - Minified version (201 KB)
  - `chart.js` - Development version (201 KB)
- **Usage**: Data visualization and charts in admin dashboard

### Alpine.js
- **Version**: 3.14.3
- **License**: MIT
- **Website**: https://alpinejs.dev/
- **Files**:
  - `alpine.min.js` - Minified version (44 KB)
  - `alpine.js` - Development version (107 KB)
- **Usage**: Reactive and declarative UI components

## Loading Strategy

The admin layout automatically:
1. Prefers minified versions (`.min.js`) if available
2. Falls back to regular versions if minified not found
3. Falls back to CDN as last resort

## Benefits of Local Hosting

- **Performance**: Eliminates external HTTP requests
- **Reliability**: Works offline and behind firewalls
- **Security**: No external dependencies
- **Control**: Version locked and predictable

## Updating Libraries

To update these libraries:
1. Download new versions from official sources
2. Maintain both minified and regular versions
3. Update version numbers in this README
4. Test thoroughly before deploying

---
*Last updated: 2025-08-23*