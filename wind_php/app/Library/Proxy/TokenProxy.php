<?php

namespace App\Library\Proxy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\GuzzleException;


class TokenProxy
{
    protected $http;

    /**
     * TokenProxy constructor.
     * @param $http
     */
    public function __construct()
    {
        $this->http = new Client();
    }

    public function checkToken($token)
    {
        $ssoHttp = config('common.sso.infoip');;
        $ssoUrl = $ssoHttp . 'Identity/GetBasicInfo';
        return $this->sendHttp($ssoUrl, "GET", [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],

        ]);

    }

    /*
     * 用户名和密码方式获取token
     */
    public function userLogin($username,$password){
        $ssoHttp = config('common.sso.tokenip');
        $ssoUrl = $ssoHttp . 'connect/token';
        return $this->sendHttp($ssoUrl, "POST", [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => config('common.sso.client_id'),
                'client_secret' => config('common.sso.client_secret'),
                'password' => $password,
                'username' => $username,
                'scopes' => config('common.sso.scopes'),

            ]
        ]);
    }
    /*
     * 授权码方式获取token
     */
    public function getToken($code)
    {
        $ssoHttp = config('common.sso.tokenip');;
        $ssoUrl = $ssoHttp . 'connect/token';
        return $this->sendHttp($ssoUrl, "POST", [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('common.sso.client_id'),
                'client_secret' => config('common.sso.client_secret'),
                'redirect_uri' => config('common.sso.redirect_uri'),
                'code' => $code,
                'scopes' => config('common.sso.scopes'),

            ]
        ]);

    }

    //
    public function sendHttp($url, $method, $params)
    {

        $result = [
            'message' => '',
            'success' => '',
            'data' => ''
        ];
        try {
            $res = $this->http->request($method, $url, $params);
            $data = $res->getBody()->getContents();
            $data = json_decode($data, true);
            if (json_last_error() != 0) {
                $result['message'] = 'Json decode error';
                $result['success'] = false;
            } else {
                $result['data'] = $data;
                $result['success'] = true;
            }
        } catch (GuzzleException $e) {

            $result['message'] = $e->getMessage();
            $result['success'] = false;
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
            $result['success'] = false;
        }
        return $result;

    }

}