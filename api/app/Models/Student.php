<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'program_id','upb_code','ci','email_institucional',
        'telefono','full_name','status',
    ];

    // 🔁 Relaciones existentes
    public function responses()
    {
        return $this->hasMany(ResponsePayload::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    // Relación inversa con User
    public function user()
    {
        return $this->hasOne(User::class);
    }

    // Opcional: relación con Program (si tenés la tabla programs)
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // Scope para filtros reutilizables
    public function scopeFilter($q, array $f)
    {
        return $q
            ->when($f['program_id'] ?? null, fn($qq,$v)=>$qq->where('program_id',$v))
            ->when($f['status'] ?? null, fn($qq,$v)=>$qq->where('status',$v))
            ->when($f['q'] ?? null, function($qq,$v){
                $like = "%{$v}%";
                $qq->where(function($w) use($like){
                    $w->where('full_name','like',$like)
                      ->orWhere('email_institucional','like',$like)
                      ->orWhere('upb_code','like',$like)
                      ->orWhere('ci','like',$like);
                });
            });
    }
}
