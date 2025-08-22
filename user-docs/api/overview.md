# Isotone API Overview

Isotone provides RESTful APIs for various system operations. All APIs follow standard HTTP conventions and return appropriate status codes.

## Base URL

```
https://your-domain.com/iso-api/
```

## Available APIs

### Icons API

**Endpoint:** `/iso-api/icons.php`

Provides on-demand SVG icon delivery with caching and optimization.

- **Documentation:** [Icon API Guide](/icons/icon-api.md)
- **Method:** GET
- **Cache:** 1 year with ETag validation
- **CORS:** Enabled for cross-origin requests

**Quick Example:**
```
GET /iso-api/icons.php?name=home&style=outline&size=24
```

## Future APIs

The following APIs are planned for future releases:

| API | Endpoint | Status | Description |
|-----|----------|--------|-------------|
| **Themes** | `/iso-api/themes.php` | Planned | Theme management and configuration |
| **Plugins** | `/iso-api/plugins.php` | Planned | Plugin activation and settings |
| **Content** | `/iso-api/content.php` | Planned | Content creation and management |
| **Media** | `/iso-api/media.php` | Planned | File upload and media library |
| **Users** | `/iso-api/users.php` | Planned | User management (admin only) |

## API Standards

### HTTP Methods

- `GET` - Retrieve data
- `POST` - Create new resources
- `PUT` - Update existing resources
- `DELETE` - Remove resources
- `OPTIONS` - CORS preflight requests

### Response Formats

#### Success Response (200)
```json
{
  "success": true,
  "data": {...},
  "message": "Operation completed"
}
```

#### Error Response (4xx/5xx)
```json
{
  "success": false,
  "error": "Error description",
  "code": "ERROR_CODE"
}
```

### Status Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 304 | Not Modified (cached) |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 405 | Method Not Allowed |
| 429 | Rate Limited |
| 500 | Internal Server Error |

### CORS Support

All APIs include CORS headers for cross-origin requests:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

### Rate Limiting

APIs may implement rate limiting based on:
- IP address
- User authentication
- API endpoint

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

### Caching

APIs use standard HTTP caching headers:

```
Cache-Control: public, max-age=31536000
ETag: "abc123"
Last-Modified: Wed, 21 Oct 2023 07:28:00 GMT
```

## Authentication

Future APIs will support multiple authentication methods:

- **Session-based** - For web interface
- **API Keys** - For programmatic access
- **JWT Tokens** - For modern applications

## Error Handling

APIs return descriptive error messages:

```json
{
  "success": false,
  "error": "Invalid icon name. Must contain only letters, numbers, and hyphens.",
  "code": "INVALID_ICON_NAME",
  "details": {
    "parameter": "name",
    "value": "invalid@icon",
    "allowed_pattern": "[a-z0-9-]+"
  }
}
```

## Testing the API

### API Explorer

Visit `/iso-api/` to see available endpoints and test them interactively.

### Command Line

```bash
# Get an icon
curl "https://your-domain.com/iso-api/icons.php?name=home&style=outline"

# Check API status
curl "https://your-domain.com/iso-api/"
```

### JavaScript

```javascript
// Fetch API info
fetch('/iso-api/')
  .then(response => response.json())
  .then(data => console.log(data));

// Get an icon
fetch('/iso-api/icons.php?name=user&style=solid')
  .then(response => response.text())
  .then(svg => console.log(svg));
```

## Versioning

Future API versions will use URL-based versioning:

```
/iso-api/v1/icons.php  (current)
/iso-api/v2/icons.php  (future)
```

The current APIs are considered v1 and will remain stable.