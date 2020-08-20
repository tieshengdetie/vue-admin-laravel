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

class TopicRepository extends BaseRepository
{

    /*
     * 创建知识点
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
        DB::table('topic')->Insert($arr);
    }

    /*
     * 获取知识点列表（筛选）
     */
    public function getTopicList()
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
        $res = DB::table('topic')->select('id', 'name', 'level', 'update_time')->where($where)->paginate($this->perpage)->toArray();
        return $this->setPage($res);
    }

    /*
     * 修改知识点名字
     */
    public function editTopicName()
    {
        $name = $this->request->name;
        $id = $this->request->id;
        DB::table('topic')->where('id', $id)->update(['name' => $name,'update_time'=>date("Y-m-d,H:i:s")]);
    }

    /*
     * 删除知识点
     */
    public function deleteTopic()
    {
        $id = $this->request->id;
        //取出此节点下的所有子孙节点
        $arrTopics = DB::table('topic')->select('id')->where('deep_code', 'like', '%' . $id . '%')->get();
        $arrIds = $arrTopics->map(function ($item) {
            return $item->id;
        });
        $arrIds = $arrIds->all();
        array_push($arrIds, $id);
        //验证此知识点下是否有资源
        $arrRes = DB::table('resource_topic')->whereIn('topic_id', $arrIds)->first();
        if ($arrRes) {
            return [
                'success' => false,
                'message' => '此知识点下已经存在资源'
            ];
        }
        //删除
        DB::table('topic')->whereIn('id', $arrIds)->delete();
        return['success' => true];

    }

    /*
     * 根据层级获取知识点
     */
    public function getTopicByLevel()
    {
        $level = $this->request->level;
        return DB::table('topic')->select('id', 'name', 'deep_code','parent_id', 'level')->where('level', $level)->get()->toArray();
    }
    /*
     * 根据父id 获取知识点
     */
    public function getTopicByParentId()
    {
        $parentId = $this->request->parent_id;
        return DB::table('topic')->select('id', 'name', 'deep_code', 'parent_id','level')->where('parent_id', $parentId)->get()->toArray();
    }

    /*
     * 获取所有一级知识点
     */
    public function getFirstTopic()
    {
        return DB::table('topic')->select('id', 'name', 'deep_code', 'parent_id','level')->where('parent_id', 0)->get()->toArray();
    }
}