<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPostComment extends Model
{
    protected $fillable = ['student_post_id', 'student_id', 'body', 'hidden'];

    protected function casts(): array
    {
        return ['hidden' => 'boolean'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
