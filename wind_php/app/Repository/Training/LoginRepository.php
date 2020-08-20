<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/15
 * Time: 上午9:49
 */

namespace App\Repository\Training;

use App\Repository\BaseRepository;
use App\Library\Proxy\TokenProxy;

class LoginRepository extends BaseRepository
{

    public function login()
    {
        $username = $this->request->get('username');
        $password = $this->request->get('password');
        //sso 登录
        $proxy = new TokenProxy();
        $ssoUrl = config('common.apiUrl.login');
        $loginRes = $proxy->sendHttp($ssoUrl, "POST", [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => config('common.sso.client_id'),
                'client_secret' => config('common.sso.client_secret'),
                'password' => $password,
                'username' => $username,
                'scopes' => config('common.sso.scopes'),

            ]
        ]);

        if($loginRes['success']==false){
            return ['success'=>false,'message'=>'用户名或密码错误','data'=>''];
        }else{
            //如果登录成功获取用户信息
            $token = $loginRes['data']['token_type']." ".$loginRes['data']['access_token'];
            $resUserInfo = $this->getUser($token);
            $userInfo['name'] = $resUserInfo['data']['result']['name'];
            $loginRes['data']['userinfo'] = $userInfo;
            return ['success'=>true,'message'=>'','data'=>$loginRes['data']];
        }

    }
    public function getUserInfo(){
        //获取token
        if($this->request->hasHeader('authorization')===false){
            return ['success'=>false,'message'=>'Missing authorization header','code'=>401];
        }
        $token = $this->getToken();
        $result = $this->getUser($token);
        if($result['success']===false){
            return ['success'=>false,'message'=>'sso error','code'=>401];
        }

        if($result['data']['success']===false){
            return ['success'=>false,'message'=>'Access token is invalid','code'=>401];
        }
        $userInfo = $result['data']['result'];
        return ['success'=>true,'message'=>'','data'=>$userInfo];
    }
    /*
     * 获取用户信息
     */
    public function getUser($token){
        //验证token
        $proxy = new TokenProxy();
        $ssoUrl = config('common.apiUrl.checkToken');
        return  $proxy->sendHttp($ssoUrl, "GET", [
            'headers' => [
                'Authorization' => $token,
                'Accept' => 'application/json',
            ],

        ]);
    }
}