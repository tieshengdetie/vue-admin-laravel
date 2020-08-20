<?php
/**
 * Desc: 用户控制器
 * User: Zhaojinsheng
 * Date: 2020/8/7
 * Time: 08:51
 * Filename:UserController.php
 */

namespace App\Http\Controllers\Api;

use App\Repository\Permission\UserRepository as Repository;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{

    public function createUser(Repository $repository)
    {

        //验证表单
        $validateArray = [
            [
                'login_name' => 'required|string|between:1,50',
                'tempRoleIds' => 'array',
                'realname' => 'required|string|between:1,20',
                'mobile' => 'required|regex:/^1[345789][0-9]{9}$/',
                'address' => 'string',
                'sex' => 'integer',
                'email' => 'required|email',
                'dept_id' => 'required|integer',
                'post_id' => 'required|integer'
            ],
            [
                'required' => ':attribute为必填项',
                'string' => ':attribute 为字符串',
                'integer' => ':attribute 为整型数字',
                'array' => ':attribute 为数组',
                'regex' => ':attribute 格式不正确',
                'email' => '请填写正确的邮箱'
            ],
            [
                'login_name' => '登录名',
                'tempRoleIds' => '角色',
                'realname' => '真实姓名',
                'sex' => '性别',
                'mobile' => '手机',
                'address' => '地址',
                'email' => '邮箱',
                'dept_id' => '部门',
                'post_id' => '职位'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $res = $repository->createOrEditUser();

        if($res['code'] === 0){

            return $this->failed($res['msg']);
        }

        return $this->success();


    }

    /**
     * Desc: 用户列表
     * User: Zhaojinsheng
     * Date: 2020/8/7
     * Time: 08:57
     * @return mixed
     */
    public function getUserList(Repository $repository)
    {

        $arr = $repository->getUserList();

        return $this->success($arr);

    }

    /**
     * Desc: 设置是否禁用
     * User: Zhaojinsheng
     * Date: 2020/8/8
     * Time: 10:45
     * @param Repository $repository
     */
    public function setIsUse(Repository $repository)
    {
        //验证表单
        $validateArray = [
            [
                'id' => 'required',
                'is_use' => 'required',
            ],
            [
                'required' => '参数错误,非法请求!',
            ],
            [

            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $repository->setIsUse();

        return $this->success();
    }

    /**
     * Desc: 重置密码
     * User: Zhaojinsheng
     * Date: 2020/8/8
     * Time: 11:46
     */
    public function resetPwd(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'id' => 'required',
            ],
            [
                'required' => '参数错误,非法请求!',
            ],
            [

            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $repository->resetPwd();

        return $this->success();
    }
    /*
     * 获取用户信息
     */
    public function getUserInfo(Repository $repository)
    {


        $res = $repository->getUserInfoById();

        return $this->success($res);

    }

    public function getUserByName(Repository $repository){

        $res = $repository->getUserByName();

        return $this->success($res);
    }

}