<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    protected $fillable = ['code','start_date','end_date'];
    protected $casts = ['start_date'=>'date','end_date'=>'date'];
}
