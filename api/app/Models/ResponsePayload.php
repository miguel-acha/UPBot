<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsePayload extends Model
{
    protected $fillable = [
        'interaction_id','student_id','payload_type',
        'academic_document_id','data_json_enc','summary','sensitivity_level',
    ];

    public function interaction()
    {
        return $this->belongsTo(Interaction::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function document()
    {
        return $this->belongsTo(AcademicDocument::class, 'academic_document_id');
    }
}
