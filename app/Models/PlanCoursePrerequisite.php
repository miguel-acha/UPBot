<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanCoursePrerequisite extends Model
{
    protected $fillable = ['plan_course_id','prereq_plan_course_id'];

    public function planCourse(){ return $this->belongsTo(PlanCourse::class, 'plan_course_id'); }
    public function prereq(){ return $this->belongsTo(PlanCourse::class, 'prereq_plan_course_id'); }
}
