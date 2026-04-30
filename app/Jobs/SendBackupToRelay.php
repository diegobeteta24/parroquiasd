<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class SendBackupToRelay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $path,
        public array $meta = []
    ) {
    }

    public function handle(): void
    {
        $url = config('backup-relay.url');
        $key = config('backup-relay.key');
        if (!$url || !$key) {
            Log::warning('Backup relay skipped: missing BACKUP_RELAY_URL or BACKUP_RELAY_KEY');
            return;
        }

        if (!is_file($this->path)) {
            throw new RuntimeException('Backup file not found at '.$this->path);
        }

        [$uploadPath, $uploadName, $isTemp] = $this->preparePayloadFile($this->path);

        try {
            $checksum = hash_file('sha256', $uploadPath);
            $response = Http::timeout(config('backup-relay.timeout', 60))
                ->withHeaders([
                    'X-Relay-Key' => $key,
                    'X-App-Env' => config('app.env'),
                ])
                ->attach('file', fopen($uploadPath, 'r'), $uploadName)
                ->post($url, [
                    'filename' => $uploadName,
                    'original_filename' => basename($this->path),
                    'ran_at' => $this->meta['ran_at'] ?? now()->toIso8601String(),
                    'checksum' => $checksum,
                    'app_environment' => config('app.env'),
                    'recipients' => implode(',', $this->meta['recipients'] ?? config('backup-relay.recipients', [])),
                ]);

            if ($response->failed()) {
                throw new RuntimeException('Backup relay failed: '.$response->body());
            }

            Log::info('Backup relay sent', [
                'path' => $this->path,
                'upload' => $uploadName,
                'response_status' => $response->status(),
            ]);
        } finally {
            if ($isTemp && is_file($uploadPath)) {
                @unlink($uploadPath);
            }
        }
    }

    /**
     * @return array{0:string,1:string,2:bool}
     */
    protected function preparePayloadFile(string $path): array
    {
        $shouldCompress = config('backup-relay.compress', true);
        if (!$shouldCompress || Str::endsWith($path, '.gz')) {
            return [$path, basename($path), false];
        }

        $compressedPath = $path.'.gz';
        $source = fopen($path, 'rb');
        if ($source === false) {
            throw new RuntimeException('Unable to open backup for compression: '.$path);
        }

        $compressed = gzopen($compressedPath, 'wb9');
        if ($compressed === false) {
            fclose($source);
            throw new RuntimeException('Unable to create compressed backup at '.$compressedPath);
        }

        while (!feof($source)) {
            $chunk = fread($source, 65536);
            if ($chunk === false) {
                fclose($source);
                gzclose($compressed);
                throw new RuntimeException('Error reading backup during compression: '.$path);
            }
            gzwrite($compressed, $chunk);
        }

        fclose($source);
        gzclose($compressed);

        return [$compressedPath, basename($compressedPath), true];
    }
}
