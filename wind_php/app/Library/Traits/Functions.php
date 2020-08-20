<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/30 0030
 * Time: 10:48
 */

namespace App\Library\Traits;


trait Functions
{
    /*
        * 处理字符串变成数组，方便入库
        */
    public function formatStrToArray($arrStr,$arrId){

        $res = array_map(function ($item) use ($arrId) {
            return [
                $arrId['filed_1'] => $arrId['id'],
                $arrId['filed_2'] => $item
            ];
        }, $arrStr);
        return $res;

    }
    /*
     * 给二维数组中添加指定的键 值
     */
    public function addDataToArray($arr,$key,$value){
        if(empty($arr)) return [];
        foreach ($arr as &$item){
            $item = array_add($item,$key,$value);
        }
        return  $arr;
    }

    /*
     *获取二维数组中指定键的值
     */
    public function getKeyValue($arr,$key){
        if(empty($arr)) return [];
        $res = [];
        foreach($arr as $k=>$v){
            if(array_key_exists($key,$v)){
                array_push($res,$v[$key]);
            }else{
                continue;
            }
        }
        return $res;
    }
    /*
     * 获取二维对象的指定键值
     */
    public function getObjectKeyValue($obj,$key){

        if($obj->isEmpty()) return [];

        $obj = collect($obj);

        $res = $obj->map(function($item,$k) use($key){
            if(collect($item)->has($key)){
                return $item->{$key};
            }
        });

        return $res->all();
    }
    /*
     * 用对象中指定值作为key 返回
     */
    public function formatObjectByKey($obj,$key){
        if(!$obj) return [];
        $obj = collect($obj);
        $arrRes = [];
        foreach($obj as $k=>$item){
            if(collect($item)->has($key)){
                $arrRes[$item->$key] = $item;
            }
        }
        return $arrRes;
    }
    /*
     * 用二维数组中指定值作为key 返回
     */
    public function formatArrayByKey($arr,$key){
        if(empty($arr)) return [];
        $res = [];
        foreach($arr as $k=>$v){
            if(array_key_exists($key,$v)){
                $res[$v[$key]] = $v;
            }else{
                continue;
            }
        }
        return $res;
    }
    /*
     * 递归处理分类
     */
    public function getTreeNode($arr,$parentId=0){
        if(empty($arr)) return [];
        $res = [];
        foreach ( $arr as $key=> $item) {
            if($item->parent_id == $parentId){
                $children = $this->getTreeNode($arr,$item->id);
                if($children){
                    $item->children = $children ;
                }

                $res[] = $item;
            }
        }
        return $res;
    }
    /*
     * 拼图片路径
     */
    public function getRightUrl($url){
        return "/".$url;
    }
}