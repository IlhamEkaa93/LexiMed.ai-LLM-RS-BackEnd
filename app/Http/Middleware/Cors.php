<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        // Tangani Preflight (OPTIONS) secara eksplisit
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
                ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization, Accept, Origin, X-Token-Auth')
                ->header('Access-Control-Allow-Credentials', 'true');
        }

        $response = $next($request);

        // Tambahkan header ke semua response (Success maupun Error)
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
            ->header('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization, Accept, Origin, X-Token-Auth')
            ->header('Access-Control-Allow-Credentials', 'true');
    }
}
