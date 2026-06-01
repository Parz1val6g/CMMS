<?php

namespace App\Shared\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SandboxScanService
{
    private string $url;

    public function __construct()
    {
        $this->url = env('SANDBOX_SCANNER_URL', 'http://scanner:8765/scan');
    }

    public function scan(string $filePath): void
    {
        if (!env('SANDBOX_ENABLED', false)) {
            return;
        }

        $response = Http::timeout(35)
            ->withBody(file_get_contents($filePath), 'application/octet-stream')
            ->post($this->url);

        if ($response->failed()) {
            throw new \RuntimeException('Sandbox scanner unreachable.');
        }

        $result = $response->json();

        if (!$result || !isset($result['status'])) {
            throw new \RuntimeException('Sandbox scanner returned invalid output.');
        }

        if ($result['status'] !== 'clean') {
            $reason = $result['reason'] ?? 'File failed security scan.';
            throw new UnprocessableEntityHttpException($reason);
        }
    }
}
