<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAccount extends Model
{
    protected $fillable = ['student_id','current_balance'];
    protected $casts = ['current_balance'=>'decimal:2'];
}
