<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\AccessToken;

class AccessTokenService
{
    /**
     * Genera un código tipo XZ91-K3LM, guarda su hash y devuelve [code, AccessToken]
     */
    public function emitToken(
        int $studentId,
        int $interactionId,
        string $purpose = 'view_protected_response',
        string $ttl = '+10 minutes'
    ): array {
        $code = strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        $hash = Hash::make($code);

        $token = AccessToken::create([
            'interaction_id' => $interactionId,
            'student_id'     => $studentId,
            'token_hash'     => $hash,
            'token_hint'     => substr($code, 0, 2).'**-'.substr($code, -2),
            'purpose'        => $purpose,
            'status'         => 'active',
            'max_uses'       => 1,
            'expires_at'     => now()->modify($ttl),
        ]);

        return [$code, $token];
    }

    /**
     * Verifica el código ingresado contra el hash guardado
     */
    public function checkCode(string $code, AccessToken $token): bool
    {
        return Hash::check($code, $token->token_hash);
    }
}
