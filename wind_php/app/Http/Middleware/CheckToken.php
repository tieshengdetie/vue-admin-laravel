<?php

namespace App\Http\Middleware;

use App\Library\Traits\ApiResponse;
use Closure;
use App\Library\Jwt\JwtLib;

class CheckToken
{
    use ApiResponse;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //获取token
        if($request->hasHeader('authorization')===false){

            return $this->failed('Missing authorization header',401);
        }
        $header = $request->header('authorization');
        //验证token
        $token = str_replace('Bearer','',$header);

        $token = trim($token);

        //验证token
        $result = JwtLib::verifyJwt($token);

        if($result['status']===0){

            return $this->failed($result['msg'],401);
        }


        $userInfo = $result['data'];

        //将用户信息存入request
        $request->attributes->add(['userInfo'=>$userInfo]);

        return $next($request);
    }
}
