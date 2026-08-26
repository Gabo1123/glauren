<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBlock extends Model
{
    protected $fillable = [
        'page_id', 'type', 'position', 'title', 'subtitle', 'body',
        'image_path', 'button_text', 'button_url', 'video_url', 'data', 'columns', 'spacing', 'img_pos',
        'theme', 'bg_color', 'bg_image', 'width', 'align', 'sort', 'active',
    ];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'data' => 'array'];
    }

    public const TYPES = [
        'texto'        => 'Texto (título + contenido)',
        'imagen_texto' => 'Imagen + texto',
        'cta'          => 'Llamado a la acción (botón)',
        'video'        => 'Video (YouTube / TikTok)',
        'features'     => 'Beneficios / Garantías (tarjetas con ícono)',
        'steps'        => 'Pasos / Proceso (numerados)',
        'stats'        => 'Números / Estadísticas (contadores)',
        'galeria'      => 'Galería de fotos',
        'html'         => 'HTML libre',
    ];

    /** Tipos que usan la lista de ítems (data) con editor repetible. */
    public const ITEM_TYPES = ['features', 'steps', 'stats'];

    public const POSITIONS = [
        'after_hero'     => 'Debajo del banner principal',
        'before_gallery' => 'Antes de “Ya visten Glauren”',
        'before_footer'  => 'Antes del pie de página',
    ];

    public function scopeActive($q)
    {
        return $q->where('active', true)->orderBy('sort')->orderBy('id');
    }

    /** Bloques del home (sin página asociada). */
    public function scopeHome($q)
    {
        return $q->whereNull('page_id');
    }

    /** Bloques de una página concreta. */
    public function scopeForPage($q, int $pageId)
    {
        return $q->where('page_id', $pageId);
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : Storage::disk('public')->url($this->image_path);
    }

    public function bgImageUrl(): ?string
    {
        if (! $this->bg_image) {
            return null;
        }

        return str_starts_with($this->bg_image, 'http')
            ? $this->bg_image
            : Storage::disk('public')->url($this->bg_image);
    }

    /** Devuelve ['type'=>'yt'|'tk', 'url'=>embedUrl] o null. */
    public function videoEmbed(): ?array
    {
        $u = trim((string) $this->video_url);
        if (! $u) {
            return null;
        }
        if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|shorts/|embed/))([A-Za-z0-9_-]{6,})~', $u, $m)) {
            return ['type' => 'yt', 'url' => 'https://www.youtube.com/embed/'.$m[1]];
        }
        if (preg_match('~tiktok\.com/(?:.*?/video/|embed/v2/)(\d+)~', $u, $m)) {
            return ['type' => 'tk', 'url' => 'https://www.tiktok.com/embed/v2/'.$m[1]];
        }

        return null;
    }
}
