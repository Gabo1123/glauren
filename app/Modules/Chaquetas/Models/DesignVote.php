<?php

namespace App\Modules\Chaquetas\Models;

use Illuminate\Database\Eloquent\Model;

class DesignVote extends Model
{
    protected $fillable = ['contract_id', 'design_option_id', 'student_id'];
}
