<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class ApplyLocale {
    public function handle(Request $request, Closure $next): Response {
        $locale=$request->session()->get('locale',config('app.locale','uz'));
        app()->setLocale(in_array($locale,['uz','ru','en'])?$locale:'uz');
        return $next($request);
    }
}
