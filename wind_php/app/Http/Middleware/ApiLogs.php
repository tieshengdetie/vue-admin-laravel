<?php

namespace App\Http\Middleware;
use Closure;

class ApiLogs
{
    public function handle($request, Closure $next)
    {
        $arrInfo = [
            'path' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'params'=>$request->all(),
        ];
//        $strInfo = json_encode($arrInfo, JSON_UNESCAPED_UNICODE);
        app('api-log')->debug($arrInfo, compact('time'));
        return $next($request);
    }
}