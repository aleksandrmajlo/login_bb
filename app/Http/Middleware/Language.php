<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;

class Language
{

    public function __construct(Application $app, Request $request) {
        $this->app = $app;
        $this->request = $request;
    }


    public function handle($request, Closure $next)
    {
        $this->app->setLocale(session('lng', config('app.locale')));
        return $next($request);
    }
}
