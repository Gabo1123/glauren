<?php

namespace App\Modules\Chaquetas\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Hace más livianas las fotos (para que la web cargue rápido en el celular):
 * las reduce a un máximo de 1920 px y las vuelve a guardar comprimidas,
 * con el mismo nombre y formato (no cambia ninguna ruta guardada).
 */
class ImageOptimizer
{
    public const MAX = 1920;

    public const MIN_BYTES = 250 * 1024;   // las de menos de 250 KB se dejan quietas

    /** Optimiza un archivo del disco "public". */
    public static function optimizeStored(?string $path): ?array
    {
        if (! $path) {
            return null;
        }
        $disk = Storage::disk('public');

        return $disk->exists($path) ? self::optimizeFile($disk->path($path)) : null;
    }

    /**
     * @return array{before:int, after:int}|null  null si no se tocó
     */
    public static function optimizeFile(string $file, bool $dryRun = false, int $max = self::MAX): ?array
    {
        if (! function_exists('imagecreatefromjpeg') || ! is_file($file)) {
            return null;
        }
        $before = filesize($file);
        $info = @getimagesize($file);
        if (! $info) {
            return null;
        }
        [$w, $h, $type] = $info;
        $tooBig = max($w, $h) > $max;
        if ($before < self::MIN_BYTES && ! $tooBig) {
            return null;
        }
        if (! in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return null;
        }

        $img = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
            IMAGETYPE_PNG  => @imagecreatefrompng($file),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : false,
        };
        if (! $img) {
            return null;
        }

        // Orientación de fotos de celular (EXIF)
        if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $o = (int) (@exif_read_data($file)['Orientation'] ?? 1);
            $img = match ($o) { 3 => imagerotate($img, 180, 0), 6 => imagerotate($img, -90, 0), 8 => imagerotate($img, 90, 0), default => $img };
            [$w, $h] = [imagesx($img), imagesy($img)];
        }

        if (max($w, $h) > $max) {
            $k = $max / max($w, $h);
            $nw = (int) round($w * $k);
            $nh = (int) round($h * $k);
            $dst = imagecreatetruecolor($nw, $nh);
            if ($type !== IMAGETYPE_JPEG) {
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $dst;
        }

        $tmp = $file.'.opt';
        if ($type === IMAGETYPE_JPEG) {
            imageinterlace($img, true);          // carga progresiva
        } else {
            imagesavealpha($img, true);
        }
        $ok = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($img, $tmp, 82),
            IMAGETYPE_PNG  => imagepng($img, $tmp, 9),
            IMAGETYPE_WEBP => imagewebp($img, $tmp, 80),
        };
        imagedestroy($img);
        if (! $ok || ! is_file($tmp)) {
            @unlink($tmp);

            return null;
        }

        $after = filesize($tmp);
        if ($after >= $before || $dryRun) {
            @unlink($tmp);

            return $after >= $before ? null : ['before' => $before, 'after' => $after];
        }
        rename($tmp, $file);

        return ['before' => $before, 'after' => $after];
    }
}
