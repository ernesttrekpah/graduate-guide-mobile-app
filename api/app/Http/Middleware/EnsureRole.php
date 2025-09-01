<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnsureRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthenticated');
        }

        // normalize needles to lowercase
        $needles = collect($roles)->map(fn($r) => strtolower($r))->values()->all();

        // group the OR conditions inside the relation scope to avoid leakage
        $has = $user->roles()
            ->where(function ($q) use ($needles) {
                $q->whereIn(DB::raw('LOWER(code)'), $needles)
                    ->orWhereIn(DB::raw('LOWER(name)'), $needles);
            })
            ->exists();

        if (! $has) {
            // TEMP: log what the server actually sees to catch env/token mismatches
            Log::warning('EnsureRole: denied', [
                'uid'     => $user->id,
                'needles' => $needles,
                'roles'   => $user->roles()->get(['id', 'code', 'name'])->toArray(),
            ]);
            abort(403, 'User does not have the right roles');
        }

        return $next($request);
    }
}
