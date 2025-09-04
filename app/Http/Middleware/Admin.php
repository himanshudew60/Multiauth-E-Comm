<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
       if(auth()->check()){
        $user= auth()->user();
        If($user->role!=='admin' && $request -> is('admin/*')){
            return redirect('user/dashboard');
        }
       }
       return $next($request);
    }
}
