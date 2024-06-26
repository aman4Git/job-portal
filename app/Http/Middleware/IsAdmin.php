<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated
        if(!$request->user()){
            return redirect()->route('home');
        }

        // Check if the requested user is an admin
        if($request->user()->role != 'admin'){
            session()->flash('error', 'You are not authorized to access this page.');
            return redirect()->route('account.profile');
        }
        return $next($request);
    }
}
