<?php


namespace App\Repository\Resource;

use Illuminate\Support\Facades\DB;
use App\Library\Upload\FileUpload;
use App\Models\Category;
use App\Models\Rsource;
use Exception;
use App\Repository\BaseRepository;

/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/11
 * Time: 上午11:38
 */
class ResourceRepository extends BaseRepository
{
    /*
     * 创建资源
     */
    public function create()
    {
        //构建数据,开启事务
        DB::beginTransaction();
        try {
            //获取当前分类id的所有父亲id
            $catId = $this->request->category_id;
            //上传图片
            $obj = new FileUpload($this->request);
            $upImageRes = $obj->setTable('resource')->uploadResource('face_image', false, 4);
            $arrFiled = [
                'name' => $this->request->name,
                'author' => $this->request->author,
                'desc' => $this->request->desc,
                'resource_type' => $this->request->type,
                'face_image' => $upImageRes['url'],
                'face_image_md5' => $upImageRes['md5'],
                'create_user' => $this->userInfo['guid'],
                'category_id' => $catId,
            ];
            //生成资源
            $resurceRes = DB::table('resource')->insertGetId($arrFiled);
            //插入知识点
            $topicIds = $this->request->topic_ids;
            $arrTopicFiled = $this->dealTopicId($topicIds, $resurceRes);
            DB::table('resource_topic')->insert($arrTopicFiled);
            //插入标签
            $labelIds = $this->request->label_ids;
            $arrLabelFiled = $this->dealLabelId($labelIds, $resurceRes);
            DB::table('resource_label')->insert($arrLabelFiled);
            $type = $this->request->type;
            if($type==1){
                $vid = $this->request->vid;
                $upResourceRes = $obj->getPolyvVideoInfo($vid);
            }else{
                //上传物理资源
                $upResourceRes = $obj->setTable('file')->uploadResource('resource', true);
            }

            //插入文件
            $arrFile = [
                'type' => $upResourceRes['type'],
                'size' => $upResourceRes['size'],
                'url' => $upResourceRes['url'],
                'md5' => $upResourceRes['md5'],
                'path' => $upResourceRes['path'],
                'original_name' => $upResourceRes['original_name'],
                'resource_id' => $resurceRes,
                'create_user' => $this->userInfo['guid'],
            ];
            $fileRes = DB::table('file')->insertGetId($arrFile);
            //再更新资源的默认版本
            DB::table('resource')->where('id', $resurceRes)->update(['default_version' => $fileRes]);
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
     * 资源升级版本
     */
    public function upResourceVersion()
    {

        try {
            //构建数据,开启事务
            DB::beginTransaction();
            //获取当前资源
            $resourceId = $this->request->get('id');
            $resource = $this->getById($resourceId);
            //上传文件
            $obj = new FileUpload($this->request);
            $type=  $this->request->type;
            if($type==1){
                $vid = $this->request->vid;
                $upResourceRes = $obj->getPolyvVideoInfo($vid);
            }else{
                $upResourceRes = $obj->setTable('file')->uploadResource('resource', true);
            }

            //创建文件
            $fileId = DB::table('file')->insertGetId([
                'type' => $upResourceRes['type'],
                'size' => $upResourceRes['size'],
                'url' => $upResourceRes['url'],
                'md5' => $upResourceRes['md5'],
                'path' => $upResourceRes['path'],
                'original_name' => $upResourceRes['original_name'],
                'resource_id' => $resourceId,
                'create_user' => $this->userInfo['guid'],
                'create_time' => date('Y-m-d H:i:s'),
            ]);
            //更新当前资源的默认版本
            $resource->default_version = $fileId;
            $resource->save();
            $res = [
                'success' => true,
                'message' => '升级成功'
            ];

        } catch (exception $e) {
            if ($e->getMessage()) {
                $res = $this->setError($e);
            }
        }
        DB::commit();
        return $res;

    }

    /*
     * 根据分类id获取资源
     */
    public function getResourceByCatId()
    {
        $id = $this->request->category_id;
        //取出此节点下的所有子孙节点
        $arrIds = $this->getChildrenById($id);
        //连表查询
        $resResource = DB::table('resource')->join('file', 'resource.default_version', '=', 'file.id');
        $resResource->select('resource.id', 'resource.name', 'file.size', 'file.create_time');
        $resRes = $resResource->whereIn('resource.category_id', $arrIds)->paginate($this->perpage);
        //处理数据
        $res = $this->dealFileSize($resRes)->toArray();

        return $this->setPage($res);
    }

    /*
     * 根据资源名称获取资源
     */
    public function getResourceList($order='id')
    {
        $resName = $this->request->name;
        $category_id = $this->request->category_id;
        $author = $this->request->author;
        $type = $this->request->type;
        $level = $this->request->level;
        $reResource = DB::table('resource')->join('file', 'resource.default_version', '=', 'file.id');
        $reResource->join('category','resource.category_id','=','category.id');
        $select = [
            'resource.id',
            'resource.resource_type',
            'resource.category_id',
            'resource.name',
            'resource.times',
            'resource.author',
            'resource.update_time',
            'category.name as category_name',
            'file.size',
            'file.type',
        ];
        $strSelect = implode(',',$select);
        $reResource->select(DB::raw($strSelect));
        $where = [];
        if ($resName) {
            array_push($where, [
                'resource.name',
                'like',
                '%' . $resName . '%'
            ]);
        }
        if ($author) {
            array_push($where, [
                'resource.author',
                'like',
                '%' . $author . '%'
            ]);
        }
        if ($type) {
            array_push($where, [
                'resource.resource_type',
                '=',
                $type
            ]);
        }
        if($level){
            array_push($where, [
                'category.level',
                '=',
                $level
            ]);
        }
        $reResource->where($where);
        if ($category_id) {
            //取出此节点下的所有子孙节点
            $arrIds = $this->getChildrenById($category_id);
            $reResource->whereIn('resource.category_id', $arrIds);
        }
        $reResource->orderBy("resource.".$order,'desc');
        $resRes = $reResource->paginate($this->perpage);
        //处理数据
        $res = $this->dealResource($resRes)->toArray();
        return $this->setPage($res);


    }

    /*
     * 取出当前分类下的所有子孙分类
     */
    public function getChildrenById($id)
    {
        //取出此节点下的所有子孙节点
        $arr = DB::table('category')->select('id')->where('deep_code', 'like', '%' . $id . '%')->get();
        $arrIds = $arr->map(function ($item) {
            return $item->id;
        });
        $arrIds = $arrIds->all();
        array_push($arrIds, $id);
        return $arrIds;
    }

    /*
     * 获取资源统计数据
     */
    public function getCountResource()
    {
        $resName = $this->request->name;
        $author = $this->request->author;
        $type = $this->request->type;
        $category_id = $this->request->category_id;
        $where = $whereIn = [];
        if ($resName) {
            array_push($where, [
                'resource.name',
                'like',
                '%' . $resName . '%'
            ]);
        }
        if ($author) {
            array_push($where, [
                'resource.author',
                'like',
                '%' . $resName . '%'
            ]);
        }
        if ($type) {
            array_push($where, [
                'resource.resource_type',
                '=',
                $type
            ]);
        }
        if ($category_id) {
            //取出此节点下的所有子孙节点
            $arrIds = $this->getChildrenById($category_id);
            array_push($whereIn, [
                'resource.category_id',
                $arrIds
            ]);
        }
        $reResource = DB::table('resource')->join('flie', 'resource.default_version', '=', 'file.id');
        $reResource->select('resource.id', 'resource.name', 'resource.times', 'file.size', 'resource.author', 'file.type', 'file.create_time');
        $resRes = $reResource->where($where)->whereIn($whereIn)->orderBy('resource.times', 'desc')->paginate($this->perpage);
        //处理数据
        $res = $this->dealResource($resRes)->toArray();
        return $res;
    }

    /*
     * 编辑资源
     */
    public function editResource()
    {
        //构建数据,开启事务
        DB::beginTransaction();
        try {
            $catId = $this->request->category_id;
            $id = $this->request->id;
            //删除知识点
            DB::table('resource_topic')->where('resource_id', $id)->delete();
            //删除标签
            DB::table('resource_label')->where('resource_id', $id)->delete();
            $arrFiled = [
                'name' => $this->request->name,
                'author' => $this->request->author,
                'desc' => $this->request->desc,
//                'resource_type' => $this->request->type,
//                'create_user' => $this->userInfo['id'],
                'category_id' => $catId,
                'update_time' => date('Y-m-d H:i:s'),
            ];
            if ($this->request->hasFile('face_image')) {
                //上传图片
                $obj = new FileUpload($this->request);
                $upImageRes = $obj->setTable('resource')->uploadResource('face_image', false, 4);
                $arrFiled['face_image'] = $upImageRes['url'];
                $arrFiled['face_image_md5'] = $upImageRes['md5'];
            }
            //更新资源
            DB::table('resource')->where('id', $id)->update($arrFiled);
            //插入知识点
            $topicIds = $this->request->topic_ids;
            $arrTopicFiled = $this->dealTopicId($topicIds, $id);
            DB::table('resource_topic')->insert($arrTopicFiled);
            //插入标签
            $labelIds = $this->request->label_ids;
            $arrLabelFiled = $this->dealLabelId($labelIds, $id);
            DB::table('resource_label')->insert($arrLabelFiled);
            $res = [
                'success' => true,
                'message' => '更新成功'
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
     * 根据id 获取资源
     */
    public function getResourceById()
    {
        $id = $this->request->id;
        $resResource = $this->getById($id);
        $resResource->face_image = $resResource->face_image ? $this->getRightUrl($resResource->face_image):'';
        $arrResource = $resResource->toArray();
        //取分类
        $resCat = $this->getCatById($resResource->category_id);
//        $arrIds = explode(',', $resCat->deep_code . ',' . $resCat->id);
//        $resCats = $this->getCatByIds($arrIds);
        //处理
        $arrResource['category'] = ['id'=>$resCat->id,'name'=>$resCat->name];
        //取知识点
        $resTopic = $this->getTopicByResouceId($id);
        $arrResource['topic'] = $this->dealData($resTopic);
        //取标签
        $resLabel = $this->getLabelByResourceId($id);
        $arrResource['label'] = $this->dealData($resLabel);

        return $arrResource;

    }

    public function getById($id)
    {
        return Rsource::find($id);

    }

    /*
     * 移动资源
     */
    public function moveResource()
    {
        $id = $this->request->id;
        $categoryId = $this->request->category_id;
        DB::table('resource')->where('id', $id)->update(['category_id' => $categoryId]);

    }

    /*
     * 删除资源
     */
    public function deleteResource()
    {
        $id = $this->request->id;
        //检测此资源有没有被应用到课程
        $resRes = DB::table('course_resource')->where('resource_id', $id)->first();
        if ($resRes) {
            return [
                'success' => false,
                'message' => '此资源已经被应用到课程'
            ];
        }
        //删除
        DB::table('resource')->where('id', $id)->delete();
        return ['success' => true];
    }

    /*
     * 根据id 获取资源历史版本
     */
    public function getResourceVersion()
    {
        $resourceId = $this->request->id;
        //当前资源
        $arrResource = $this->getById($resourceId);
        //对应的文件
        $arrFile = DB::table('file')->select('id','original_name', 'size', 'create_time')->find($arrResource->default_version);
        $arrFile->size = $this->sizeTool($arrFile->size);
        //历史版本
        $arrVersion = DB::table('file')->select('id','original_name', 'size', 'create_time');
        $arr = $arrVersion->where('resource_id', $resourceId)->orderBy('id','desc')->paginate($this->perpage);
        $resRes = $this->dealFileSize($arr)->toArray();
        $resRes = $this->setPage($resRes);
        return [
            'current' => $arrFile,
            'history' => $resRes
        ];

    }

    /*
     * 设置默认
     */
    public function setDefault()
    {
        $id = $this->request->id;
        $fileId = $this->request->file_id;
        //查询文件
        $objFile = DB::table('file')->find($fileId);

        $type = $this->getResType($objFile->type);

        DB::table('resource')->where('id', $id)->update(['default_version' => $fileId,'resource_type'=>$type]);
    }
    /************************************************工具方法**************************************/
    /*
     * 处理文件大小
     */
    public function dealFileSize($res)
    {
        $res->each(function ($item, $key) {
            $item->size = $this->sizeTool($item->size);
        });
        return $res;
    }

    public function sizeTool($size)
    {
        return round($size / (1024 * 1000), 2);
    }

    /*
     * 处理资源
     */
    public function dealResource($res)
    {
        if (!$res)
            return $res;
        $typeConfig = config('common.upload.typeconfig');
        $res->each(function ($item, $key) use ($typeConfig) {
            $item->size = $this->sizeTool($item->size);
            $item->resource_type = $typeConfig[$item->resource_type];
//            $arrCategory = $this->getCatById($item->category_id);
//            $item->category_name = $arrCategory->name;
        });
        return $res;
    }

    public function getCatById($id)
    {
        return DB::table('category')->find($id);
    }

    public function getCatByIds($arrIds)
    {
        return DB::table('category')->whereIn('id', $arrIds)->get();
    }

    public function getTopicByResouceId($id)
    {
        $objDb = DB::table('topic')->join('resource_topic', 'topic.id', '=', 'resource_topic.topic_id');
        return $objDb->select('topic.id', 'topic.name')->where('resource_topic.resource_id', $id)->get();

    }

    public function getLabelByResourceId($id)
    {
        $objDb = DB::table('label')->join('resource_label', 'label.id', '=', 'resource_label.label_id');
        return $objDb->select('label.id', 'label.name')->where('resource_label.resource_id',$id)->get();
    }

    /*
     * 处理数据工具方法
     */
    public function dealData($resRes)
    {
        $res = $resRes->map(function ($item, $key) {
            return [
                'id' => $item->id,
                'name' => $item->name,
            ];
        });
        return $res;
    }

    /*
     * 处理知识点id
     */
    public function dealTopicId($arrTopicId, $resourceId)
    {
        $arrTopicId = explode(',', $arrTopicId);
        $res = array_map(function ($item) use ($resourceId) {
            return [
                'resource_id' => $resourceId,
                'topic_id' => $item
            ];
        }, $arrTopicId);
        return $res;
    }

    /*
     * 处理标签id
     */
    public function dealLabelId($arrTopicId, $resourceId)
    {
        $arrTopicId = explode(',', $arrTopicId);
        $res = array_map(function ($item) use ($resourceId) {
            return [
                'resource_id' => $resourceId,
                'label_id' => $item
            ];
        }, $arrTopicId);
        return $res;
    }

    /*
     * 处理分类id
     */
    public function dealCategoryId($deepCode)
    {
        return [
            'category1' => isset($deepCode[0]) ? $deepCode[0] : null,
            'category2' => isset($deepCode[1]) ? $deepCode[1] : null,
            'category3' => isset($deepCode[2]) ? $deepCode[2] : null
        ];
    }

    /*
     * 获取当前分类的所有父id
     */
    public function getParentId($catId)
    {
        $catObj = Category::find($catId, [
            'id',
            'deep_code'
        ]);
        $strCode = $catObj->deep_code ? $catObj->deep_code . ',' . $catId : $catId;
        $deepCode = explode(',', $strCode);
        return [
            'category1' => isset($deepCode[0]) ? $deepCode[0] : null,
            'category2' => isset($deepCode[1]) ? $deepCode[1] : null,
            'category3' => isset($deepCode[2]) ? $deepCode[2] : null
        ];

    }
}