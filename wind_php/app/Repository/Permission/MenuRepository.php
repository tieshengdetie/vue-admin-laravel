<?php
/**
 * Desc: 菜单管理服务层
 * User: Zhaojinsheng
 * Date: 2020/8/10
 * Time: 08:37
 * Filename:MenuRepository.php
 */

namespace App\Repository\Permission;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;

class MenuRepository extends BaseRepository
{


    public function createOrEditMenu()
    {

        $param = $this->request->all();

        $id = $this->request->id;

        $parentId = $param['parent_id'];

        unset($param['id']);

        $objModle = DB::table('wind_menu');


        if (intval($id) > 0) {

            $objModle->where('id', $id)->update($param);

        } else {

            if (intval($parentId) === 0) {

                $param['menu_level'] = 1;

                $strDeepCode = ',';


            } else {

                //查询父亲
                $parentInfo = $objModle->find($parentId);

                $strDeepCode = $parentInfo->deep_code.$parentId.',';

                $param['menu_level'] = $parentInfo->menu_level + 1;
            }

            $param['deep_code'] = $strDeepCode;

            $objModle->insert($param);
        }

    }

    public function getFisrtMenu()
    {

//        $parent_id = $this->request->id ? $this->request->id : 0;

        $objModel = DB::table('wind_menu');

        $menuInfo = $objModel->get()->toArray();

        //处理数据
        $typeArr = [1 => '菜单', 2 => '按钮',3=>'路由'];


        foreach ($menuInfo as $item) {

            $item->typeName = $typeArr[$item->type];
        }


        $resMenu = $this->getTreeNode($menuInfo);


        return $resMenu;

    }

    public function getMenuByRoleId()
    {

        $roleId = $this->request->roleId;

        $roleInfo = DB::table('wind_role')->find($roleId);

        $permission = trim($roleInfo->permission, ',');

        $arrPermission = explode(',', $permission);


        $arrMenuInfo = $this->getFisrtMenu();


        foreach ($arrMenuInfo as $key => $item) {

            if (isset($item->children)) {


                $arrIds = $this->getChildCheckedId($item->children, $arrPermission);


            }

            $item->checkedMenu= $arrIds;


        }

        return $arrMenuInfo;


    }


    private function getChildCheckedId($children, $arrPer,$arrIds = [])
    {

        foreach ($children as $key => $item) {

            if (in_array( $item->id,$arrPer)) {

                array_push($arrIds, $item->id);
            }
            if(isset($item->children)){

                $arrIds = $this->getChildCheckedId($item->children,$arrPer,$arrIds);
            }
        }

        return $arrIds;
    }

    public function deleteMenu(){

        $id = $this->request->id;

        //查看其所有子菜单

        $where = [

            ['deep_code','like','%,'.$id.',%']
        ];

        $children = DB::table('wind_menu')->where($where)->get();

        $arrId = $this->getObjectKeyValue($children,'id');

        array_push($arrId,$id);

        DB::table('wind_menu')->whereIn('id',$arrId)->delete();
    }
}