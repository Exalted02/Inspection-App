<?php

namespace App\Http\Middleware;
  
use Closure;
use App;
  
class LanguageManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		if(empty(session()->has('locale')))
		{
			session()->put('locale','en');
		}
		
        if (session()->has('locale')) {
            App::setLocale(session()->get('locale'));
        }
          
        return $next($request);
    }
}
