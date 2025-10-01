<?php

namespace Edgaras\SystemBackup\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BackupDownloadController extends Controller
{
    public function download(Request $request, string $filename)
    {
        $backupPath = rtrim(config('backup.backup_path', storage_path('app/backups')), '/\\');
        $path = $backupPath . '/' . basename($filename);
        
        if (!file_exists($path)) {
            abort(404, 'Backup file not found at: ' . $path);
        }
        
        return response()->download($path);
    }
}
