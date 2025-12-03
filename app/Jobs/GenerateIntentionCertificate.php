<?php

namespace App\Jobs;

use App\Models\Intention;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateIntentionCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $intentionId)
    {
        $this->onQueue('certificates');
    }

    public function handle(): void
    {
        $intention = Intention::with(['mass','dedicatees','dedicatee'])->find($this->intentionId);
        if (!$intention) {
            Log::warning('GenerateIntentionCertificate: intention not found', ['id' => $this->intentionId]);
            return;
        }

        if (!$intention->certificate_token) {
            $intention->certificate_token = Str::random(64);
        }

        try {
            $pdf = Pdf::loadView('admin.intentions.certificate', ['intention' => $intention])->setPaper('a4', 'landscape');
            $fileName = sprintf('certificates/intentions/%s-%s.pdf', $intention->id, now()->format('YmdHis'));
            Storage::disk('public')->put($fileName, $pdf->output());

            $intention->forceFill([
                'certificate_path' => $fileName,
                'certificate_generated_at' => now(),
            ])->save();
        } catch (Throwable $e) {
            Log::error('GenerateIntentionCertificate failed', [
                'intention_id' => $intention->id,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
