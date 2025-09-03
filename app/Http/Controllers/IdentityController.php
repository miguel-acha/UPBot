<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccessToken;
use App\Models\Student;
use App\Models\IdentityVerification;

class IdentityController extends Controller
{
    public function show() { return view('token.verify'); }

    public function verifyCi(Request $r)
    {
        $r->validate(['ci' => 'required|string']);
        $token = AccessToken::find(session('pending_token_id'));
        $payloadId = session('pending_payload_id');

        if (!$token || $token->status !== 'active' || !$payloadId) {
            return redirect()->route('token.enter')->withErrors(['code' => 'Token inválido']);
        }

        $student = Student::find($token->student_id);
        if (!$student || $student->ci !== $r->input('ci')) {
            return back()->withErrors(['ci' => 'CI incorrecto']);
        }

        IdentityVerification::create([
            'student_id'  => $student->id,
            'method'      => 'ci',
            'status'      => 'passed',
            'verified_at' => now(),
        ]);

        $token->update([
            'status'       => 'redeemed',
            'redeemed_at'  => now(),
            'redeemed_ip'  => $r->ip(),
        ]);

        return redirect()->route('response.show', ['payload' => $payloadId]);
    }
}
