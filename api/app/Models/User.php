<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Campos asignables en masa.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'is_active',
        'must_change_password',
    ];

    /**
     * Campos ocultos en serialización.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts de atributos.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'    => 'datetime',
            'password'             => 'hashed',
            'is_active'            => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at'  => 'datetime',
        ];
    }

    /**
     * Relación: el usuario pertenece a un estudiante (opcional).
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Helper: ¿es admin?
     */
    public function isAdmin(): bool
    {
        return ($this->role ?? null) === 'admin';
    }

    /**
     * Helpers para el flujo de cambio de contraseña.
     */
    public function requirePasswordChange(): void
    {
        $this->must_change_password = true;
        $this->save();
    }

    public function markPasswordChanged(): void
    {
        $this->must_change_password = false;
        $this->password_changed_at = now();
        $this->save();
    }

    /**
     * Scope: solo activos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
