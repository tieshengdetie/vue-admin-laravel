<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/15
 * Time: 上午9:49
 */

namespace App\Repository\Permission;

use App\Repository\BaseRepository;
use App\Library\Password\PasswordHash;
use App\Library\Jwt\JwtLib;
use Illuminate\Support\Facades\DB;

class LoginRepository extends BaseRepository
{

    public function login()
    {
        $username = $this->request->username;
        $password = $this->request->password;

        //先根据用户名查询当前用户是否存在
        $objModel = DB::table("wind_user");

        $userNameInfo = $objModel->select("id","login_name","password")->where('login_name',$username)->first();

        if(!$userNameInfo){

            return ['code'=>0,'msg'=>'用户名不存在'];
        }

        //对比密码
        $isTrue = PasswordHash::verifyPassword($password,$userNameInfo->password);

        if($isTrue === false){

            return ['code'=>0,'msg'=>'密码错误'];
        }
        $userInfo = [
            'id'=> $userNameInfo->id,
            'login_name'=> $userNameInfo->login_name
        ];

        $token = JwtLib::createJwt($userInfo);

        //跟新登录时间
        $objModel->where('id',$userNameInfo->id)->update(['last_login_time'=>date("Y-m-d H:i:s")]);


        return ['code'=>1,'msg'=>$token];

    }
    public function getUserInfo(){
        //获取token

    }

}