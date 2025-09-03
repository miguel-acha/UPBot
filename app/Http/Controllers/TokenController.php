<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AccessToken;
use App\Models\ResponsePayload;
use App\Models\TokenAttempt;

class TokenController extends Controller
{
    public function enter() { return view('token.enter'); }

    public function verify(Request $r)
    {
        $r->validate(['code' => 'required|string']);
        $code = trim($r->input('code'));
        $user = Auth::user();

        // 1) Solo tokens del alumno logueado, activos y no expirados
        $token = AccessToken::where('student_id', $user->student_id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()->first();

        $ok = $token && Hash::check($code, $token->token_hash);

        TokenAttempt::create([
            'access_token_id' => $token?->id,
            'ip' => $r->ip(),
            'user_agent' => $r->userAgent(),
            'success' => $ok,
        ]);

        if (!$ok) {
            return back()->withErrors(['code' => 'Código inválido o expirado']);
        }

        // 2) Resolver payload exacto del alumno + interacción
        $payload = ResponsePayload::where('interaction_id', $token->interaction_id)
            ->where('student_id', $user->student_id)
            ->latest()->first();

        if (!$payload) {
            return back()->withErrors(['code' => 'No se encontró información vinculada']);
        }

        // 3) Guardar en sesión lo mínimo para el siguiente paso
        session([
            'pending_token_id'  => $token->id,
            'pending_payload_id'=> $payload->id,
        ]);

        return redirect()->route('id.show');
    }
}
