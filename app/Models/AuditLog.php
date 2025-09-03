<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['actor_type','actor_id','action','target_type','target_id','ip','metadata'];
    protected $casts = ['metadata'=>'array'];
}
