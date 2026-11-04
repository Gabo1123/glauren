<?php

namespace App\Console\Commands;

use App\Modules\Chaquetas\Support\SalesAutomation;
use Illuminate\Console\Command;

class SalesFollowUp extends Command
{
    protected $signature = 'ventas:seguimiento {--dry : Solo muestra a quién se le escribiría}';

    protected $description = 'Escribe una vez a los clientes que vieron la cotización y no respondieron en N días.';

    public function handle(SalesAutomation $sales): int
    {
        if (! SalesAutomation::followupEnabled() && ! $this->option('dry')) {
            $this->info('Seguimiento automático desactivado (actívalo en Ventas → Automatizaciones).');

            return self::SUCCESS;
        }
        $list = $sales->followupCandidates();
        foreach ($list as $q) {
            if ($this->option('dry')) {
                $this->line($q->code().' · '.$q->customer_name);
                continue;
            }
            $via = $sales->followup($q);
            $this->line($q->code().' → '.($via ? implode(', ', $via) : 'sin enviar'));
        }
        $this->info($list->count().' cotización(es).');

        return self::SUCCESS;
    }
}
