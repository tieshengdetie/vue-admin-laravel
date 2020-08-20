<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/11/21
 * Time: 4:23 PM
 */

namespace App\Repository\Training;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;
use App\Library\Upload\FileUpload;
use App\Library\Proxy\TokenProxy;

class TrainingRepository extends BaseRepository
{

    /*
     * 创建资源分类
     */
    public function create()
    {
        //构建数据,开启事务
        DB::beginTransaction();
        try {
            //上传图片
            $obj = new FileUpload($this->request);
            //插入部门
            $strDept = $this->request->dept;
            $arrDept = json_decode($strDept, true);
            //处理
            $resDept = $this->arrTostr($arrDept);
            $upImageRes = $obj->setTable('training')->uploadResource('face_image', false, 4);
            //构建数据
            $arrFiled = [
                'name' => $this->request->name,
                'category_id' => $this->request->category_id,
                'start_date' => $this->request->start_date,
                'face_image' => $upImageRes['url'],
                'face_image_md5' => $upImageRes['md5'],
                'create_user' => $this->userInfo['guid'],
                'end_date' => $this->request->end_date,
                'degree' => $this->request->degree,
                'desc' => $this->request->desc,
                'dept_ids' => $resDept['strIds'],
                'dept_names' => $resDept['strNames'],
            ];
            //入库
            $trainingId = DB::table('training')->insertGetId($arrFiled);
            $res = [
                'success' => true,
                'message' => '',
                'data' => ['training_id' => $trainingId]
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
     * 编辑培训
     */
    public function editTraining()
    {
        $id = $this->request->id;
        $objTraing = $this->getTrainingById($id);
        //检测是否已经被分配到了角色下
        $isAssign = $this->checkTrainIsAssign($id);
        if ($isAssign && $objTraing->status==1) {
            $res = [
                'success' => false,
                'message' => '当前培训已经发布且分配到角色下，不可编辑！',
            ];
            return $res;
        }
        //构建数据,开启事务
        DB::beginTransaction();
        try {

            //插入部门
            $strDept = $this->request->dept;
            $arrDept = json_decode($strDept, true);
            //            //处理
            $resDept = $this->arrTostr($arrDept);
            //构建数据
            $arrFiled = [
                'name' => $this->request->name,
                'category_id' => $this->request->category_id,
                'start_date' => $this->request->start_date,
                'end_date' => $this->request->end_date,
                'degree' => $this->request->degree,
                'desc' => $this->request->desc,
                'dept_ids' => $resDept['strIds'],
                'dept_names' => $resDept['strNames'],
            ];
            if($objTraing->status==3 && strtotime($this->request->end_date) >= time()){
                $arrFiled['status'] = 0;
            }
            if ($this->request->hasFile('face_image')) {
                //上传图片
                $obj = new FileUpload($this->request);
                $upRes = $obj->setTable('training')->uploadResource('face_image', false, 4);
                $arrFiled['face_image'] = $upRes['url'];
                $arrFiled['face_image_md5'] = $upRes['md5'];
            }
            //入库
            DB::table('training')->where('id', $id)->update($arrFiled);
            $res = [
                'success' => true,
                'message' => '',
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
     * 根据trainingId 获取课程
     */
    public function getCourseByTrainingId()
    {
        $id = $this->request->id;
        //连表
        $reTraning = DB::table('course')->join('training_course', 'course.id', '=', 'training_course.course_id');
        $select = [
            'course.id as course_id',
            'training_course.id',
            'training_course.training_id',
            'course.name',
            'course.desc',
            'course.create_time',
            'training_course.index'
        ];
        $strSelect = implode(',', $select);
        $reTraning->select(DB::raw($strSelect))->where('training_course.training_id', $id)->orderBy('training_course.index', 'asc');
        $res = $reTraning->get()->toArray();
        return $res;

    }

    /*
     * 根据课程获取资源
     */
    public function getCourseResource()
    {
        $id = $this->request->id;
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
        });
        return $arrResource;
    }

    /*
     * 删除培训下的课程
     */
    public function deleteTrainingCourse()
    {
        $id = $this->request->id;
        DB::table('training_course')->where('id', $id)->delete();
    }

    /*
     * 删除培训下的人
     */
    public function deleteTrainingUser()
    {
        $id = $this->request->id;
        $guid = $this->request->guid;
        //判断前培训下有无此人
        $where = [
            [
                'training_id',
                '=',
                $id
            ],
            [
                'user',
                '=',
                $guid
            ]
        ];
        $objStudent = DB::table('training_student')->where($where)->get();
        if (!$objStudent->isEmpty()) {
            DB::table('training_student')->where($where)->delete();
            $res = [
                'success' => true,
                'message' => '删除成功'
            ];
            return $res;
        } else {
            $res = [
                'success' => false,
                'message' => '当前培训下无此人'
            ];
            return $res;
        }


    }

    /*
     * 删除培训
     */
    public function deleteTraining()
    {
        $id = $this->request->id;
        //当前培训
        $arrTraining = $this->getTrainingById($id);
        if (!$arrTraining) {
            $res = [
                'success' => false,
                'message' => '该培训不存在',
                'code' => 400
            ];
            return $res;
        }
        //检测是否已经被分配到了角色下
        $isAssign = $this->checkTrainIsAssign($id);
        if ($isAssign && $arrTraining->status==1) {
            $res = [
                'success' => false,
                'message' => '该培训已发布且分配到角色下，不可删除！',
                'code' => 400
            ];
            return $res;
        }
        try {
            //开事务
            DB::beginTransaction();
            //删除培训
            DB::table('training')->where('id', $id)->delete();
            //删课程
            DB::table('training_course')->where('training_id', $id)->delete();
            //删角色培训关联表
            DB::table('role_training')->where('training_id', $id)->delete();
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
    public function checkTrainIsAssign($id){

        return DB::table("role_training")->where('training_id',$id)->first();
    }
    /*
     * 发布培训
     */
    public function publishTraining()
    {
        $id = $this->request->id;
        //取出当前培训
        $currentTraining = $this->getTrainingById($id);
        if (($currentTraining->status == 3 || $currentTraining->end_date <= now())) {
            $response = [
                'success' => false,
                'message' => "该培训已经结束"
            ];
            return $response;
        } else if ($currentTraining->status == 1) {
            $response = [
                'success' => false,
                'message' => "该培训已经发布"
            ];
            return $response;
        }

        DB::table('training')->where('id', $id)->update(['status' => 1]);
        //取出所有学员
        $objStudent = DB::table('training_student')->where('training_id', $id)->get();
        $arrStudent = $this->getObjectKeyValue($objStudent, 'user');
        //获取学员信息
        $arrStudentList = $this->getAllStudent($arrStudent);
        $arrStudentList = $arrStudentList['data']['result']['items'];
        //        $this->pushMessage($currentTraining, $arrStudent, $arrStudentList);
        $response = [
            'success' => true,
            'message' => ""
        ];
        return $response;
    }

    /*
     * 下架培训
     */
    public function offTraining()
    {
        $id = $this->request->id;
        //取出当前培训
        $currentTraining = $this->getTrainingById($id);

        if (($currentTraining->status == 3 || $currentTraining->end_date <= now())) {
            $response = [
                'success' => false,
                'message' => "该培训已经结束"
            ];
            return $response;
        } else if ($currentTraining->status == 2) {
            $response = [
                'success' => false,
                'message' => "该培训已经下架"
            ];
            return $response;
        }
        DB::table('training')->where('id', $id)->update(['status' => 2]);
        $response = [
            'success' => true,
            'message' => ""
        ];
        return $response;
    }

    /*
     * 给培训添加学生
     */
    public function addStudent()
    {
        $id = $this->request->id;
        //查看此培训是否存在
        $objTraining = $this->getTrainingById($id);
        if (!$objTraining) {
            $response = [
                'success' => false,
                'message' => "此培训不存在"
            ];
            return $response;
        }
        $students = $this->request->students;
        $arrStudents = json_decode($students, true);
        //获取所有学生id
        $arrStudentId = $this->getKeyValue($arrStudents, 'guid');
        $arrFormat = $this->formatArrayByKey($arrStudents, 'guid');
        //检查是否重复添加
        $check = DB::table('training_student')->whereIn('user', $arrStudentId)->where('training_id', $id)->first();
        if ($check) {
            $response = [
                'success' => false,
                'message' => "{$arrFormat[$check->user]['name']}已经添加到了此培训下"
            ];
            return $response;
        }

        $arrFileds = $this->formatStrToArray($arrStudentId, [
            'id' => $id,
            'filed_1' => 'training_id',
            'filed_2' => 'user'
        ]);
        //入库
        DB::table('training_student')->insert($arrFileds);
        //取出当前培训
        $currentTraining = $this->getTrainingById($id);
        if ($currentTraining->status == 1) {
            //            $this->pushMessage($currentTraining, $arrStudentId, $arrStudents);
        }
        return [
            'success' => true,
            'message' => ''
        ];

    }

    /*
     * 添加所有的学生
     */
    public function addAllStudent()
    {
        $id = $this->request->id;
        $result = $this->getAllStudent();
        if ($result['success'] == true && isset($result['data']['result'])) {
            $studentList = $result['data']['result']['items'];
            //获取所有学生的id
            $arrStudentId = $this->getKeyValue($studentList, 'guid');
            //取出当前培训下已有的学生
            $objTrainingStudent = DB::table('training_student')->select('user')->where('training_id', $id)->get();
            $arrTrainingStudent = $this->getObjectKeyValue($objTrainingStudent, 'user');
            //获取两个数组的差集
            $arrDiffStudent = array_diff($arrStudentId, $arrTrainingStudent);

            //取出当前培训
            $currentTraining = $this->getTrainingById($id);
            $arrFileds = $this->formatStrToArray($arrDiffStudent, [
                'id' => $id,
                'filed_1' => 'training_id',
                'filed_2' => 'user'
            ]);
            //入库
            DB::table('training_student')->insert($arrFileds);


            if ($currentTraining->status == 1) {
                //                $this->pushMessage($currentTraining, $arrDiffStudent, $studentList);
            }
            return [
                'success' => true,
                'message' => ''
            ];
        }

    }

    /*
     * 统计资源的播放次数
     */
    public function countResourceTimes()
    {
        $id = $this->request->id;
        //验证是否存在此资源
        $objResource = DB::table('resource')->find($id);
        if (!$objResource) {
            $response = [
                'success' => false,
                'message' => '该资源不存在'
            ];
            return $response;
        }
        DB::table('resource')->where('id', $id)->increment('times');
        $response = [
            'success' => true,
            'message' => '',
            'data' => ['times' => $objResource->times + 1]
        ];
        return $response;
    }

    public function getTrainingById($id)
    {
        return DB::table('training')->find($id);
    }

    /*
     * 推送消息
     * $obj 当前培训对象
     * $arr 分配人的guid 数组
     */
    public function pushMessage($obj, $arr, $allStudent = null)
    {
        //请求接口
        if (empty($arr)) {
            return false;
        }
        $objPorxy = new TokenProxy();
        $token = $this->getToken();
        $url = config('common.apiUrl.pushMessage');
        if (!$allStudent) {
            $apiResult = $this->getAllStudent();
            $allStudent = $apiResult['data']['result']['items'];
        }
        $studentGuid = $this->formatArrayByKey($allStudent, 'guid');
        $params = [];
        foreach ($arr as $key => $item) {
            //判断当前人事国际学校的还是集团的
            if (isset($studentGuid[$item])) {
                $currentUser = $studentGuid[$item];
            } else {
                continue;
            }

            $owner = $currentUser['isBCIS'] ? config('common.pushMessage.bcisOwner') : config('common.pushMessage.owner');
            $content = $currentUser['isBCIS'] ? config('common.pushMessage.bcisContent') : config('common.pushMessage.content');
            $data = [
                'userCode' => $item,
                'productType' => config('common.pushMessage.productType'),
                'owner' => $owner,
                'messageType' => config('common.pushMessage.messageType'),
                'title' => $obj->name,
                'content' => $content . "《{$obj->name}》",
                'redirectUrl' => config('common.pushMessage.redirectUrl'),
                //测试地址
                'sendTime' => date("Y-m-d H:i:s"),
            ];
            array_push($params, $data);
        }
        $result = $objPorxy->sendHttp($url, 'POST', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
            'json' => $params
        ]);
    }

    /*
     * 获取所有人
     */
    public function getAllStudent($arrGuid = [])
    {
        //获取所有学生
        $token = $this->getToken();
        //请求接口
        $objPorxy = new TokenProxy();
        $url = config('common.apiUrl.getUserInfo');
        $result = $objPorxy->sendHttp($url, 'POST', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
            'json' => [
                'staffIds' => $arrGuid,
                'staffName' => '',
                'departmentId' => '',
                'postId' => '',
                'hireStartTime' => '',
                'hireEndTime' => '',
                'skipCount' => 0,
                'maxResultCount' => 200000000,
            ]
        ]);
        return $result;
    }

    /*
     * 获取培训下的学生列表
     */
    public function getStudentList()
    {
        $id = $this->request->id;
        $page = $this->request->page ? $this->request->page : 1;
        $offset = ($page - 1) * $this->perpage;
        $roleId = $this->request->role_id;
        $arrGuid = [];
        $arrList = [];
        if ($id) {
            //获取所有学生id
            $studentGuid = DB::table('training_student')->select('user')->where('training_id', $id)->get();
            if ($studentGuid->isEmpty()) {
                return [
                    'current_page' => $page,
                    'total' => 0,
                    'pagesize' => $this->perpage,
                    'data' => []
                ];
            }
            //处理学生id 二维转一维
            $arrGuid = $this->getObjectKeyValue($studentGuid, 'user');
        }
        if ($roleId) {
            //获取角色下的人员guid
            $userGuid = DB::table('role_user')->select('guid')->where('role_id', $roleId)->get();
            if ($userGuid->isEmpty()) {
                return [
                    'current_page' => $page,
                    'total' => 0,
                    'pagesize' => $this->perpage,
                    'data' => []
                ];
            }
            //处理学生id 二维转一维
            $arrGuid = $this->getObjectKeyValue($userGuid, 'guid');
        }
        $token = $this->getToken();
        //请求接口
        $objPorxy = new TokenProxy();
        $url = config('common.apiUrl.getUserInfo');
        $result = $objPorxy->sendHttp($url, 'POST', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
            'json' => [
                'staffIds' => $arrGuid,
                'staffName' => $this->request->name,
                'departmentId' => $this->request->dept_id,
                'postId' => $this->request->post_id,
                'hireStartTime' => $this->request->start_date,
                'hireEndTime' => $this->request->end_date,
                'skipCount' => $offset,
                'maxResultCount' => $this->perpage,
            ]
        ]);

        if ($result['success'] == true && isset($result['data']['result'])) {
            $arr = $result['data']['result'];
            $arrList['current_page'] = $page;
            $arrList['total'] = $arr['totalCount'];
            $arrList['pagesize'] = $this->perpage;
            $arrList['data'] = $this->dealUserData($arr['items']);
        } else {
            return [
                'current_page' => $page,
                'total' => 0,
                'pagesize' => $this->perpage,
                'data' => []
            ];
        }
        return $arrList;

    }

    /*
     * 获取部门岗位
     */
    public function getDeptPost()
    {
        $parentId = $this->request->parentId;
        //请求接口
        $objPorxy = new TokenProxy();
        $token = $this->getToken();
        $url = config('common.apiUrl.getDept');
        $result = $objPorxy->sendHttp($url, 'POST', [
            'headers' => [
                'Authorization' => $token,
            ],
            'form_params' => [
                'parentId' => $parentId
            ]
        ]);
        $arrDept = [];
        if (isset($result['data']['result'])) {
            $arrDept = $result['data']['result'];
        }
        return $arrDept;
    }

    /*
     * 添加课程
     */
    public function addCourse()
    {
        $id = $this->request->id;
        //查看此培训是否存在
        $objTraining = $this->getTrainingById($id);
        if (!$objTraining) {
            $response = [
                'success' => false,
                'message' => "此培训不存在"
            ];
            return $response;
        }
        $course = $this->request->course;
        $arrCourse = json_decode($course, true);
        //获取所有课程id
        $arrCourseId = $this->getKeyValue($arrCourse, 'id');
        //检查是否重复添加
        $check = DB::table('training_course')->select('course_id')->where('training_id', $id)->whereIn('course_id', $arrCourseId)->first();
        if ($check) {
            $checkCourse = DB::table('course')->find($check->course_id);
            $response = [
                'success' => false,
                'message' => "课程:'{$checkCourse->name}' 已经添加到此培训下"
            ];
            return $response;
        }

        $arrCourse = $this->formatStrToArray($arrCourseId, [
            'id' => $id,
            'filed_1' => 'training_id',
            'filed_2' => 'course_id'
        ]);
        //入库
        DB::table('training_course')->insert($arrCourse);
        $response = [
            'success' => true,
            'message' => ""
        ];
        return $response;
    }

    /*
     * 获取当前人下的培训列表
     */
    public function getUserTraining()
    {
        $userInfo = $this->userInfo;
        $this->request->attributes->add(['guid' => $userInfo['guid']]);
        $this->request->attributes->add(['is_fabu' => 1]);
        return $this->getTrainingList();
    }

    /*
     * 获取培训列表
     */
    public function getTrainingList()
    {
        $name = $this->request->name;
        $dept_name = $this->request->dept_name;
        $status = $this->request->status;
        $cat_id = $this->request->cat_id;
        $is_assign = $this->request->is_assign;
        if ($this->request->get('is_fabu') == 1 || $is_assign === 'assign') {
            $status = 1;
        }

        $guid = $this->request->get('guid') ? $this->request->get('guid') : $this->request->guid;
        $start_date = $this->request->start_date;
        $end_date = $this->request->end_date;
        $reTraining = DB::table('training');
        $select = [
            'id',
            'category_id',
            'name',
            'dept_names',
            '`desc`',
            'face_image',
            'is_default',
            'status',
            'start_date',
            'end_date'
        ];
        $strSelect = implode(',', $select);
        $reTraining->select(DB::raw($strSelect));
        $where = [];

        if ($name) {
            array_push($where, [
                'name',
                'like',
                '%' . $name . '%'
            ]);
        }
        if ($cat_id) {
            array_push($where, [
                'category_id',
                '=',
                $cat_id
            ]);
        }
        if ($dept_name) {
            array_push($where, [
                'dept_names',
                'like',
                '%' . $dept_name . '%'
            ]);
        }
        if (isset($status)) {

            array_push($where, [
                'status',
                '=',
                $status
            ]);


        }
        if ($start_date) {
            array_push($where, [
                'start_date',
                '>=',
                $start_date
            ]);
        }
        if ($end_date) {
            array_push($where, [
                'end_date',
                '<=',
                $end_date
            ]);
        }
        if ($is_assign === 'assign') {
            array_push($where, [
                'end_date',
                '>=',
                now()
            ]);
        }
        if ($guid) {
            //取出所有培训
            $objTraining = $this->getTrainingByGuid($guid);
            $arrTrainingId = $this->getObjectKeyValue($objTraining, 'training_id');
            $reTraining->whereIn('id', $arrTrainingId);
        }
        $objTraining = $reTraining->where($where)->orderBy('id', 'desc')->paginate($this->perpage);
        //处理数据
        $arrTraining = $this->dealTrainingData($objTraining)->toArray();
        return $this->setPage($arrTraining);
    }

    /*
     * 获取当前人培训的所有数据(pc端用的）
     *
     */
    public function getUserTrainingAll()
    {
        $guid = $this->request->guid;
        $res = $this->getAllTrainingCourse2($guid);
        //获取每个培训下的课程
        foreach ($res as $keyT => &$itemT) {

            //获取资源
            foreach ($itemT['children'] as $keyC => &$itemC) {
                $objResourceId = DB::table('course_resource')->where('course_id', $itemC['id'])->get();
                $arrReosurceId = $this->getObjectKeyValue($objResourceId, 'resource_id');
                //查询
                $objResource = DB::table('resource')->select('id', 'name')->whereIn('id', $arrReosurceId)->get()->toArray();
                $itemC['children'] = $objResource;
            }

        }

        return $res;

    }

    /*
     * 获取当前人下所有的课程（移动端用）
     */
    public function getAllTrainingCourse()
    {
        $currentUser = $this->userInfo;
        //取出当前人下所有的培训
        $objTrainingId = $this->getTrainingByGuid($currentUser['guid']);
        $arrTrainingId = $this->getObjectKeyValue($objTrainingId, 'training_id');
        $objTraining = DB::table('training')->select('id', 'name')->whereIn('id', $arrTrainingId)->where([
            [
                'status',
                '=',
                1
            ],
            [
                'end_date',
                '>=',
                now()
            ]
        ])->get();
        foreach ($objTraining as $keyT => $itemT) {
            //获取课程
            $objCourseId = DB::table('training_course')->where('training_id', $itemT->id)->get();
            $arrCourseId = $this->getObjectKeyValue($objCourseId, 'course_id');
            //查询课程
            $objCourse = DB::table('course')->select('id', 'name')->whereIn('id', $arrCourseId)->get();
            $itemT->children = $objCourse;
        }

        return $objTraining;
    }

    public function getAllTrainingCourse1()
    {
        $currentUser = $this->userInfo;
        //取出当前人下的所有培训
        $objTraining = DB::table('training')->join('training_student', 'training.id', '=', 'training_student.training_id')->where([
            [
                'training_student.user',
                '=',
                $currentUser['guid']
            ],
            [
                'training.status',
                '=',
                1
            ],
            [
                'training.end_date',
                '>=',
                now()
            ]
        ])->select('training.id', 'training.name', 'training.face_image')->orderBy('order_index', 'desc')->get();

        foreach ($objTraining as $keyT => $itemT) {
            //获取课程
            $objCourse = DB::table('course')->join('training_course', 'course.id', '=', 'training_course.course_id')->where('training_course.training_id', $itemT->id)->select('course.id', 'course.name', 'course.face_image')->orderBy('training_course.index', 'asc')->get();
            $objCourse->each(function ($valueCourse) {
                $valueCourse->face_image = $this->getRightUrl($valueCourse->face_image);
            });
            $itemT->children = $objCourse;
            $itemT->face_image = $this->getRightUrl($itemT->face_image);
        }
        return $objTraining;
    }

    //根据角色取培训
    public function getAllTrainingCourse2($guid = null)
    {
        $currentGuid = $guid ? $guid : $this->userInfo['guid'];
        //取出当前人所属的角色
        $arrRole = DB::table('role_user')->where('guid', $currentGuid)->pluck('role_id')->toArray();
        if (!empty($arrRole)) {
            //取出所有角色下的培训，并去重
            $arrTrainId = DB::table('role_training')->whereIn('role_id', $arrRole)->distinct()->pluck('training_id')->toArray();
        } else {
            $arrTrainId = [];
        }
        //查出培训
        $objTrain = DB::table('training')->join('training_course', 'training.id', '=', 'training_course.training_id')->join('course', 'training_course.course_id', '=', 'course.id');
        $arrSelect = [
            'training.id',
            'training.name',
            'training.face_image',
            'course.id as course_id',
            'course.name as course_name',
            'course.face_image as course_face_image'
        ];
        $strSelect = implode(',', $arrSelect);
        $resTrain = $objTrain->select(DB::raw($strSelect))->whereIn('training.id', $arrTrainId)->orWhere(function ($query) {
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
            ]);

        })->get();
        if (!$resTrain) {
            return [];
        }
        $res = [];
        foreach ($resTrain as $key => $item) {
            if (!array_key_exists($item->id, $res)) {
                $res[$item->id]['id'] = $item->id;
                $res[$item->id]['name'] = $item->name;
                $res[$item->id]['face_image'] = $this->getRightUrl($item->face_image);
                $childrens = [];
                foreach ($resTrain as $k => $v) {
                    if ($v->id == $item->id) {
                        array_push($childrens, [
                            'id' => $v->course_id,
                            'name' => $v->course_name,
                            'face_image' => $this->getRightUrl($v->course_face_image)
                        ]);
                    }
                }
                $res[$item->id]['children'] = $childrens;
            }

        }
        return array_values($res);


    }

    /*
     * 给培训下内容排序
     */
    public function sortTrainingCourse()
    {

        $id = $this->request->id;
        $arrCourse = $this->request->course;
        $newArrCourse = $this->addDataToArray($arrCourse, 'training_id', $id);
        try {
            //先删除当前培训下的内容
            DB::table('training_course')->where('training_id', $id)->delete();
            //入库
            DB::table('training_course')->insert($newArrCourse);

            $res = [
                'success' => true,
                'message' => ''
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
     * 获取培训信息
     */
    public function getTrainingInfo()
    {
        $id = $this->request->id;
        //获取数据
        $reTraining = DB::table('training');
        $select = [
            'id',
            'name',
            'category_id',
            '`desc`',
            'degree',
            'face_image',
            'dept_names',
            'dept_ids',
            'status',
            'start_date',
            'end_date'
        ];
        $strSelect = implode(',', $select);
        $arrTraining = $reTraining->select(DB::raw($strSelect))->find($id);
        $res = [];
        if ($arrTraining) {
            $arrTraining->face_image = $this->getRightUrl($arrTraining->face_image);
            $objCategory = DB::table('training_category')->find($arrTraining->category_id);
            $arrTraining->category_name = $objCategory->name;
            $res = $arrTraining;
        }
        return $res;


    }

    /*
     * 获取当前人的培训数量
     */
    public function getUserTrainingCount()
    {
        //当前人guid
        $guid = $this->userInfo['guid'];
//                $count = $this->getPublishTrainingCount($guid);
        $count = $this->getTrainingCount1($guid);
        return ['count' => $count];

    }

    /*
     * 处理人员数据
     *
     */
    public function dealUserData($arr)
    {
        foreach ($arr as $key => &$value) {
            $value['trainingCount'] = $this->getTrainingCount1($value['guid']);
        }
        return $arr;
    }

    /*
     * 根据人获取培训数目
     */
    public function getTrainingCount($guid)
    {
        return DB::table('training_student')->where('user', $guid)->count();
    }

    public function getTrainingCount1($guid)
    {
        //取出当前人所属的角色
        $arrRole = DB::table('role_user')->where('guid', $guid)->pluck('role_id')->toArray();
        //取出所有角色下的培训，并去重
        $res = DB::table('training')->leftJoin('role_training', 'training.id', '=', 'role_training.training_id')->whereIn('role_training.role_id', $arrRole)->orWhere(function ($query) {
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
                ]);
            })->distinct()->pluck('training.id')->toArray();
        return count($res);
    }

    /*
     * 移动端获取当前人培训数量(已发布的）
     */
    public function getPublishTrainingCount($guid)
    {
        $where = [
            [
                'training_student.user',
                '=',
                $guid
            ],
            [
                'training.status',
                '=',
                1
            ],
            [
                'training.end_date',
                '>=',
                now()
            ]

        ];
        return DB::table('training_student')->join("training", 'training.id', '=', 'training_student.training_id')->where($where)->count();
    }

    /*
     * 根据人获取培训id
     */
    public function getTrainingByGuid($guid)
    {
        return DB::table('training_student')->where('user', $guid)->get();
    }

    /*
     * 处理培训数据
     */
    public function dealTrainingData($obj)
    {
        $arr = [
            0 => "待发布",
            1 => "已发布",
            2 => "已下架",
            3 => "已结束"
        ];
        foreach ($obj as $key => $item) {
            //如果当前培训已经结束了就设置状态为已结束
            if ($item->status !== 3 && $item->end_date <= now()) {
                $this->setTrainingOver($item->id);
                $item->status = 3;
            }
            $item->userCount = $this->getUserCount($item->id);
            $item->courseCount = $this->getCourseCount($item->id);
            $item->statusName = $arr[$item->status];
            $item->categoryName = $this->getTrainingCatById($item->category_id)->name;

        }
        return $obj;
    }

    public function getTrainingCatById($id)
    {
        return DB::table("training_category")->select(DB::raw("id,name"))->find($id);
    }

    /*
     * 设置学习记录
     */
    public function setStudentLog()
    {

        $arrFiled = [
            'training_id' => $this->request->training_id,
            'training_name' => $this->request->training_name,
            'course_id' => $this->request->course_id,
            'course_name' => $this->request->course_name,
            'resource_id' => $this->request->resource_id,
            'resource_name' => $this->request->resource_name,
            'file_id' => $this->request->file_id,
            'original_name' => $this->request->original_name,
            'study_date' => $this->request->study_date,
            'guid' => $this->userInfo['guid'],
        ];
        DB::table('study_log')->insert($arrFiled);


    }

    /*
     * 设置线下学习记录
     */
    public function setStudentOutLineLog()
    {
        $arrFiled = [
            "process_number" => $this->request->process_number,
            "application_date" => $this->request->application_date,
            "title" => $this->request->title,
            "user_guid" => $this->request->user_guid,
            "user_name" => $this->request->user_name,
            "company" => $this->request->company,
            "dept_name" => $this->request->dept_name,
            "post_name" => $this->request->post_name,
            "rank" => $this->request->rank,
            "training_type" => $this->request->training_type,
            "training_startdate" => $this->request->training_startdate,
            "training_enddate" => $this->request->training_enddate,
            "training_days" => $this->request->training_days,
            "course_name" => $this->request->course_name,
            "training_org" => $this->request->training_org,
            "traning_address" => $this->request->traning_address,
            "training_character" => $this->request->training_character,
            "is_certification" => $this->request->is_certification,
            "training_user" => $this->request->training_user,
            "training_count" => $this->request->training_count,
            "course_content" => $this->request->course_content,
            "training_goal" => $this->request->training_goal,
            "training_resource" => $this->request->training_resource,
            "training_cost" => $this->request->training_cost,
            "certification_cost" => $this->request->certification_cost,
            "travel_cost" => $this->request->travel_cost,
            "other_cost" => $this->request->other_cost,
            "cost_total" => $this->request->cost_total,
            "cost_total_upper" => $this->request->cost_total_upper,
            "is_sign" => $this->request->is_sign,
            "training_range" => $this->request->training_range,
            "desc" => $this->request->desc
        ];

        DB::table('outline_study_log')->insert($arrFiled);
    }

    /*
     * 获取线上学习记录列表
     */
    public function getSudentOnlineLogList()
    {

        $trainingName = $this->request->training_name;
        $courseName = $this->request->course_name;
        $resourceName = $this->request->resource_name;
        $originalName = $this->request->original_name;
        $startDate = $this->request->start_date;
        $endDate = $this->request->end_date;
        $guid = $this->request->guid;
        $objLog = DB::table('study_log');
        $select = [
            'id',
            'training_name',
            'course_name',
            'resource_name',
            'original_name',
            'study_date'
        ];
        $strSelect = implode(',', $select);
        $objLog->select(DB::raw($strSelect));
        $where = [
            [
                'guid',
                '=',
                $guid
            ]
        ];
        if ($trainingName) {
            array_push($where, [
                'training_name',
                'like',
                '%' . $trainingName . '%'
            ]);
        }
        if ($courseName) {
            array_push($where, [
                'training_name',
                'like',
                '%' . $courseName . '%'
            ]);
        }
        if ($resourceName) {
            array_push($where, [
                'training_name',
                'like',
                '%' . $resourceName . '%'
            ]);
        }
        if ($originalName) {
            array_push($where, [
                'training_name',
                'like',
                '%' . $originalName . '%'
            ]);
        }
        if ($startDate) {
            array_push($where, [
                'start_date',
                '>=',
                $startDate
            ]);
        }
        if ($endDate) {
            array_push($where, [
                'end_date',
                '<=',
                $endDate
            ]);
        }
        $resLog = $objLog->where($where)->orderBy('study_date', 'desc')->paginate($this->perpage)->toArray();
        return $this->setPage($resLog);

    }

    /*
     * 获取线下学习记录列表
     */
    public function getSudentOutlineLogList()
    {
        $guid = $this->request->guid;
        $objLog = DB::table('outline_study_log');
        $select = [
            'id',
            'course_name',
            'training_type',
            'cost_total',
            'application_date'
        ];
        $strSelect = implode(',', $select);
        $objLog->select(DB::raw($strSelect));
        $where = [
            [
                'user_guid',
                '=',
                $guid
            ]
        ];
        $arrTrainingType = [
            1 => '个人培训',
            2 => '部门/机构培训',
            3 => '公司培训'
        ];
        $resLog = $objLog->where($where)->orderBy('id', 'desc')->paginate($this->perpage)->toArray();
        //        foreach ($resLog['data'] as $key => $item) {
        //            $item->application_date= substr($item->application_date, 0, strpos($item->application_date, '.'));
        //        }
        return $this->setPage($resLog);


    }

    /*
     * 详情
     */
    public function getSudentOutlineLogDesc()
    {
        $id = $this->request->id;
        $resDesc = DB::table('outline_study_log')->find($id);
        if (!$resDesc) {
            $resDesc = [];
        }
        //        $resDesc->application_date = substr($resDesc->application_date, 0, strpos($resDesc->application_date, '.'));
        return $resDesc;
    }

    /*
     * 设置培训为全部人可看
     */
    public function setDefault()
    {
        $training_id = $this->request->training_id;
        $is_default = $this->request->is_default;
        return DB::table('training')->where('id', $training_id)->update(['is_default' => $is_default]);

    }

    /*
     * 跟新培训状态为已结束
     *
     */
    public function setTrainingOver($id)
    {
        DB::table('training')->where('id', $id)->update(['status' => 3]);
    }

    /*
     * 根据培训id 获取总人数
     */
    public function getUserCount($id)
    {
        return DB::table('training_student')->where('training_id', $id)->count();
    }

    /*
     * 根据培训id 获取课程数
     */
    public function getCourseCount($id)
    {
        return DB::table('training_course')->where('training_id', $id)->count();
    }

    /*
     * 处理数组 变字符串
     */
    public function arrTostr($arr)
    {
        $arrIds = [];
        $arrNames = [];
        foreach ($arr as $key => $item) {
            $arrIds[$key] = $item['id'];
            $arrNames[$key] = $item['name'];
        }
        return [
            'strIds' => implode(',', $arrIds),
            'strNames' => implode(',', $arrNames),
        ];
    }


}