<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    protected $fillable = ['title', 'slug', 'cover_path', 'excerpt', 'author', 'author_role', 'body', 'published_at', 'active',
        'meta_title', 'meta_description', 'focus_keyword', 'og_image', 'noindex'];

    /** Autor efectivo (con valor por defecto de marca). */
    public function authorName(): string
    {
        return $this->author ?: 'Equipo Glauren';
    }

    public function authorRole(): string
    {
        return $this->author_role ?: 'Expertos en chaquetas de promoción';
    }

    /** Minutos de lectura estimados. */
    public function readingMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->body));

        return max(1, (int) ceil($words / 200));
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'active'       => 'boolean',
            'noindex'      => 'boolean',
        ];
    }

    /** Título SEO efectivo (usa el meta_title si existe, si no el título). */
    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /** Descripción SEO efectiva (meta_description, si no el resumen o inicio del cuerpo). */
    public function metaDescription(): string
    {
        return $this->meta_description
            ?: \Illuminate\Support\Str::limit(trim(strip_tags($this->excerpt ?: $this->body)), 155, '');
    }

    /** Imagen social efectiva (og_image o la portada). */
    public function ogImage(): ?string
    {
        return $this->og_image ?: $this->coverUrl();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function scopePublished($query)
    {
        return $query->where('active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderByDesc('published_at');
    }
}
