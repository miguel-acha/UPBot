<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name','email','password',
        // campos del dump/migración adicional:
        'role',            // 'admin' | 'student'
        'student_id',      // FK a students.id
        'is_active',       // bool
        'must_change_password', // bool (si decides usarlo luego)
    ];

    protected $hidden = ['password','remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'           => 'hashed',
            'is_active'          => 'boolean',
            'must_change_password' => 'boolean',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function isAdmin(): bool
    {
        return ($this->role ?? null) === 'admin';
    }
}
