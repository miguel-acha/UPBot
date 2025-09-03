<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = ['department_id','code','name','degree_level','duration_semesters'];

    public function department(){ return $this->belongsTo(Department::class); }
}
