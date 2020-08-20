<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-06-24
 * Time: 09:11
 */

namespace App\Repository\MobileApi;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;


class YcSchoolRepository extends BaseRepository
{


    /*
     * 根据父id 获取分类
     */
    public function getCategoryByParentId($parentId)
    {

        $objCat = DB::table('training_category')->select('id', 'name', 'deep_code', 'parent_id', 'level')->where('parent_id', $parentId)->orderBy('index','desc')->get();

        if($objCat->isEmpty()) return $objCat;

        $currentUser = $this->request->get('userInfo');

        $currentGuid = $currentUser['guid'];

        //判断各个分类下有无培训
        foreach ( $objCat as $key=>$item) {

            $objTrain = $this->getTrainingByCat($item->id,$currentGuid);

            if($objTrain->isEmpty()){

                unset($objCat[$key]);
            }

        }
        $arrCat = array_values($objCat->toArray());

        return $arrCat;

    }

    /*
     * 根据分类取当前人的培训
     */
    public function getTrainingByCat($catId, $guid)
    {

        //当前人角色
        $arrRole = DB::table('role_user')->where('guid', $guid)->pluck('role_id')->toArray();

        $arrSelect = [
            'training.id',
            'training.name',
            'training.face_image',
        ];
        $strSelect = implode(',', $arrSelect);

        $objTrain = DB::table('training')->leftJoin('role_training', 'training.id', '=', 'role_training.training_id');

        $where = [
            [
                'training.status',
                '=',
                1
            ],
            [
                'training.end_date',
                '>=',
                now()
            ],
            [
                'training.category_id',
                '=',
                $catId
            ],

        ];

        $objTrain->where($where)->whereIn('role_training.role_id', $arrRole) ;

        $objTrain->select(DB::raw($strSelect));

        $dataTraing = $objTrain->orWhere(function ($query) use ($catId){


            $query->where([
                [
                    'training.status',
                    '=',
                    1
                ],
                [
                    'training.end_date',
                    '>=',
                    now()
                ],
                [
                    'training.is_default',
                    '=',
                    1
                ],
                [
                    'training.category_id',
                    '=',
                    $catId
                ],
            ]);

        })->distinct()->get();

        //处理数据
        foreach($dataTraing as $key =>$item){

            $item->face_image = $item->face_image ? $this->getRightUrl($item->face_image) : "";

        }

        return $dataTraing;

    }

    /*
     * 根据trainingId 获取课程
     */
    public function getCourseByTrainingId($trainId)
    {

        //连表
        $reTraning = DB::table('course')->join('training_course', 'course.id', '=', 'training_course.course_id');

        $select = [
            'course.id as course_id',
            'training_course.training_id',
            'course.name',
            'course.desc',
            'course.create_time',
            'training_course.index',
            'course.face_image',
//            "concat('http://192.168.0.202:8086/',course.face_image) as face_image"
        ];

        $strSelect = implode(',', $select);

        $reTraning->select(DB::raw($strSelect))->where('training_course.training_id', $trainId)->orderBy('training_course.index', 'asc');

        $resCourse = $reTraning->get();

        if($resCourse){

            foreach ($resCourse as $key=>$item){

                $item->face_image = $item->face_image ? $this->getRightUrl($item->face_image) : "";

            }
        }

        return $resCourse;

    }

    /*
     * 根据课程获取资源
     */
    public function getResourceByCourseId($id)
    {
        //获取资源
        $objResource = DB::table('resource');
        $objResource->join('course_resource', 'resource.id', '=', 'course_resource.resource_id');
        $objResource->join('file', 'resource.default_version', '=', 'file.id');
        $select = [
            'resource.id',
            'resource.name',
            'resource.desc',
            'resource.times',
            'resource.face_image',
            'resource.resource_type',
            'file.id as file_id',
            'file.path',
            'file.type',
            'file.url',
            'file.original_name',
            'course_resource.index',
            'course_resource.title'
        ];
        $strSelect = implode(',', $select);

        $objResource->select(DB::raw($strSelect));

        $arrResource = $objResource->where('course_resource.course_id', $id)->orderBy('course_resource.index', 'desc')->get();

        $arrResource->each(function ($item) {

            $item->face_image = $item->face_image ? $this->getRightUrl($item->face_image) : "";

            $item->url = $this->getRightUrl($item->url);

            $item->is_store =  $this->checkResourceIsStore($item->id,$this->userInfo['guid']);
        });

        return $arrResource;
    }
    /*
     * 查看资源是否被当前人收藏
     */
    public function checkResourceIsStore($id,$guid){

        $where = [
            ['resource_id','=',$id],
            ['guid','=',$guid]
        ];

        $dataResource = DB::table('store')->where($where)->first();

        return $dataResource ? 1 : 0;
    }

    /*
     * 收藏列表
     */
    public function getStoreList($guid){
        //获取资源
        $objResource = DB::table('resource');
        $objResource->join('store', 'resource.id', '=', 'store.resource_id');
        $objResource->join('file', 'resource.default_version', '=', 'file.id');
        $select = [
            'resource.id',
            'resource.name',
            'resource.desc',
            'resource.times',
            'resource.face_image',
            'resource.resource_type',
            'file.id as file_id',
            'file.path',
            'file.type',
            'file.url',
            'file.original_name',
            'store.add_time'

        ];
        $strSelect = implode(',', $select);

        $objResource->select(DB::raw($strSelect));

        $arrResource = $objResource->where('store.guid', $guid)->orderBy('store.add_time', 'desc')->get();

        $arrResource->each(function ($item) {

            $item->face_image = $item->face_image ? $this->getRightUrl($item->face_image) : "";

            $item->url = $this->getRightUrl($item->url);

        });

        return $arrResource;

    }
    /*
     * 取消收藏
     */
    public function cancelResource($guid,$id){


        $where = [
            ['resource_id','=',$id],
            ['guid','=',$guid]
        ];

        DB::table('store')->where($where)->delete();


    }

    /*
     * 收藏资源
     */
    public function storeResource($guid,$id){

        DB::table('store')->insert(['guid'=>$guid,'resource_id'=>$id]);

    }

    /*
     * 获取收藏总数
     */
    public function getStoreTotal($guid){

        return   DB::table('store')->where('guid',$guid)->count();
    }
}