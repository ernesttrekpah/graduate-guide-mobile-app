<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VersionHeader
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $ver      = config('version.version');
        if ($request->is('api/*') && $ver) {
            $response->headers->set('X-App-Version', $ver);
        }
        return $response;
    }
}
