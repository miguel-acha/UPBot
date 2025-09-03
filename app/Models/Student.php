<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = ['program_id','upb_code','ci','email_institucional','telefono','full_name','status'];
    protected $casts = ['status'=>'string'];

    public function program(){ return $this->belongsTo(Program::class); }
    public function documents(){ return $this->hasMany(AcademicDocument::class); }
}
