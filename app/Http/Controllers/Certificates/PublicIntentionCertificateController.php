<?php

namespace App\Http\Controllers\Certificates;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateIntentionCertificate;
use App\Models\Intention;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicIntentionCertificateController extends Controller
{
    public function __invoke(Request $request, Intention $intention, string $token)
    {
        if (!$intention->certificate_token || !hash_equals($intention->certificate_token, $token)) {
            abort(404);
        }

        if (!$intention->certificate_path || !Storage::disk('public')->exists($intention->certificate_path)) {
            GenerateIntentionCertificate::dispatchSync($intention->id);
            $intention->refresh();
        }

        if (!$intention->certificate_path || !Storage::disk('public')->exists($intention->certificate_path)) {
            abort(503, 'Certificado no disponible aún. Intenta en unos minutos.');
        }

        $filename = sprintf('Constancia-%s.pdf', $intention->code);
        return Storage::disk('public')->download($intention->certificate_path, $filename);
    }
}
