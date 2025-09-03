<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessToken extends Model
{
    protected $fillable = [
        'interaction_id','student_id','token_hash','token_hint','purpose',
        'status','max_uses','expires_at','redeemed_at','redeemed_ip'
    ];
    protected $casts = ['expires_at'=>'datetime','redeemed_at'=>'datetime','max_uses'=>'integer'];

    public function interaction(){ return $this->belongsTo(Interaction::class); }
    public function student(){ return $this->belongsTo(Student::class); }
}
