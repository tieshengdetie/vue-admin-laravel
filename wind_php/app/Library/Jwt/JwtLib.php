<?php
/**
 * Desc: 用于App 生成token  跨域认证（token 须与前台用户关联）
 * User: Zhaojinsheng
 * Date: 2020/8/4
 * Time: 11:08
 * Filename:JwtLib.php
 */
namespace App\Library\Jwt;

use Firebase\JWT\JWT;


final class JwtLib {


    /**
     * Desc: 生成token
     * User: Zhaojinsheng
     * Date: 2020/8/4
     * Time: 11:53
     * @param $userId
     * @return string
     */
    public static function createJwt($data)
    {
        $key = config('phpjwt.key'); //jwt的签发密钥，验证token的时候需要用到

        $token = array(
            "data" => $data,
            "iss" => config('phpjwt.iss'),//签发组织
            "aud" => config('phpjwt.aud'), //签发作者
            "lat" => config('phpjwt.lat'),
            "nbf" => config('phpjwt.nbf'),
            "exp" => config('phpjwt.exp')
        );

        $jwt =JWT::encode($token, $key,'HS256');

        return $jwt;
    }
    /**
     * Desc: 校验token方法
     * User: Zhaojinsheng
     * Date: 2020/8/4
     * Time: 11:50
     * @param $jwt
     * @return array|\Exception|\Firebase\JWT\ExpiredException|\Firebase\JWT\SignatureInvalidException|Exception
     */
    public  static function verifyJwt($jwt)
    {
        $key = config('phpjwt.key'); //jwt的签发密钥，验证token的时候需要用到

        try {
            $authInfo = JWT::decode($jwt, $key, array('HS256'));

            $authInfo = (array)$authInfo;

            $msg = [];

            if (!empty($authInfo['data'])) {
                $msg = [
                    'status' => 1,
                    'msg' => 'Token验证通过',
                    'data' => $authInfo['data']
                ];
            } else {
                $msg = [
                    'status' => 0,
                    'msg' => 'Token验证不通过,用户信息不存在'
                ];
            }

        } catch (\Firebase\JWT\SignatureInvalidException $e) {

            $msg = [
                'status' => 0,
                'msg' => 'Token无效'
            ];


        } catch (\Firebase\JWT\ExpiredException $e) {

            $msg =  [
                'status' => 0,
                'msg' => 'Token过期'
            ];

        } catch (\Exception $e) {
            $msg = [
                'status' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        return  $msg;
    }




}