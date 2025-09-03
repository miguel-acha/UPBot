<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicDocument extends Model
{
    protected $fillable = ['student_id','type','semester_code','file_path','data_json_enc','summary'];
    // Si vas a cifrar/descifrar con helpers, lo manejas en controlador. Si prefieres casts:
    // protected $casts = ['data_json_enc'=>'encrypted:array'];

    public function student(){ return $this->belongsTo(Student::class); }
}
