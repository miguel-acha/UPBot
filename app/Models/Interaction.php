<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $fillable = ['channel','requester_contact','intent','sensitivity_level','student_id','raw_text','meta_json'];
    protected $casts = ['meta_json'=>'array'];

    public function student(){ return $this->belongsTo(Student::class); }
}
