<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
        // 'http://localhost:8000/api/orden',
        // 'http://localhost:8000/api/resenia',
        // 'http://apicarta.balanceado.com.ar/api/resenia',
        'http://localhots:8000/categories/{id}',
        'http://localhots:8000/products/{id}'

    ];
}
