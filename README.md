# Laravel System Backup

Laravel package to perform a full system backup, compress it into a zip file, and optionally email it or upload to the cloud.

---

## Features

- Zips the entire Laravel application directory.
- **Exclude list** to skip unnecessary files (e.g. `.env`, `vendor`, `node_modules`).
- Optional **AES‑256 encryption** for the archive.
- **Retention policy** – keep only _N_ most recent backups.
- Upload to any configured **filesystem disk** (e.g. [Azure](https://github.com/Azure-OSS/azure-storage-php-adapter-laravel), S3) and generate a **signed URL**.
- Send a configurable **email** with the local path or cloud link.
- Artisan command `backup:system` with `--email` flag.

---

⚠️ **Disclaimer**

This package is functional but **not thoroughly tested** in production environments. Use with caution and verify backups independently before relying on it for critical systems.

---

## Requirements

- PHP 8.2+
- Laravel 12

---

## Installation

```bash
composer require edgaras/laravel-system-backup
```
 
Publish assets:

```bash
# Mail blade view
php artisan vendor:publish --tag=backup-mail-view

# Config file
php artisan vendor:publish --tag=backup-config
```

---

## Usage

### Manual backup

```bash
php artisan backup:system          # create archive only
php artisan backup:system --email  # create archive and send e‑mail
```

---

## Configuration (`config/backup.php`)

```php
return [
    // Where archives are stored locally
    'backup_path' => storage_path('app/backups'),

    // Backup file name 
    'zip_name' => 'system-backup-' . date('YmdHis') . '.zip',

    // Patterns to exclude from the archive
    'exclude' => [
        '.env', 
        '.env.*', 
        '.git', 
        '.gitignore',
        'composer.lock', 
        'vendor', 
        'node_modules',
        'storage/app/backups', 
        'storage/framework', 
        'storage/logs',
    ],

    // Optional AES‑256 encryption password (set in .env)
    'encryption_password' => env('BACKUP_ENCRYPTION_PASSWORD', null),

    // Add .htaccess (Deny from all) to backup_path
    'restrict_access' => true,

    // Keep only the N newest archives (local or cloud)
    'retention' => [
        'enabled' => true,
        'max_backups' => 5,
    ],

    // Cloud storage
    'cloud' => [
        'enabled' => false,           // true = upload instead of storing locally
        'disk'    => 'azure',         // any disk defined in config/filesystems.php
        'path'    => 'backups/path',  // folder inside the disk
        'signed_url_expiry' => 24,    // link validity in hours
    ],

    // Email notification
    'email' => [
        'enabled'    => false,
        'recipients' => ['hello@demomailtrap.co'],
        'subject'    => 'System Backup Completed',
        'body'       => 'Your system backup has finished successfully. Download using the link below (valid for 24 hours):',
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@demomailtrap.co'),
            'name'    => env('MAIL_FROM_NAME', 'Laravel Backup'),
        ], 
        'use_cloud_link' => false,
    ],

    // Route that serves local downloads (signed)
    'download_route' => [
        'middleware' => ['web', 'signed'],
    ],
];
```

### Environment variables

```dotenv
BACKUP_ENCRYPTION_PASSWORD=secretpass
```

 