<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLedgerEntry extends Model
{
    protected $fillable = ['student_id','semester_id','entry_type','amount','description','ref_json'];
    protected $casts = ['amount'=>'decimal:2','ref_json'=>'array'];
}
