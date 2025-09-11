<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'program_id','upb_code','ci','email_institucional',
        'telefono','full_name','status',
    ];

    public function responses()
    {
        return $this->hasMany(ResponsePayload::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }
}
