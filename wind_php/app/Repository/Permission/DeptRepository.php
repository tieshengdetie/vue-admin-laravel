<?php
/**
 * Desc: 部门管理
 * User: Zhaojinsheng
 * Date: 2020/8/12
 * Time: 11:04
 * Filename:DeptRepository.php
 */
namespace App\Repository\Permission;

use DemeterChain\B;
use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;

class DeptRepository extends  BaseRepository{


    public  function createOrEditDept(){

        $id = $this->request->id;

        $name = $this->request->name;


        if(intval($id)>0){

            $upArr = ['name'=>$name,'update_time'=>date("Y-m-d H:i:s")];

            DB::table('wind_dept')->where('id',$id)->update($upArr);

        }else{
            //构建数据
            $parent_id = $this->request->parent_id;

            if($parent_id === 0){

                $strDeepCode = ',';
                $level = 1;

            }else{

                $parentInfo = DB::table('wind_dept')->find($parent_id);

                $strDeepCode = $parentInfo->deep_code.$parent_id.',';

                $level = $parentInfo->level + 1;

            }

            $arr = [
                'name' => $name,
                'parent_id' => $parent_id,
                'level' => $level,
                'deep_code' => $strDeepCode,
                'create_time'=>date("Y-m-d H:i:s"),
            ];

            //插入数据
            DB::table('wind_dept')->insertGetId($arr);
        }




    }

    public function getDeptData(){

        $objCat = DB::table('wind_dept')->select('id', 'name', 'deep_code', 'parent_id','level')->where('is_deleted', 0)->get();

        $res =[];

        if($objCat){

            $res = $this->getTreeNode($objCat);

        }
        return $res;
    }


    public function deleteDept(){

        $id = $this->request->id;

        //取出此节点下的所有子孙节点
        $arrs = DB::table('wind_dept')->select('id')->where('deep_code', 'like', '%,' . $id . ',%')->get();

        $arrIds = $arrs->map(function ($item) {
            return $item->id;
        });

        $arrIds = $arrIds->all();

        array_push($arrIds, $id);

        //删除
        DB::table('wind_dept')->whereIn('id', $arrIds)->delete();


    }
}