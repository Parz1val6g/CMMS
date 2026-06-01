<?php

namespace App\Shared\Services;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SandboxScanService
{
    private string $binaryPath;

    public function __construct()
    {
        $this->binaryPath = env('SANDBOX_BINARY_PATH', '/var/www/sandbox/sandbox/sandbox_engine');
    }

    public function scan(string $filePath): void
    {
        if (!is_executable($this->binaryPath)) {
            return;
        }

        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open([$this->binaryPath, $filePath], $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new \RuntimeException('Failed to launch sandbox scanner.');
        }

        fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);

        $result = json_decode($stdout, true);

        if (!$result || !isset($result['status'])) {
            throw new \RuntimeException('Sandbox scanner returned invalid output.');
        }

        if ($result['status'] !== 'clean') {
            $reason = $result['reason'] ?? 'File failed security scan.';
            throw new UnprocessableEntityHttpException($reason);
        }
    }
}
