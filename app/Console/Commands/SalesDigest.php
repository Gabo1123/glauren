<?php

namespace App\Console\Commands;

use App\Modules\Chaquetas\Support\SalesAutomation;
use Illuminate\Console\Command;

class SalesDigest extends Command
{
    protected $signature = 'ventas:recordatorio {--forzar : Envía aunque esté desactivado en el panel}';

    protected $description = 'Envía al administrador la lista de clientes a contactar hoy (correo y WhatsApp).';

    public function handle(SalesAutomation $sales): int
    {
        if (! $this->option('forzar') && ! SalesAutomation::digestEnabled()) {
            $this->info('Resumen diario desactivado.');

            return self::SUCCESS;
        }
        $n = $sales->sendDigest();
        $this->info($n ? "Resumen enviado: $n cliente(s)." : 'Hoy no hay a quién contactar.');

        return self::SUCCESS;
    }
}
