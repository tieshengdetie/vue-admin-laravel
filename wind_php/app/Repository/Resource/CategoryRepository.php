<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/15
 * Time: 下午2:00
 */

namespace App\Repository\Resource;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;

class CategoryRepository extends BaseRepository
{

    /*
     * 创建资源分类
     */
    public function create()
    {
        //构建数据
        $deepCode = $this->request->get('deep_code');
        $parent_id = $this->request->parent_id;
        $arrCode = $parent_id == 0 ? [] : ($deepCode == null ? [$parent_id] : explode(',', $deepCode . ',' . $parent_id));
        $strDeepCode = $parent_id == 0 ? '' : implode(',', $arrCode);
        $level = count($arrCode) + 1;
        $arr = [
            'name' => $this->request->get('name'),
            'parent_id' => $parent_id,
            'level' => $level,
            'deep_code' => $strDeepCode,
        ];
        //插入数据
        DB::table('category')->Insert($arr);
    }

    /*
     * 获取资源分类列表（筛选）
     */
    public function getCategoryList()
    {
        $name = $this->request->name;
        $level = $this->request->level;
        $where = [];
        if ($name) {
            array_push($where, [
                'name',
                'like',
                '%' . $name . '%'
            ]);
        }
        if ($level) {
            array_push($where, [
                'level',
                '=',
                $level
            ]);
        }
        $catRes = DB::table('category')->select('id', 'name', 'level', 'update_time')->where($where)->paginate($this->perpage)->toArray();
        return $this->setPage($catRes);
    }

    /*
     * 修改资源分类名字
     */
    public function editCategoryName()
    {
        $name = $this->request->name;
        $id = $this->request->id;
        DB::table('category')->where('id', $id)->update([
            'name' => $name,
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }

    /*
     * 删除资源分类
     */
    public function deleteCategory()
    {
        $id = $this->request->id;
        //验证是否存在此分类
        $cat = DB::table('category')->find($id);
        if(!$cat){
            return [
                'success' => false,
                'message' => '此分类不存在'
            ];
        }
        //取出此节点下的所有子孙节点
        $arrs = DB::table('category')->select('id')->where('deep_code', 'like', '%' . $id . '%')->get();

        $arrIds = $arrs->map(function ($item) {
            return $item->id;
        });
        $arrIds = $arrIds->all();
        array_push($arrIds, $id);
        //验证此资源分类下是否有资源
        $arrRes = DB::table('resource')->whereIn('category_id', $arrIds)->first();
        if ($arrRes) {
            return [
                'success' => false,
                'message' => '此分类下已经存在资源'
            ];
        }
        //删除
        DB::table('category')->whereIn('id', $arrIds)->delete();
        return ['success' => true];

    }

    /*
     * 根据层级获取分类
     */
    public function getCategoryByLevel()
    {
        $level = $this->request->level;
        return DB::table('category')->select('id', 'name', 'deep_code', 'level')->where('level', $level)->get()->toArray();
    }

    /*
     * 根据父id 获取分类
     */
    public function getCategoryByParentId()
    {
        $parentId = $this->request->parent_id;
        return DB::table('category')->select('id', 'name', 'deep_code', 'parent_id','level')->where('parent_id', $parentId)->get()->toArray();
    }

    /*
     * 获取所有一级分类
     */
    public function getFirstCategory()
    {
        return DB::table('category')->select('id', 'name', 'deep_code', 'parent_id')->where('parent_id', 0)->get()->toArray();
    }
}