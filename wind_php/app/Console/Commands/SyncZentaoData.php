<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Proxy\TokenProxy;
use Illuminate\Support\Facades\DB;

class SyncZentaoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncZentaoData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '同步禅道数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $_time;


    public function __construct()
    {
        parent::__construct();
        $this->_time = time();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        echo "----开始处理部门数据----\r\n";
        $this->dealData(1);
        echo "----开始处理人员数据----\r\n";
        $this->dealData(2);
        echo "----全部处理完毕----\r\n";
    }

    /*
     * 处理数据
     */
    private function dealData($type)
    {
        $localOrm = DB::table('zentao_sync_data');
        $objData = $localOrm->where([
            [
                'status',
                '=',
                0
            ],
            [
                'type',
                '=',
                $type
            ]
        ])->orderBy('id')->get();
        $typeName = $type == 1 ? '部门' : '人员';
        if($objData->isEmpty()){
            echo("----------没有可处理的{$typeName}数据----\r\n");
        }else{

            foreach ($objData as $item) {
                $arrData = json_decode($item->str_json);
                $arrData->data_id = $item->id;
                echo "----------开始处理{$typeName} guid:{$item->guid}----\r\n";
                try {
                    echo "----------插入It服务----\r\n";
                    $this->insertData($arrData, $type, 1);
                    echo "----------插入bcis----\r\n";
                    $this->insertData($arrData, $type, 2);
                    echo "----------插入政府事务----\r\n";
                    $this->insertData($arrData, $type, 3);

                    //跟新状态
                    echo "----------guid:{$item->guid} 处理成功并跟新了状态----\r\n";
                    DB::table('zentao_sync_data')->where('id', $item->id)->update(['status' => 1]);

                } catch (\Exception $e) {
                    echo "----------{$typeName} guid:{$item->guid} 处理失败----\r\n";
                    $mess = $e->getMessage();
                    $line = $e->getLine();
                    print_r([
                        'msg' => $mess,
                        'line' => $line,
                        'guid' => $arrData->guid,
                    ]);
                    //                    die("\r\n");
                    continue;
                }
            }
        }



    }

    /*
     * 更新It服务
     * $ormType  1：插入It服务  ，2：插入bcis ， 3：插入Task
     */
    private function insertData($arrData = [], $type, $ormType)
    {
        //部门
        if ($type == 1) {

            //判断是否存在
            $objDept = $this->getOrm($ormType)->table('zt_dept')->where('guid', $arrData->guid)->first();

            if($arrData->isDeleted == true){
                if($objDept){
                    $this->getOrm($ormType)->table('zt_dept')->where('id', $objDept->id)->delete();
                }
            }else{

                if ($arrData->parentGuid == null) {
                    $parentId = 0;
                } else {
                    //查出当前部门的父亲
                    $objParentDept = $this->getOrm($ormType)->table('zt_dept')->where('guid', $arrData->parentGuid)->first();
                    $parentId = $objParentDept->id;
                }
                $isWuye = in_array($arrData->chineseName,['工程部','客服部','安保部']) ? 1 : 0 ;
                if($objDept){
                    //处理deepCode
                    $arrDeepCode = $arrData->deepCode == null ? [] : explode("@", $arrData->deepCode);
                    if ($arrDeepCode) {
                        $arrDeepCodeDept = $this->getOrm($ormType)->table('zt_dept')->whereIn('guid', $arrDeepCode)->pluck('id')->toArray();
                        $path = "," . implode(',', $arrDeepCodeDept) . "," . $objDept->id . ",";
                    } else {
                        $path = "," . $objDept->id . ",";
                    }
                    $arrPath = explode(',', trim($path, ','));
                    $arrUpdateDept = [
                        'name' => $arrData->chineseName,
                        'parent' => $parentId,
                        'path' => $path,
                        'grade' => count($arrPath),
                        'guid' => $arrData->guid,
                        'is_wuye' => $isWuye,
                    ];
                    //更新
                    $this->getOrm($ormType)->table('zt_dept')->where('id', $objDept->id)->update($arrUpdateDept);
                }else{
                    //处理deepCode
                    $arrDeepCode = $arrData->deepCode == null ? [] : explode("@", $arrData->deepCode);
                    if ($arrDeepCode) {
                        $arrDeepCodeDept = $this->getOrm($ormType)->table('zt_dept')->whereIn('guid', $arrDeepCode)->pluck('id')->toArray();
                        $path = "," . implode(',', $arrDeepCodeDept) . ",";
                    } else {
                        $path = ",";
                    }

                    $arrInsertDept = [
                        'name' => $arrData->chineseName,
                        'parent' => $parentId,
                        'path' => $path,
                        'guid' => $arrData->guid,
                        'is_wuye' => $isWuye,
                    ];
                    $id = $this->getOrm($ormType)->table('zt_dept')->insertGetId($arrInsertDept);
                    $newPath = $path . $id . ",";
                    $arrNewPath = explode(',', trim($newPath, ','));
                    $arrCurrentDept = [
                        'grade' => count($arrNewPath),
                        'path' => $newPath,

                    ];
                    $this->getOrm($ormType)->table('zt_dept')->where('id', $id)->update($arrCurrentDept);
                }


            }
        }
        //人员
        if ($type == 2) {
            //判断是否存在
            $objUser = $this->getOrm($ormType)->table('zt_user')->where('guid', $arrData->guid)->first();
            //查出部门所属id
            $currentDept = $this->getOrm($ormType)->table('zt_dept')->where('guid', $arrData->departmentGuid)->first();
            if ($objUser) {
                $arrUpdate = [
                    'dept' => $currentDept->id,
                    'guid' => $arrData->guid,
                    'account' => $arrData->userName ? $arrData->userName :'',
                    'realname' => $arrData->chineseName ? $arrData->chineseName:'',
                    'birthday' => date('Y-m-d', strtotime($arrData->birthday)),
                    'join' => date('Y-m-d', strtotime($arrData->hireDate)),
                    'email' => $arrData->email ? $arrData->email :'',
                    'gender' => $arrData->gender == 1 ? 'm' : 'f',
                    'pinyin' => $arrData->namePinYin ?$arrData->namePinYin:'',
                    'short_pinyin'=> $arrData->nameShortPinYin ? $arrData->nameShortPinYin:'',
                    'foreign_name'=>$arrData->foreignName ? $arrData->foreignName : '',
                    'mobile'=>$arrData->mobilePhone ? $arrData->mobilePhone : '',
                ];
                if ($arrData->isDeleted == true) {
                    $arrUpdate['deleted'] = 1;
                }
                $this->getOrm($ormType)->table('zt_user')->where('id', $objUser->id)->update($arrUpdate);
            } else {
                $insertData = [
                    'dept' => $currentDept->id,
                    'account' => $arrData->userName ? $arrData->userName :'',
                    'realname' => $arrData->chineseName ? $arrData->chineseName:'',
                    'email' => $arrData->email ? $arrData->email :'',
                    'guid' => $arrData->guid,
                    'birthday' => date('Y-m-d', strtotime($arrData->birthday)),
                    'locked' => '1970-01-01 00:00:00',
                    'commiter' => '',
                    'role' => 'dev',
                    'join' => date('Y-m-d', strtotime($arrData->hireDate)),
                    'password' => '7d200cd167525c854091e3cf6baa4927',
                    'gender' => $arrData->gender == 1 ? 'm' : 'f',
                    'pinyin' => $arrData->namePinYin ?$arrData->namePinYin:'',
                    'short_pinyin'=> $arrData->nameShortPinYin ? $arrData->nameShortPinYin:'',
                    'foreign_name'=>$arrData->foreignName ? $arrData->foreignName : '',
                    'mobile'=>$arrData->mobilePhone ? $arrData->mobilePhone : '',
                ];
                $this->getOrm($ormType)->table('zt_user')->insert($insertData);
            }


        }


    }

    /*
     * 获取构造器
     */
    private function getOrm($type)
    {
        $arr = [
            1 => 'mysql_zentao',
            2 => 'mysql_zentao_bcis',
            3 => 'mysql_zentao_government'
        ];

        return DB::connection($arr[$type]);
    }

    /*
     * 获取数据
     */
    private function getDataByGuid($arrParams)
    {

        //获取所有学生
        $token = $this->getToken();
        //请求接口
        $objPorxy = new TokenProxy();
        $url = '';
        $result = $objPorxy->sendHttp($url, 'POST', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
            'json' => $arrParams
        ]);
        return $result;
    }

    /*
     * 获取token
     */
    private function getToken()
    {
        return '';
    }
}
