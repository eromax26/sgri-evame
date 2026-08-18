<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $collaborateur = $request->user();

        if (! $collaborateur) {
            return redirect()->route('login');
        }

        foreach ($roles as $role) {
            if ($collaborateur->aLeRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Vous n\'avez pas les droits necessaires pour acceder a cette page.');
    }
}