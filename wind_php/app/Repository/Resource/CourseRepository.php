<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/15
 * Time: 下午2:00
 */

namespace App\Repository\Resource;

use App\Repository\BaseRepository;
use Illuminate\Support\Facades\DB;
use App\Library\Upload\FileUpload;
use Exception;
class CourseRepository extends BaseRepository
{

    /*
     * 创建知课程
     */
    public function create()
    {
        //构建数据
        try {
            //构建数据,开启事务
            DB::beginTransaction();
            $userInfo = $this->userInfo;
            $arrResource = json_decode($this->request->resource,true);
            $hour = count($arrResource);

            //上传图片
            $obj = new FileUpload($this->request);
            $upRes = $obj->setTable('course')->uploadResource('face_image', false, 4);
            $arr = [
                'name' => $this->request->name,
                'desc' => $this->request->desc,
                'contents' => $this->request->contents,
                'teacher' => $this->request->teacher,
                'category_id' => $this->request->category_id,
                'hour' => $hour,
                'face_image' => $upRes['url'],
                'face_image_md5' => $upRes['md5'],
                'create_user' => $userInfo['guid'],

            ];

            //插入数据
            $courseId = DB::table('course')->insertGetId($arr);
            //构建资源数据
            $arrFiled = $this->dealResourceData($arrResource, $courseId);
            DB::table('course_resource')->Insert($arrFiled);
            $res = [
                'success' => true,
                'message' => '创建成功'
            ];

        } catch (Exception $e) {
            if ($e->getMessage()) {
                DB::rollBack();
                $res = $this->setError($e);
            }
        }
        DB::commit();
        return $res;

    }

    /*
     * 修改课程
     */
    public function editCourse()
    {
        //构建数据
        try {
            //构建数据,开启事务
            DB::beginTransaction();
            $arrResource = json_decode($this->request->resource,true);
            $hour = count($arrResource);
            $courseId = $this->request->id;
            //删除以前的资源
            DB::table('course_resource')->where('course_id', $courseId)->delete();
            $arr = [
                'name' => $this->request->name,
                'desc' => $this->request->desc,
                'contents' => $this->request->contents,
                'teacher' => $this->request->teacher,
                'category_id' => $this->request->category_id,
                'hour' => $hour,
                'update_time' => date("Y-m-d,H:i:s"),

            ];
            if ($this->request->hasFile('face_image')) {
                //上传图片
                $obj = new FileUpload($this->request);
                $upRes = $obj->setTable('course')->uploadResource('face_image', false, 4);
                $arr['face_image'] = $upRes['url'];
                $arr['face_image_md5'] = $upRes['md5'];
            }
            //更新数据
            DB::table('course')->where('id', $courseId)->update($arr);
            //构建资源数据
            $arrFiled = $this->dealResourceData($arrResource, $courseId);
            DB::table('course_resource')->Insert($arrFiled);
            $res = [
                'success' => true,
                'message' => '更新成功'
            ];

        } catch (\Exception $e) {
            if ($e->getMessage()) {
                DB::rollBack();
                $res = $this->setError($e);
            }
        }
        DB::commit();
        return $res;
    }

    /*
     * 根据id 获取课程
     */
    public function getCourseById()
    {
        $id = $this->request->id;
        //先取出课程
        $arrCourse = DB::table('course')->find($id);
        //取分类
        $arrCategory = DB::table('category_course')->select('id', 'name')->find($arrCourse->category_id);
        $arrCourse->category_name = $arrCategory->name;
        $arrCourse->face_image = $this->getRightUrl($arrCourse->face_image);
        //获取资源
        $objResource = DB::table('resource');
        $objResource->join('course_resource', 'resource.id', '=', 'course_resource.resource_id');
        $select = [
            'resource.id',
            'resource.name',
            'course_resource.index',
            'course_resource.title'
        ];
        $strSelect = implode(',', $select);
        $objResource->select(DB::raw($strSelect));
        $arrResource = $objResource->where('course_resource.course_id', $id)->orderBy('course_resource.index', 'desc')->get()->toArray();
        return [
            'course' => $arrCourse,
            'resource' => $arrResource
        ];
    }

    /*
     * 获取课程列表
     */
    public function getCourseList($order='id')
    {
        //连表查询
        $objDb = DB::table('course');
        $objDb->join('category_course', 'course.category_id', '=', 'category_course.id');
        $select = [
            'course.id',
            'course.name',
            'course.hour',
            'course.times',
            'course.update_time',
            'course.category_id',
            'category_course.name as category_name'
        ];
        $strSelect = implode(',', $select);
        $objDb->select(DB::raw($strSelect));
        //构建where
        $where = [];
        if ($name = $this->request->get('name')) {
            array_push($where, [
                'course.name',
                'like',
                '%' . $name . '%'
            ]);
        }
        if($level = $this->request->level){
            array_push($where, [
                'category_course.level',
                '=',
                $level
            ]);
        }
        $objDb->where($where);
        if ($category_id = $this->request->get('category_id')) {
            $arrIds = $this->getChildrenById($category_id);
            $objDb->whereIn('course.category_id', $arrIds);
        }
        $objDb->orderBy('course.'.$order,'desc');
        $resCourse = $objDb->paginate($this->perpage)->toArray();

        return $this->setPage($resCourse);
    }

    /*
     * 处理课程数据
     */
    public function dealCourse($res)
    {

        $res->each(function ($item, $key) {
            $arrCategory = $this->getCatById($item->category_id);
            $item->category_name = $arrCategory->name;
        });
        return $res;


    }

    public function getCatById($id)
    {
        return DB::table('category_course')->find($id);
    }

    /*
     * 获取课程统计数据
     */
    public function getCountCourse()
    {
        return $this->getCourseList($order='times');
    }

    /*
     * 取出当前分类下的所有子孙分类
     */
    public function getChildrenById($id)
    {
        //取出此节点下的所有子孙节点
        $arr = DB::table('category_course')->select('id')->where('deep_code', 'like', '%' . $id . '%')->get();
        $arrIds = $arr->map(function ($item) {
            return $item->id;
        });
        $arrIds = $arrIds->all();
        array_push($arrIds, $id);
        return $arrIds;
    }

    /*
     * 删除课程
     */
    public function deleteCourse()
    {
        $id = $this->request->id;
        try {
            //开事务
            DB::beginTransaction();
            //删除资源
            DB::table('course_resource')->where('course_id', $id)->delete();
            //删课程
            DB::table('course')->where('id', $id)->delete();
            $res = [
                'success' => true,
                'message' => '删除成功'
            ];
        } catch (\Exception $e) {
            if ($e->getMessage()) {
                DB::rollBack();
                $res = $this->setError($e);
            }
        }
        DB::commit();
        return $res;
    }

    /******************************************************工具方法****************************/
    /*
     * 构建资源数据
     */
    public function dealResourceData($arrResource, $courseId)
    {
        $res = array_map(function ($item) use ($courseId) {
            return [
                'course_id' => $courseId,
                'resource_id' => $item['id'],
                'index' => $item['index'],
                'title' => $item['title']
            ];
        }, $arrResource);
        return $res;
    }

    /*
     * 构建分类数据
     */
    public function dealCategoryData($arrCat, $courseId)
    {
        $res = array_map(function ($item) use ($courseId) {
            return [
                'course_id' => $courseId,
                'category_id' => $item
            ];
        }, $arrCat);
        return $res;
    }

    /*
     * 获得所有资源id
     */
    public function getResourceId($arr)
    {
        $res = array_map(function ($item) {
            return $item['id'];
        }, $arr);
        return $res;
    }
}