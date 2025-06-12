<?php

namespace Edgaras\SystemBackup\Services;

use Edgaras\SystemBackup\Mail\BackupMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Collection;
use Illuminate\Mail\Message;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BackupService
{
    public function backupSystem(bool $sendEmail = false): string
    {
        $backupPath = config('backup.backup_path', storage_path('app/backups'));
        if (!is_dir($backupPath)) {
            mkdir($backupPath, 0755, true);
            if (config('backup.restrict_access', true)) {
                file_put_contents($backupPath . '/.htaccess', "Deny from all");
            }
        }
        if (!is_writable($backupPath)) {
            throw new \Exception('Backup path is not writable');
        }
 
        $zipName = basename(config('backup.zip_name', 'system-backup-' . date('YmdHis') . '.zip'));
        $zipPath = $backupPath . '/' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) { 
            throw new \Exception('Failed to create zip file');
        }

        $password = config('backup.encryption_password');
        if ($password) {
            $zip->setPassword($password);
        }

        $exclude   = config('backup.exclude', []);
        $rootPath  = base_path();
        $files     = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            $filePath     = $file->getRealPath();
            $relativePath = substr($filePath, strlen($rootPath) + 1);

            if ($this->shouldExclude($relativePath, $exclude)) {
                continue;
            }

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
                if ($password) {
                    $zip->setEncryptionName($relativePath, ZipArchive::EM_AES_256);
                }
            }
        }

        $zip->close();

        $this->enforceRetention($backupPath);

        $cloudUrl = null;
        if (config('backup.cloud.enabled', false)) {
            $cloudUrl = $this->uploadToCloudAndGetUrl($zipPath);
            if (file_exists($zipPath)) {
                unlink($zipPath);
            }
        }

        if ($sendEmail && config('backup.email.enabled', false)) {
            $this->sendBackupEmail($cloudUrl ?: $zipPath);
        }

        return $cloudUrl ?: $zipPath;
    }

    protected function shouldExclude(string $path, array $exclude): bool
    {
        foreach ($exclude as $pattern) {
            $pattern = str_replace(['..', '//'], '', $pattern);
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        return false;
    }

    protected function enforceRetention(string $backupPath): void
    {
        if (!config('backup.retention.enabled', false)) {
            return;
        }

        $maxBackups = config('backup.retention.max_backups', 5);

        if (config('backup.cloud.enabled', false)) {
            $disk = config('backup.cloud.disk', 'azure');
            if (!array_key_exists($disk, config('filesystems.disks'))) {
                throw new \Exception("Cloud disk [$disk] is not configured.");
            }

            $files = collect(Storage::disk($disk)->files(config('backup.cloud.path')))
                ->filter(fn($file) => str_ends_with($file, '.zip'))
                ->sortByDesc(fn($file) => Storage::disk($disk)->lastModified($file))
                ->values();

            if ($files->count() > $maxBackups) {
                foreach ($files->slice($maxBackups - 1) as $file) {
                    if (Storage::disk($disk)->exists($file)) {
                        Storage::disk($disk)->delete($file);
                    }
                }
            } 
        } else {
            $files = glob($backupPath . '/*.zip');
            usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

            if (count($files) > $maxBackups) {
                foreach (array_slice($files, $maxBackups) as $file) {
                    unlink($file);
                }
            }
        }
    }

    protected function sendBackupEmail(string $backupLink): void
    {
        $recipients = array_filter(
            config('backup.email.recipients', []),
            fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        );

        if (empty($recipients)) { 
            throw new \Exception('No valid email recipients configured');
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new BackupMail(
                    config('backup.email.subject'),
                    config('backup.email.body'),
                    $backupLink
                ));
            } catch (\Throwable $e) { 
                throw $e;
            }
        }
    }

    protected function uploadToCloudAndGetUrl(string $zipPath): string
    {
        $disk = config('backup.cloud.disk', 'azure');

        if (!array_key_exists($disk, config('filesystems.disks'))) {
            throw new \Exception("Cloud disk [$disk] is not configured.");
        }

        $cloudPath = trim(config('backup.cloud.path'), '/') . '/' . basename($zipPath);
        Storage::disk($disk)->put($cloudPath, file_get_contents($zipPath), 'private');

        return Storage::disk($disk)->temporaryUrl(
            $cloudPath,
            now()->addHours(config('backup.cloud.signed_url_expiry', 24))
        );
    }
}