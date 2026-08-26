<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPostLike extends Model
{
    protected $fillable = ['student_post_id', 'student_id'];
}
