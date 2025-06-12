<?php

namespace Edgaras\SystemBackup\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Edgaras\SystemBackup\Console\Commands\BackupSystemCommand;
use Edgaras\SystemBackup\Services\BackupService;

class BackupServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('backup', fn () => new BackupService());
    }

    public function boot()
    { 

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'systembackup');

        $this->publishes([__DIR__ . '/../../resources/views' => resource_path('views/vendor/systembackup'),], 'backup-mail-view');

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupSystemCommand::class,
            ]);
        }
 
        $this->publishes([
            __DIR__ . '/../../config/backup.php' => config_path('backup.php'),
        ], 'backup-config');

        $this->mergeConfigFrom(__DIR__ . '/../../config/backup.php', 'backup');
 
        Route::middleware(config('backup.download_route.middleware', ['web', 'signed']))
             ->get('/backups/download/{filename}', function ($filename) {
                 $path = rtrim(config('backup.backup_path', storage_path('app/backups')), '/\\') . '/' . basename($filename);
                 if (!file_exists($path)) {
                     abort(404, 'Backup file not found');
                 }
                 return response()->download($path);
             })->name('backup.download');
    }
}