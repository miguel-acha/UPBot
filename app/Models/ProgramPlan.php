<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPlan extends Model
{
    protected $fillable = ['program_id','code','plan_year','is_active'];
    protected $casts = ['is_active'=>'boolean','plan_year'=>'integer'];

    public function program(){ return $this->belongsTo(Program::class); }
    public function planCourses(){ return $this->hasMany(PlanCourse::class); }
}
