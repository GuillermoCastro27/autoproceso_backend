<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackupDatabase extends Command
{
    protected $signature   = 'db:backup';
    protected $description = 'Genera un backup de la base de datos PostgreSQL';

    public function handle(): int
    {
        $cfg  = config('database.connections.pgsql');
        $dir  = rtrim(env('BACKUP_PATH', storage_path('backups')), '/\\');
        $file = $dir . DIRECTORY_SEPARATOR . 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pgDump = env('PG_DUMP_PATH', 'pg_dump');

        $cmd = sprintf(
            '%s --host=%s --port=%s --username=%s --no-password --format=plain --file=%s %s',
            escapeshellarg($pgDump),
            escapeshellarg($cfg['host']),
            escapeshellarg($cfg['port']),
            escapeshellarg($cfg['username']),
            escapeshellarg($file),
            escapeshellarg($cfg['database'])
        );

        // Pasar la contraseña via variable de entorno (evita exponerla en el proceso)
        putenv("PGPASSWORD={$cfg['password']}");
        exec($cmd, $output, $code);
        putenv('PGPASSWORD=');

        if ($code !== 0) {
            $this->error("Error al generar el backup (código: {$code})");
            return self::FAILURE;
        }

        $size = round(filesize($file) / 1024, 1);
        $this->info("Backup generado: {$file} ({$size} KB)");

        // Eliminar backups con más de 30 días
        $this->limpiarBackupsViejos($dir);

        return self::SUCCESS;
    }

    private function limpiarBackupsViejos(string $dir): void
    {
        $limite = now()->subDays(30)->getTimestamp();
        $count  = 0;

        foreach (glob($dir . DIRECTORY_SEPARATOR . 'backup_*.sql') as $archivo) {
            if (filemtime($archivo) < $limite) {
                unlink($archivo);
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("Se eliminaron {$count} backup(s) con más de 30 días.");
        }
    }
}
