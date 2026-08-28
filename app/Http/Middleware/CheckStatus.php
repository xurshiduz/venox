<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class CheckStatus
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        //If the status is not approved redirect to login 
        
        if(Auth::check() && Auth::user()->status != '1'){
            auth('web')->logout();
            return redirect('/login')->with('error', 'Ваш аккаунт заблокирован');
        }

        return $response;
    }
}
