# Environment Variables

*Auto-generated from .env.example*

## Application

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_NAME` | `"Isotone CMS"` | |
| `APP_ENV` | `development` | |
| `APP_DEBUG` | `true` | |
| `APP_URL` | `http://localhost/isotone` | |
| `APP_KEY` | `*(empty)*` | |

## Your IDE should connect using these same settings

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `localhost` | |
| `DB_PORT` | `3306` | |
| `DB_DATABASE` | `isotone_db` | |
| `DB_USERNAME` | `root` | |
| `DB_PASSWORD` | `*(empty)*` | |

## Cache

| Variable | Default | Description |
|----------|---------|-------------|
| `CACHE_DRIVER` | `file` | |

## Session

| Variable | Default | Description |
|----------|---------|-------------|
| `SESSION_DRIVER` | `file` | |
| `SESSION_LIFETIME` | `120` | |

## Logging

| Variable | Default | Description |
|----------|---------|-------------|
| `LOG_CHANNEL` | `single` | |
| `LOG_LEVEL` | `debug` | |

## Mail

| Variable | Default | Description |
|----------|---------|-------------|
| `MAIL_DRIVER` | `smtp` | |
| `MAIL_HOST` | `localhost` | |
| `MAIL_PORT` | `25` | |
| `MAIL_USERNAME` | `*(empty)*` | |
| `MAIL_PASSWORD` | `*(empty)*` | |
| `MAIL_ENCRYPTION` | `*(empty)*` | |
| `MAIL_FROM_ADDRESS` | `noreply@isotone.local` | |
| `MAIL_FROM_NAME` | `"${APP_NAME}"` | |

## Media

| Variable | Default | Description |
|----------|---------|-------------|
| `MAX_UPLOAD_SIZE` | `10M` | |
| `ALLOWED_FILE_TYPES` | `jpg,jpeg,png,gif,webp,pdf,doc,docx,zip` | |

## Security

| Variable | Default | Description |
|----------|---------|-------------|
| `BCRYPT_ROUNDS` | `10` | |
| `JWT_SECRET` | `*(empty)*` | |
| `RATE_LIMIT` | `60` | |

