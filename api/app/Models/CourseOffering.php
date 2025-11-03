<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseOffering extends Model
{
    protected $guarded = [];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}
