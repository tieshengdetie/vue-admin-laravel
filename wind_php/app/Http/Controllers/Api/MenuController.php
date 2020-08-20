<?php
/**
 * Desc: 菜单管理
 * User: Zhaojinsheng
 * Date: 2020/8/10
 * Time: 08:35
 * Filename:MenuController.php
 */
namespace App\Http\Controllers\Api;

use App\Repository\Permission\MenuRepository as Repository;
use App\Http\Controllers\ApiController;

class MenuController extends  ApiController{



    public function getFisrtMenu(Repository $repository){

//        $result = "[{\"id\":1,\"module\":\"首页\",\"permissionName\":\"首页\",\"permissionCode\":\"home\",\"resourceLevel\":1,\"permissionLevel\":1},{\"id\":2,\"module\":\"订单管理\",\"permissionName\":\"订单管理\",\"permissionCode\":\"order-manage\",\"resourceLevel\":1,\"permissionLevel\":1},{\"id\":3,\"module\":\"产品管理\",\"permissionName\":\"产品管理\",\"permissionCode\":\"goods\",\"resourceLevel\":1,\"permissionLevel\":1},{\"id\":4,\"module\":\"权限管理\",\"permissionName\":\"权限管理\",\"permissionCode\":\"permission\",\"resourceLevel\":1,\"permissionLevel\":1}]";
//
//        $arr = json_decode($result);

        $res = $repository->getFisrtMenu();

        return $this->success($res);
    }

    public function createOrEditMenu(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'menu_name' => 'required|string',
                'menu_code' => 'required|string',
                'type' => 'required|integer',
            ],
            [
                'required' => ':attribute 缺失！',
                'string' =>'attribute 为字符串！',
                'integer' =>'attribute 为整型！'
            ],
            [
                'menu_name' =>'菜单名字',
                'menu_code' => '菜单代码',
                'type' => '菜单类型id',
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $repository->createOrEditMenu();

        return $this->success();
    }

    public function getMenuByRoleId(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'roleId' => 'required|integer',
            ],
            [
                'required' => ':attribute 缺失！',
                'integer' =>'attribute 为整型！'
            ],
            [
                'roleId' =>'角色id',
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $res = $repository->getMenuByRoleId();

        return $this->success($res);
    }

    public function deleteMenu(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'id' => 'required|integer',
            ],
            [
                'required' => ':attribute 缺失！',
                'integer' =>'attribute 为整型！'
            ],
            [
                'id' =>'ID',
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

         $repository->deleteMenu();

        return $this->success();
    }
}