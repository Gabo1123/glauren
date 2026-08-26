<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Collection extends Model
{
    protected $fillable = ['title', 'slug', 'description', 'image_path', 'link', 'sort', 'active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (Collection $c) {
            if (empty($c->slug) && $c->title) {
                $base = Str::slug($c->title);
                $slug = $base;
                $i = 2;
                while (static::where('slug', $slug)->where('id', '!=', $c->id)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $c->slug = $slug;
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(CollectionImage::class)->orderBy('sort');
    }

    public function imageUrl(): ?string
    {
        if (! $this->image_path) {
            // si no hay portada, usa la primera imagen de la galería
            return $this->images->first()?->url();
        }

        return str_starts_with($this->image_path, 'http')
            ? $this->image_path
            : Storage::disk('public')->url($this->image_path);
    }

    /** A dónde lleva la tarjeta: enlace propio si existe, si no la galería de la colección. */
    public function targetUrl(): string
    {
        if ($this->link) {
            return $this->link;
        }

        return route('collection.show', $this->slug);
    }
}
