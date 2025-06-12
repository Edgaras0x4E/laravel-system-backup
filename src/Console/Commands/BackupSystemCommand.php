<?php

namespace Edgaras\SystemBackup\Console\Commands;

use Illuminate\Console\Command;
use Edgaras\SystemBackup\Services\BackupService;

class BackupSystemCommand extends Command
{
    protected $signature = 'backup:system {--email : Send an email with the backup link}';
    protected $description = 'Create a backup of the entire system and zip it, with an option to email the backup link';

    public function handle()
    {
        $this->info('Starting system backup...');
        try {
            $sendEmail = $this->option('email');
            $backupPath = app('backup')->backupSystem($sendEmail);
            $this->info('Backup created successfully at: ' . $backupPath);
            if ($sendEmail) {
                $this->info('Backup link emailed to configured recipients.');
            }
        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage()); 
        }
    }
}