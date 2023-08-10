<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
       
 
        if (auth()->check() && auth()->user()->is_chef == 2) {
        //  dd('hello');
            return $next($request);
           
        }

        return Redirect::route('login')->withErrors(['message' => 'Unauthorized']);
    }
}
