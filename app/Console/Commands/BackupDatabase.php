<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--keep=14 : Cuántos respaldos conservar}';

    protected $description = 'Crea un respaldo (.sql) de la base de datos y borra los más antiguos.';

    public function handle(): int
    {
        $db   = config('database.connections.mysql.database');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');

        // Ubica mysqldump (XAMPP por defecto, o en el PATH)
        $dump = 'C:/xampp/mysql/bin/mysqldump.exe';
        if (! File::exists($dump)) {
            $dump = 'mysqldump';
        }

        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $file = $dir.DIRECTORY_SEPARATOR.$db.'_'.now()->format('Y-m-d_His').'.sql';

        $cmd = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s --single-transaction --routines --events %s > "%s"',
            $dump, $host, $port, $user,
            $pass !== '' ? '--password='.escapeshellarg($pass) : '',
            escapeshellarg($db), $file
        );

        $this->info('Creando respaldo...');
        exec($cmd.' 2>&1', $out, $code);

        if ($code !== 0 || ! File::exists($file) || File::size($file) < 100) {
            $this->error('No se pudo crear el respaldo. '.implode(' ', $out));

            return self::FAILURE;
        }

        $this->info('Respaldo creado: '.$file.' ('.round(File::size($file) / 1024, 1).' KB)');

        // Conserva solo los últimos N
        $keep = (int) $this->option('keep');
        $files = collect(File::glob($dir.DIRECTORY_SEPARATOR.$db.'_*.sql'))
            ->sortByDesc(fn ($f) => $f)->values();
        foreach ($files->slice($keep) as $old) {
            File::delete($old);
            $this->line('Eliminado respaldo antiguo: '.basename($old));
        }

        return self::SUCCESS;
    }
}
