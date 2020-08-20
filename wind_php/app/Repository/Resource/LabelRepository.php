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

class LabelRepository extends BaseRepository
{

    /*
     * 创建知识点
     */
    public function create()
    {
        //构建数据
        $arr = [
            'name' => $this->request->get('name'),
        ];
        //插入数据
        DB::table('label')->Insert($arr);
    }

    /*
     * 知识点列表
     */
    public function getLabelList()
    {
        $name = $this->request->name;
        $where = [];
        if ($name) {
            array_push($where, [
                'name',
                'like',
                '%' . $name . '%'
            ]);
        }
        $res =DB::table('label')->where($where)->paginate($this->perpage)->toArray();
        return $this->setPage($res);
    }
    /*
     * 编辑标签
     */
    public function editLabelName(){
        $name = $this->request->name;
        $id = $this->request->id;
        DB::table('label')->where('id',$id)->update(['name'=>$name]);
    }

    /*
     * 删除标签
     */
    public function deleteLabel()
    {
        $labelId = $this->request->id;
        //检查此标签下有无资源
        $resRes = DB::table('resource_label')->where('label_id', $labelId)->first();
        if ($resRes) {
            return [
                'success' => false,
                'message' => '此标签下存在资源'
            ];
        }
        //删除
        DB::table('label')->where('id', $labelId)->delete();
    }
}