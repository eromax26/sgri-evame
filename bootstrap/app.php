<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware){
        $middleware->alias([
            'role' => App\Http\Middleware\CheckRole::class,
        ]);

        // Fait confiance aux en-tetes X-Forwarded-* (necessaire derriere un tunnel/proxy
        // comme Cloudflare Tunnel, sinon Laravel genere des URLs en http:// sur une page https).
        // X-Forwarded-Host est volontairement exclu : le faire confiance depuis n'importe
        // quel client permettrait a un attaquant de falsifier l'hote utilise dans les liens
        // generes (ex. lien de reinitialisation de mot de passe envoye par email).
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
