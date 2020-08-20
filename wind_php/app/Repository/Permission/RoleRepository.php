<?php
/**
 * Created by PhpStorm.
 * User: 赵金生
 * Date: 2020-8-8
 * Time: 11:49
 */
namespace App\Repository\Permission;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;

class RoleRepository extends BaseRepository{


    /*
     *  添加角色
     */
    public function addOrEditRole(){
        //接受参数
        $roleName = $this->request->roleName;

        $id = $this->request->id;

        $arrFiled =[

            'name'=>$roleName,
        ];

        if(intval($id)>0){

            DB::table('wind_role')->where('id',$id)->update($arrFiled);

        }else{

            DB::table('wind_role')->Insert($arrFiled);
        }



    }
    /*
     * 编辑角色
     */
    public function editRole(){
        $roleName = $this->request->rolename;
        $id = $this->request->id;
        $arrFiled =[
            'name'=>$roleName,
        ];
        return DB::table('role')->where(['id'=>$id])->update($arrFiled);
    }
    /*
     * 获取角色列表
     */
    public function getRoleList(){

        $pageSize = $this->request->pageSize ? $this->request->pageSize : config('common.pageSize');

        $roleName = $this->request->roleName;

        $where = [];

        if($roleName){

            $where =[['name','like','%'.$roleName.'%']];

        }

        $objModle = DB::table('wind_role');

        $select = [
            'id',
            'name as roleName',
            'permission'
        ];

        $strSelect = implode(',', $select);

        $objModle->select(DB::raw($strSelect));

        $objRoleList = $objModle->where($where)->paginate($pageSize);

        //处理数据
        if(!$objRoleList->isEmpty()){

            foreach($objRoleList as $key=>$item){

                $strPermission = trim(trim($item->permission,','));

                $arrPerIds = explode(',',$strPermission);

                $permission =  DB::table('wind_menu')->select('id','menu_name')->whereIn('id',$arrPerIds)->get();

                $item->objPermission=$permission;
            }
        }

        return $this->setPage($objRoleList->toArray());
    }

    public function deleteRole(){

        $id = $this->request->id;

        DB::table('wind_role')->where('id',$id)->delete();
    }

    //获取所有的角色
    public function getAllRole(){

        $select = [
            'id',
            'name as roleName'
        ];
        $objModle = DB::table('wind_role');

        $strSelect = implode(',', $select);

        $roleList = $objModle->select(DB::raw($strSelect))->get();


        return $roleList;
    }

    public function handlePower(){

        $roleId = $this->request->roleId;

        $menuArr = $this->request->menuArr;

        $strMenu = ','.implode(',',$menuArr).',';

        DB::table('wind_role')->where('id',$roleId)->update(['permission'=>$strMenu]);



    }
}