<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'published', 'sort',
        'meta_title', 'meta_description', 'focus_keyword', 'og_image', 'noindex', 'schema_type'];

    protected function casts(): array
    {
        return ['published' => 'boolean', 'noindex' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Título SEO efectivo. */
    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    /** Descripción SEO efectiva. */
    public function metaDescription(): string
    {
        return $this->meta_description
            ?: \Illuminate\Support\Str::limit(trim(strip_tags($this->body)), 155, '');
    }

    public function ogImage(): ?string
    {
        return $this->og_image ?: null;
    }
}
