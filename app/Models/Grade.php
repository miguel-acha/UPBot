<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['enrollment_id','component','score'];
    protected $casts = ['score'=>'decimal:2'];
}
