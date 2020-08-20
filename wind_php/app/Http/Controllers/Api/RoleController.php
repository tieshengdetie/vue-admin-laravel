<?php
/**
 * Desc: 角色管理控制器
 * User: Zhaojinsheng
 * Date: 2020/8/8
 * Time: 12:58
 * Filename:RoleController.php
 */
namespace App\Http\Controllers\Api;

use App\Repository\Permission\RoleRepository as Repository;
use App\Http\Controllers\ApiController;

class RoleController extends ApiController{


    public function getRoleList(Repository $repository){

        $arr = $repository->getRoleList();

        return $this->success($arr);
    }

    public function createOrEditRole(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'roleName' => 'required|string',
            ],
            [
                'required' => ':attribute 缺失！',
                'string' =>'attribute 为字符串！'
            ],
            [
                'roleName' =>'角色名字'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $repository->addOrEditRole();

        return $this->success();


    }

    public function deleteRole(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'id' => 'required|integer',
            ],
            [
                'required' => ':attribute 缺失！',
                'integer'=>':attribute 为数字！'
            ],
            [
                'id' =>'Id'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $repository->deleteRole();

        return $this->success();

    }

    public function handlePower(Repository $repository){
//验证表单
        $validateArray = [
            [
                'roleId' => 'required|integer',
                'menuArr' => 'required|array',
            ],
            [
                'required' => '请选择 :attribute ',
                'integer'=>':attribute 为数字！',
                'array' =>'参数错误'
            ],
            [
                'id' =>'Id',
                'menuArr'=>'权限'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $repository->handlePower();

        return $this->success();

    }
}