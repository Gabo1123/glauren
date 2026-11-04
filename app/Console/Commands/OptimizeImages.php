<?php

namespace App\Console\Commands;

use App\Modules\Chaquetas\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class OptimizeImages extends Command
{
    protected $signature = 'imagenes:optimizar
        {--dry : Solo muestra cuánto se ahorraría, sin cambiar nada}
        {--max=1920 : Tamaño máximo (px) del lado más largo}';

    protected $description = 'Hace más livianas las fotos subidas (storage/app/public) y las del sitio (public/img) sin cambiar sus nombres.';

    public function handle(): int
    {
        $dirs = array_filter([storage_path('app/public'), public_path('img')], 'is_dir');
        $files = (new Finder)->files()->in($dirs)->name(['*.jpg', '*.jpeg', '*.JPG', '*.JPEG', '*.png', '*.PNG', '*.webp']);

        $count = 0;
        $before = 0;
        $after = 0;
        foreach ($files as $f) {
            $r = ImageOptimizer::optimizeFile($f->getRealPath(), (bool) $this->option('dry'), (int) $this->option('max'));
            if ($r) {
                $count++;
                $before += $r['before'];
                $after += $r['after'];
                $this->line(sprintf('  %s  %s → %s', $f->getRelativePathname(), $this->kb($r['before']), $this->kb($r['after'])));
            }
        }

        $saved = $before - $after;
        $this->info(($this->option('dry') ? 'Se ahorraría' : 'Listo: se ahorró').' '.$this->kb($saved).' en '.$count.' foto(s).');

        return self::SUCCESS;
    }

    private function kb(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024).' KB';
    }
}
