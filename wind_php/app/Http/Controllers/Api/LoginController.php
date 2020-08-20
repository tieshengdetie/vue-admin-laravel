<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/8
 * Time: 上午10:19
 */


namespace App\Http\Controllers\Api;

use App\Repository\Permission\LoginRepository as Repository;
use Validator;
use App\Http\Controllers\ApiController;

class LoginController extends ApiController
{


    public function login(Repository $Repository)
    {
        //验证表单
        $validateArray = [
            [
                'username' => 'required|string',
                'password' => 'required|string',
                'captcha' => 'required|captcha_api:' . $this->request->input('key'),
                'key' => 'required'
            ],
            [
                'required' => '请填写:attribute',
                'string' => ':attribute 为字符串',

            ],
            [
                'username' => '用户名',
                'password' => '密码',
                'captcha' => '验证码'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }

        $res = $Repository->login();
        //登录交给repository
        if($res['code'] === 0){

            return $this->failed($res['msg']);
        }
        return $this->success(['token' => $res['msg']]);

    }

    /*
     * 获取验证码
     */
    public function getCaptcha()
    {
        $captcha = app('captcha')->create('default', true);
        return $this->success($captcha);
    }


}