<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    protected $fillable = ['code','name','description','benefit_type','benefit_value','conditions_json','is_active'];
    protected $casts = ['is_active'=>'boolean','conditions_json'=>'array','benefit_value'=>'decimal:2'];
}
