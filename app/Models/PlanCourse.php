<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanCourse extends Model
{
    protected $fillable = ['program_plan_id','course_id','semester_number','type','recommended_credits'];
    protected $casts = ['semester_number'=>'integer','recommended_credits'=>'integer'];

    public function plan(){ return $this->belongsTo(ProgramPlan::class, 'program_plan_id'); }
    public function course(){ return $this->belongsTo(Course::class); }
    public function prerequisites(){
        return $this->hasMany(PlanCoursePrerequisite::class, 'plan_course_id');
    }
}
