<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Interaction;
use App\Models\Student;

class InteractionController extends Controller
{
    public function store(Request $r)
    {
        $studentId = null;
        if ($email = data_get($r->input('student_hint', []), 'email')) {
            $studentId = Student::where('email_institucional', $email)->value('id');
        } elseif ($ci = data_get($r->input('student_hint', []), 'ci')) {
            $studentId = Student::where('ci', $ci)->value('id');
        }

        $interaction = Interaction::create([
            'channel'           => $r->input('channel','other'),
            'requester_contact' => $r->input('requester_contact'),
            'intent'            => $r->input('intent_hint'),
            'sensitivity_level' => $r->input('sensitivity_hint','public'),
            'student_id'        => $studentId,
            'raw_text'          => $r->input('message'),
            'meta_json'         => $r->input('meta', []),
        ]);

        return response()->json([
            'interaction_id'    => $interaction->id,
            'student_id'        => $studentId,
            'sensitivity_level' => $interaction->sensitivity_level,
            'intent'            => $interaction->intent,
        ]);
    }
}
