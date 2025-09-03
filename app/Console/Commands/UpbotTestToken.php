<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use App\Models\Interaction;
use App\Models\ResponsePayload;
use App\Services\AccessTokenService;

class UpbotTestToken extends Command
{
    protected $signature = 'upbot:test-token {email=alumno1@upb.edu} {--sem=2025-2}';
    protected $description = 'Emite un token de prueba para un estudiante y una interacción demo';

    public function handle(AccessTokenService $tokens): int
    {
        $email = $this->argument('email');
        $sem   = $this->option('sem');

        $student = Student::where('email_institucional', $email)->first();
        if (!$student) {
            $this->error("No se encontró estudiante con email: {$email}");
            return self::FAILURE;
        }

        // Crea interacción simple (si no hay)
        $interaction = Interaction::create([
        'channel'           => 'other', // <-- antes estaba 'cli'
        'requester_contact' => $email,
        'intent'            => 'constancia',
        'sensitivity_level' => 'private',
        'student_id'        => $student->id,
        'raw_text'          => "Demo CLI: constancia {$sem}",
        'meta_json'         => [],
        ]);


        // Crea un payload (demo) y emite token
        $payload = ResponsePayload::create([
            'interaction_id'        => $interaction->id,
            'student_id'            => $student->id,
            'payload_type'          => 'json_data',
            'academic_document_id'  => null,
            'data_json_enc'         => encrypt(['message' => "Payload privado demo {$sem}"]),
            'summary'               => 'Información solicitada (demo CLI)',
            'sensitivity_level'     => 'private',
        ]);

        [$code, $token] = $tokens->emitToken($student->id, $interaction->id);

        $this->info("✅ Token emitido para {$email}");
        $this->line("Código: {$code}");
        $this->line("Expira: {$token->expires_at}");
        $this->line("Usa este código en /token y verifica con el CI del alumno.");
        $this->line("Interaction ID: {$interaction->id} | Payload ID: {$payload->id}");

        return self::SUCCESS;
    }
}
