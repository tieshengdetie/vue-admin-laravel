<?php
/**
 * Desc: 用户逻辑层
 * User: Zhaojinsheng
 * Date: 2020/8/7
 * Time: 11:56
 * Filename:UserRepository.php
 */

namespace App\Repository\Permission;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;
use App\Library\Password\PasswordHash;
use App\Repository\Permission\RoleRepository;

class UserRepository extends BaseRepository
{


    public function createOrEditUser()
    {

        $userFiled = $this->request->all();

        $loginName = $userFiled['login_name'];

        $mobile = $userFiled['mobile'];

        $objModel = DB::table('wind_user');

        //判断手机是否重复
        $mobileInfo = $objModel->select("id")->where('mobile', $mobile)->first();

        $id = intval($userFiled['id']);

        unset($userFiled['id']);

        $userFiled['role_ids'] = ','.implode(',', $userFiled['role_ids']).',';

        $userFiled['leader'] = ','.implode(',', $userFiled['leader']).',';


        if ($id > 0) {

            if ($mobileInfo && $id !== $mobileInfo->id) {

                return ['code' => 0, 'msg' => '手机号重复'];

            }
            unset($userFiled['login_name']);

            return $objModel->where('id', $id)->update($userFiled);

        } else {

            if ($mobileInfo) {

                return ['code' => 0, 'msg' => '手机号重复'];
            }
            //判断用户名是否重复
            $loginNameInfo = $objModel->select("id")->where('login_name', $loginName)->first();

            if ($loginNameInfo) {

                return ['code' => 0, 'msg' => '登录名重复'];
            }
            //生成默认密码
            $str = config('common.default_password');

            $password = PasswordHash::createPassword($str);

            $userFiled['password'] = $password;

            $userFiled['avatar'] = config('common.avatar');

            $objModel->insert($userFiled);

        }

        return ['code' => 1, 'msg' => 'ok'];

    }

    public function getUserList()
    {

        //接受参数
        $pageSize = $this->request->pageSize ? $this->request->pageSize : config('common.pageSize');

        $loginName = $this->request->login_name;

        $realname = $this->request->realname;

        $mobile = $this->request->mobile;

        $email = $this->request->email;

        $deptId = $this->request->dept_id;

        $postId = $this->request->post_id;

        //构建where条件
        $where = [];

        if ($loginName) {

            array_push($where, [
                'login_name',
                'like',
                '%' . $loginName . '%'
            ]);

        }

        if ($realname) {

            array_push($where, [
                'realname',
                'like',
                '%' . $realname . '%'
            ]);

        }

        if ($mobile) {

            array_push($where, [
                'mobile',
                '=',
                $mobile
            ]);
        }

        if ($email) {

            array_push($where, [
                'email',
                '=',
                $email
            ]);
        }
        if ($deptId) {

            array_push($where, [
                'dept_id',
                '=',
                $deptId
            ]);
        }
        if ($postId) {

            array_push($where, [
                'post_id',
                '=',
                $postId
            ]);
        }
        $select = [
            'id',
            'login_name',
            'realname',
            'mobile',
            'email',
            'sex',
            'address',
            'dept_id',
            'post_id',
            'role_ids',
            'is_use',
            'leader',
            'last_login_time as lastLoginTime'
        ];
        $objModle = DB::table('wind_user');

        $strSelect = implode(',', $select);

        $objModle->select(DB::raw($strSelect));

        $resData = $objModle->where($where)->paginate($pageSize);


        $isUse = config('common.is_use');

        $sex = config('common.sex');

        //获取所有的角色
        $roleRepository = new RoleRepository($this->request);

        $roleList = $roleRepository->getAllRole();

        $roleChange = $this->formatObjectByKey($roleList, 'id');

        //处理数据
        if (!$resData->isEmpty()) {

            $arrLeaderId = [];
            //第一次循环
            foreach($resData as $value){

                $leader = $value->leader ? explode(',',trim($value->leader,',')) : [];

                $arrLeaderId = array_merge($arrLeaderId,$leader);

            }

            if(!empty($arrLeaderId)){
                //去重
                $arrLeaderId = array_unique($arrLeaderId);

                $arrLeaderData = DB::table('wind_user')->select(DB::raw($strSelect))->whereIn('id',$arrLeaderId)->get();

                $arrLeaderData = $this->formatObjectByKey($arrLeaderData,'id');

            }else{

                $arrLeaderData = [];
            }
            //第二次循环处理数据
            foreach ($resData as $key => $item) {

                $item->is_use_name = $isUse[$item->is_use];
                $item->sex_name = $sex[$item->sex];

                $roleArr = $item->role_ids ? explode(',',trim($item->role_ids,',') ) : [];

                $item->role_ids = $roleArr;

                if (!empty($roleArr)) {

                    $arrRoleList = array_map(function ($value) use ($roleChange) {

                        return isset($roleChange[$value]) ? $roleChange[$value] : '';

                    }, $roleArr);

                } else {

                    $arrRoleList = [];

                }
                $item->rolelist = $arrRoleList;

                $offLeaderId = $item->leader ? explode(',',trim($item->leader,',') ) : [];

                if(!empty($offLeaderId)){

                    $leaderList = array_map(function($off) use($arrLeaderData){

                        return isset($arrLeaderData[$off]) ? $arrLeaderData[$off] : '';

                    },$offLeaderId);

                }else{

                    $leaderList = [];
                }
                $item->leaderList = $leaderList;
                $item->leader = $offLeaderId;

                $item->post_name = "值长";
                $item->dept_name = "巡检部";
            }
        }


        $resData = $this->setPage($resData->toArray());

        return ['userData' => $resData, 'roleData' => $roleList];

    }

    public function setIsUse()
    {

        $id = $this->request->id;

        $isUse = $this->request->is_use;

        $userInfo = DB::table("wind_user")->find($id);

        if (1 === $userInfo->is_use) {

            $setIsUse = 0;

        } else {

            $setIsUse = 1;
        }

        DB::table('wind_user')->where('id', $id)->update(['is_use' => $setIsUse]);
    }

    public function resetPwd()
    {

        $id = $this->request->id;
        //生成默认密码
        $str = config('common.default_password');

        $password = PasswordHash::createPassword($str);

        DB::table("wind_user")->where('id', $id)->update(['password' => $password]);

    }

    public function getUserInfoById()
    {

        $userInfo = $this->request->get('userInfo');

        $perfectUser = DB::table('wind_user')->select('id', 'login_name as name', 'avatar', 'role_ids')->find($userInfo->id);

        $strRoleId = trim($perfectUser->role_ids);

        $arrRoleId = explode(',', $strRoleId);

        $roleInfo = DB::table('wind_role')->whereIn('id', $arrRoleId)->get()->toArray();

        $arrPer = [];

        if (!empty($roleInfo)) {

            array_walk($roleInfo, function ($item) use (&$arrPer) {

                $strMenuId = trim($item->permission, ',');

                $arrMenuId = explode(',', $strMenuId);

                $arrPer = array_merge($arrPer, $arrMenuId);

            });

            $arrPer = array_unique($arrPer);

        }

        $dataMenu = [];

        $dataButton = [];

        if (!empty($arrPer)) {

            $menuInfo = DB::table('wind_menu')->whereIn('id', $arrPer)->get();


            foreach ($menuInfo as $K => $v) {

                if(in_array($v->type,[1,3])){

                    array_push($dataMenu, $v->menu_code);

                }

                if(in_array($v->type,[2,3])){

                    array_push($dataButton, $v->menu_code);
                }


            }
        }
        $perfectUser->data = $dataMenu;

        $perfectUser->dataButton = $dataButton; //按钮权限


        return $perfectUser;


    }

    public function getUserByName(){

        $name = $this->request->name;

        $id = $this->request->id;

        $where = [
            ['is_use','=',1],
            ['id','<>',$id]
        ];

        if($name){

            array_push($where,['login_name','like','%'.$name.'%']);

            $res = DB::table('wind_user')->select('id','login_name')->where($where)->get();

        }else{
            $res = [];
        }



        return $res;
    }
}