<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class VersionController extends Controller
{
    public function show()
    {
        return response()->json([
            'data' => [
                'app'      => config('app.name'),
                'env'      => config('app.env'),
                'version'  => config('version.version'),
                'commit'   => config('version.commit'),
                'built_at' => config('version.built_at'),
                'timezone' => config('app.timezone'),
                'php'      => PHP_VERSION,
                'laravel'  => app()->version(),
            ],
        ]);
    }
}
