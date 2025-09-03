<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionPolicy extends Model
{
    protected $fillable = ['program_id','semester_id','tuition_type','tuition_per_credit','flat_tuition','other_fees_json'];
    protected $casts = ['other_fees_json'=>'array','tuition_per_credit'=>'decimal:2','flat_tuition'=>'decimal:2'];
}
