<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClampPerPage
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('api/*')) {
            $min = (int) env('API_PAGE_SIZE_MIN', 1);
            $max = (int) env('API_PAGE_SIZE_MAX', 50);
            $def = (int) env('API_PAGE_SIZE_DEFAULT', 20);

            $pp = $request->integer('per_page', $def);
            if ($pp < $min) {
                $pp = $min;
            }

            if ($pp > $max) {
                $pp = $max;
            }

            $request->merge(['per_page' => $pp]);
        }
        return $next($request);
    }
}
