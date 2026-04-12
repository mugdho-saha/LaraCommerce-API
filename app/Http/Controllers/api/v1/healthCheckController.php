<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class healthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $database = $this->databaseStatus();

        $body = [
            'status' => $database['connected'] ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'system' => [
                'environment' => config('app.env'),
                'timezone' => config('app.timezone'),
            ],
            'database' => $database,
            'versions' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
            ],
        ];

        $code = $database['connected'] ? 200 : 503;

        return response()->json($body, $code);
    }

    /**
     * @return array{connected: bool, driver: string|null, error: string|null}
     */
    private function databaseStatus(): array
    {
        $driver = config('database.default');

        try {
            DB::connection()->getPdo();

            return [
                'connected' => true,
                'driver' => is_string($driver) ? $driver : null,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'driver' => is_string($driver) ? $driver : null,
                'error' => config('app.debug') ? $e->getMessage() : 'Database connection failed.',
            ];
        }
    }
}
