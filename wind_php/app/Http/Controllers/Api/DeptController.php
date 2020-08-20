<?php
/**
 * Desc: 部门管理
 * User: Zhaojinsheng
 * Date: 2020/8/12
 * Time: 11:01
 * Filename:DeptController.php
 */
namespace App\Http\Controllers\Api;

use App\Repository\Permission\DeptRepository as Repository;
use App\Http\Controllers\ApiController;

class DeptController extends  ApiController{


    public function createOrEditDept(Repository $repository){

        //验证表单
        $validateArray = [
            [
                'name' => 'required|string',
            ],
            [
                'required' => ':attribute 缺失！',
                'string' =>'attribute 为字符串！'
            ],
            [
                'name' =>'部门名称'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }

        $res = $repository->createOrEditDept();

        return $this->success($res);
    }

    public function getDeptData(Repository $repository){

        $res = $repository->getDeptData();

        return $this->success($res);
    }

    public function deleteDept(Repository $repository){

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
                'id' =>'ID'
            ]
        ];

        $msg = $this->validateParam($validateArray);

        if($msg !== true){

            return $this->failed($msg);
        }
        $repository->deleteDept();

        return $this->success();
    }
}