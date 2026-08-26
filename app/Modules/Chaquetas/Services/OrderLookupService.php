<?php

namespace App\Modules\Chaquetas\Services;

use App\Modules\Chaquetas\Models\Student;

/**
 * Cruza el mensaje del cliente con su pedido (por número de documento)
 * para que la IA responda con datos reales: etapa, pago y fotos.
 */
class OrderLookupService
{
    /** Extrae un posible número de documento del texto (6 a 11 dígitos). */
    public function extractDocument(string $text): ?string
    {
        // quita espacios/puntos de miles dentro de números: "1.023.456.789" -> "1023456789"
        $clean = preg_replace('/(?<=\d)[.\s](?=\d)/', '', $text);
        if (preg_match('/\b\d{6,11}\b/', $clean, $m)) {
            return $m[0];
        }

        return null;
    }

    public function findByDocument(string $document): ?Student
    {
        return Student::with(['contract.school', 'contract.jacketType', 'contract.photos'])
            ->where('document', trim($document))
            ->latest()
            ->first();
    }

    /** Texto para inyectar al prompt: la IA responde SOLO con esta verdad del pedido. */
    public function contextFor(Student $s): string
    {
        $c = $s->contract;
        $stage = $c ? $c->stageLabel() : 'sin contrato asignado';
        $school = $c?->school?->name ?? '—';
        $prenda = $c?->jacketType?->name ?? 'prenda';
        $photos = $c ? $c->photos->count() : 0;

        $pago = match ($s->payment_status) {
            'pagado'      => 'PAGADO (al día)',
            'en_revision' => 'comprobante EN REVISIÓN (un asesor lo está validando)',
            default       => 'pago PENDIENTE',
        };

        $lines = [
            "DATOS REALES DEL PEDIDO DE ESTE CLIENTE (documento {$s->document}) — responde con esto, no inventes:",
            "• Estudiante: {$s->name}",
            "• Colegio: {$school} · Prenda: {$prenda}",
            "• Etapa de producción actual: {$stage}",
            "• Estado de pago: {$pago}",
            "• Fotos de avance disponibles: {$photos}".($photos ? ' (dile que ya se las envías por aquí)' : ' (aún no hay fotos cargadas)'),
        ];

        return implode("\n", $lines);
    }

    /** Últimas fotos de avance (URL + descripción) para enviarlas por WhatsApp. */
    public function latestPhotos(Student $s, int $limit = 4): array
    {
        $c = $s->contract;
        if (! $c) {
            return [];
        }

        return $c->photos->sortByDesc('id')->take($limit)->map(fn ($p) => [
            'url'     => $p->url(),
            'caption' => trim(($p->phase ? '['.$p->phase.'] ' : '').($p->caption ?? '')),
        ])->values()->all();
    }
}
