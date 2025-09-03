<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentScholarship extends Model
{
    protected $fillable = ['student_id','scholarship_id','status','effective_from_semester_id','effective_to_semester_id','custom_benefit_value'];
    protected $casts = ['custom_benefit_value'=>'decimal:2'];
}
