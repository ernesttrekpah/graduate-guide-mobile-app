<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function __invoke()
    {
        $checks = [
            'db'               => false,
            'cache'            => false,
            'queue'            => config('queue.default'),
            'storage_writable' => is_writable(storage_path('framework')),
        ];

        // DB
        try {
            DB::select('select 1');
            $checks['db'] = true;
        } catch (Throwable $e) { /* leave false */}

        // Cache
        try {
            Cache::put('health:ping', '1', 5);
            $checks['cache'] = Cache::get('health:ping') === '1';
        } catch (Throwable $e) { /* leave false */}

        $ok = $checks['db'] && $checks['cache'] && $checks['storage_writable'];

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'time'   => now()->toIso8601String(),
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }
}
