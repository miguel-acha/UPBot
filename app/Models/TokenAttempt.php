<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenAttempt extends Model
{
    protected $fillable = ['access_token_id','ip','user_agent','success'];
    protected $casts = ['success'=>'boolean'];

    public function token(){ return $this->belongsTo(AccessToken::class, 'access_token_id'); }
}
