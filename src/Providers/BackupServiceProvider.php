<?php

namespace Edgaras\SystemBackup\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Edgaras\SystemBackup\Console\Commands\BackupSystemCommand;
use Edgaras\SystemBackup\Services\BackupService;
use Edgaras\SystemBackup\Http\Controllers\BackupDownloadController;

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
 
        $this->registerDownloadRoute();
    }

    protected function registerDownloadRoute(): void
    {
        Route::middleware(config('backup.download_route.middleware', ['web', 'signed']))
        ->get(config('backup.download_route.prefix', '/backups/download') . '/{filename}', [BackupDownloadController::class, 'download'])
        ->name('backup.download');
    }
}