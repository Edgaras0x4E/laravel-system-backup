<?php

return [ 
    'backup_path' => storage_path('app/backups'), 
    'zip_name' => 'system-backup-' . date('YmdHis') . '.zip', 
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
    'encryption_password' => env('BACKUP_ENCRYPTION_PASSWORD', null), 
    'restrict_access' => true, 
    'retention' => [
        'enabled' => true,
        'max_backups' => 5,
    ], 
    'cloud' => [
        'enabled' => false,
        'disk' => 'azure',
        'path' => 'backups/path',
        'signed_url_expiry' => 24,
    ], 
    'email' => [
        'enabled' => false,
        'recipients' => ['hello@demomailtrap.co'],
        'subject' => 'System Backup Completed',
        'body' => 'Your system backup has finished successfully. Download using the link below (valid for 24 hours):',
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'hello@demomailtrap.co'),
            'name' => env('MAIL_FROM_NAME', 'Laravel Backup'),
        ],
        'use_cloud_link' => false,  
    ], 
    'download_route' => [
        'middleware' => ['web', 'signed'],
    ],
];
