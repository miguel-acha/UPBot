<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityVerification extends Model
{
    protected $fillable = ['student_id','method','destination','code_hash','status','expires_at','verified_at','meta_json'];
    protected $casts = ['expires_at'=>'datetime','verified_at'=>'datetime','meta_json'=>'array'];

    public function student(){ return $this->belongsTo(Student::class); }
}
