<?php

namespace App\Modules\Chaquetas\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Formatos de Excel (CSV) para alumnos y tallas.
 * Se descargan con ";" (Excel en Colombia los abre en columnas) y al subirlos
 * se acepta ";" o "," y los encabezados se reconocen aunque cambien mayúsculas o tildes.
 */
class StudentSheet
{
    /** Descarga un CSV que Excel abre directo en columnas. */
    public static function download(string $filename, array $rows)
    {
        $csv = collect($rows)->map(fn ($r) => implode(';', array_map(
            fn ($v) => preg_match('/[;"\n]/', (string) $v) ? '"'.str_replace('"', '""', (string) $v).'"' : (string) $v,
            $r
        )))->implode("\r\n");

        return response("\xEF\xBB\xBF".$csv."\r\n", 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Lee el archivo y devuelve filas como ['documento' => .., 'nombre' => .., ...].
     *
     * @return array<int, array<string, string>>
     */
    public static function read(UploadedFile $file, int $max = 1000): array
    {
        $text = (string) file_get_contents($file->getRealPath());
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        if (! mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'Windows-1252');   // CSV guardado por Excel en Windows
        }
        $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $text), fn ($l) => trim($l, " ;,\t") !== ''));
        if (! $lines) {
            return [];
        }
        if (stripos($lines[0], 'sep=') === 0) {
            array_shift($lines);
        }
        $delim = substr_count($lines[0], ';') >= substr_count($lines[0], ',') ? ';' : ',';
        if (substr_count($lines[0], "\t") > substr_count($lines[0], $delim)) {
            $delim = "\t";
        }

        $headers = array_map(fn ($h) => self::key($h), str_getcsv(array_shift($lines), $delim));
        $rows = [];
        foreach (array_slice($lines, 0, $max) as $line) {
            $cells = array_map('trim', str_getcsv($line, $delim));
            $row = [];
            foreach ($headers as $i => $h) {
                if ($h !== '') {
                    $row[$h] = $cells[$i] ?? '';
                }
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /** "Número de Documento" → "numero_de_documento"; luego se mapean sinónimos. */
    public static function key(string $h): string
    {
        $k = Str::of($h)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->toString();

        return match (true) {
            str_contains($k, 'documento') || in_array($k, ['doc', 'cedula', 'ti', 'identificacion'], true) => 'documento',
            str_contains($k, 'nombre') => 'nombre',
            str_contains($k, 'apodo') => 'apodo',
            str_contains($k, 'celular') || str_contains($k, 'telefono') || str_contains($k, 'whatsapp') => 'celular',
            str_contains($k, 'sexo') || str_contains($k, 'genero') => 'sexo',
            str_contains($k, 'talla') => 'talla',
            str_contains($k, 'pecho') => 'pecho',
            str_contains($k, 'cintura') => 'cintura',
            str_contains($k, 'cadera') => 'cadera',
            str_contains($k, 'manga') => 'manga',
            str_contains($k, 'talle') || str_contains($k, 'largo') => 'talle',
            default => $k,
        };
    }

    public static function document(?string $v): string
    {
        return preg_replace('/\D/', '', (string) $v);
    }

    public static function number(?string $v): ?float
    {
        $v = trim((string) $v);
        if ($v === '') {
            return null;
        }
        $n = (float) str_replace(',', '.', preg_replace('/[^\d,.]/', '', $v));

        return $n > 0 ? $n : null;
    }

    public static function gender(?string $g): ?string
    {
        $g = mb_strtolower(trim((string) $g));

        return match (true) {
            in_array($g, ['m', 'masculino', 'hombre', 'h'], true) => 'hombre',
            in_array($g, ['f', 'femenino', 'mujer'], true) => 'mujer',
            default => null,
        };
    }
}
