<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponsePayload extends Model
{
    // Si tu tabla se llama exactamente response_payloads, no necesitas $table.
    // protected $table = 'response_payloads';

    protected $fillable = [
        'interaction_id',
        'student_id',
        'payload_type',
        'academic_document_id',
        'data_json_enc',
        'summary',
        'sensitivity_level', // 👈 nombre real
    ];

    protected $casts = [
        'interaction_id'        => 'integer',
        'student_id'            => 'integer',
        'academic_document_id'  => 'integer',
        'summary'               => 'string',
        'payload_type'          => 'string',
        'sensitivity_level'     => 'string',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
    ];

    public function interaction()
    {
        return $this->belongsTo(Interaction::class, 'interaction_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function document()
    {
        return $this->belongsTo(AcademicDocument::class, 'academic_document_id');
    }

    public function scopeForPortalUser($query, \App\Models\User $user)
    {
        return $query->where(function ($w) use ($user) {
            if (!empty($user->student_id)) {
                $w->orWhere('student_id', $user->student_id);
            }
            $w->orWhereHas('student', function ($qs) use ($user) {
                $qs->where('email_institucional', $user->email);
            });
        });
    }
}
