<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusRoute extends Model
{
    protected $fillable = ['code','name','is_active','pdf_path','map_json'];
    protected $casts = ['is_active'=>'boolean','map_json'=>'array'];
}
