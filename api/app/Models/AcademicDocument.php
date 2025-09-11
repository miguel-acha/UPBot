<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicDocument extends Model
{
    protected $fillable = [
        'student_id','type','semester_code','file_path','data_json_enc','summary',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
