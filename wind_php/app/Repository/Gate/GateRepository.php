<?php
/**
 * Desc: 网关管理
 * User: Zhaojinsheng
 * Date: 2020/8/13
 * Time: 09:49
 * Filename:GateRepository.php
 */

namespace App\Repository\Gate;

use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;
use App\Library\Log\SysLog;
use function foo\func;

class GateRepository extends BaseRepository
{

    public function getTempData()
    {
        $gwId = $this->request->gw_id;

        $snId = $this->request->sn_id;

        $startTime = $this->request->start_time;

        $endTime = $this->request->end_time;


        $pageSize = $this->request->pageSize ? $this->request->pageSize : config('common.pageSize');

        $where = [['show', '=', 1]];

        if ($gwId) {

            array_push($where, [
                'gw_id', 'like', '%' . $gwId . '%'
            ]);
        }
        if ($gwId) {

            array_push($where, [
                'sn_id', 'like', '%' . $snId . '%'
            ]);
        }
        if ($startTime) {

            array_push($where, [
                'create_time', '>=', $startTime
            ]);

        }
        if ($endTime) {

            array_push($where, [
                'create_time', '<=', $endTime
            ]);

        }

        $list = DB::table('wind_sensors_data')->where($where)->orderby('create_time', 'desc')->paginate($pageSize)->toArray();

        $listData = $this->setPage($list);

        return $listData;

    }

    public function getAllTempData()
    {

        $gwId = $this->request->gw_id;

        $snId = $this->request->sn_id;

        $startTime = $this->request->start_time ? round($this->request->start_time/1000) : '';

        $endTime = $this->request->end_time ? round($this->request->end_time/1000) : '';


        $pageSize = $this->request->pageSize ? $this->request->pageSize : config('common.pageSize');

        $where = [];

        if ($gwId) {

            array_push($where, [
                'gw_id', 'like', '%' . $gwId . '%'
            ]);
        }
        if ($gwId) {

            array_push($where, [
                'sn_id', 'like', '%' . $snId . '%'
            ]);
        }
        if ($startTime) {

            array_push($where, [
                'create_time', '>=', $startTime
            ]);

        }
        if ($endTime) {

            array_push($where, [
                'create_time', '<=', $endTime
            ]);

        }

        $list = DB::table('wind_sensors_data')->where($where)->orderby('create_time', 'desc')->paginate($pageSize);

        $list->each(function ($item) {

            $item->create_time = date("Y-m-d H:i:s", $item->create_time);
        });

        $listData = $this->setPage($list->toArray());

        return $listData;

    }

    public function getGateList()
    {

        $gwId = $this->request->gw_id;

        $where = [];

        if ($gwId) {

            array_push($where, [
                'gw_id', 'like', '%' . $gwId . '%'
            ]);
        }

        $list = DB::table('wind_gate')->where($where)->orderby('update_time', 'desc')->get();

        $time = strtotime('-600 seconds');

        $list->each(function ($item) use ($time) {


            if ($item->update_time < $time) {


                $item->status_name = '失效';
                $item->status = false;

            } else {


                $item->status_name = '正常';
                $item->status = true;
            }

            $item->update_time = date("Y-m-d H:i:s", $item->update_time);
        });

        return $list;
    }

    public function getSensorList()
    {

        $snId = $this->request->sn_id;

        $gwId = $this->request->gw_id;

        $pageSize = $this->request->pageSize ? $this->request->pageSize : config('common.pageSize');

        $where = [];

        if ($gwId) {

            array_push($where, [
                'gw_id', 'like', '%' . $gwId . '%'
            ]);
        }

        if ($snId) {

            array_push($where, [
                'sn_id', 'like', '%' . $snId . '%'
            ]);

        }
        $list = DB::table('wind_sensors')->where($where)->paginate($pageSize);

        $time = strtotime('-600 seconds');

        $list->each(function ($item) use ($time) {

            if ($item->update_time < $time) {


                $item->status_name = '失效';
                $item->status = false;

            } else {


                $item->status_name = '正常';
                $item->status = true;
            }

            $item->update_time = date("Y-m-d H:i:s", $item->update_time);
        });

        return $this->setPage($list->toArray());

    }

    public function saveHeartBeat()
    {

        $log = new SysLog();

        try {

            return $this->doHbData();

        } catch (\Exception $exception) {

            $log->addLog('错误：' . $exception->getMessage(), 'hb');

            return ['code' => 500, 'msg' => 'fail', 'data' => ''];
        }


    }


    public function saveSensorsData()
    {


        $log = new SysLog();

        try {

            return $this->doData();

        } catch (\Exception $exception) {

            $log->addLog('错误：' . $exception->getMessage(), 'data');

            return ['code' => 500, 'msg' => 'fail', 'data' => ''];
        }

    }

    private function doData()
    {

        $postData = $this->request->post_data;

        $arrPostData = json_decode($postData, true);

        $log = new SysLog();

        $log->addLog('接收的参数：' . print_r($arrPostData, true), 'data');

        //客户端ip
        $ip = $this->request->ip();

        $arrPostData['s_time'] = strtotime($arrPostData['s_time']);

        $arrPostData['ip'] = $ip;

        $arrPostData['create_time'] = time();

//        $arrPostData['data'] = $postData;

        //看此传感器是否存在
        $where = [

            ['gw_id', '=', $arrPostData['gw_id']],
            ['sn_id', '=', $arrPostData['sn_id']]
        ];

        $objModle = DB::table('wind_sensors');

        $sensorInfo = $objModle->where($where)->first();

        if (!$sensorInfo) {

            $arrInsert = [

                'gw_id' => $arrPostData['gw_id'],
                'sn_id' => $arrPostData['sn_id'],
                'ST' => $arrPostData['ST'],
                'temp' => $arrPostData['temp'],
                'update_time' => time(),
                'create_time' => time(),
            ];

            $objModle->insert($arrInsert);

        } else {

            //更新传感器表
            $arrSensor = [
                'ST' => $arrPostData['ST'],
                'temp' => $arrPostData['temp'],
                'update_time' => time(),
            ];

            $objModle->where($where)->update($arrSensor);

        }

        DB::table('wind_sensors_data')->insert($arrPostData);

        $result = ['code' => 200, 'msg' => 'success', 'data' => ''];

        $log->addLog('返回的参数：' . print_r($result, true), 'data');

        return $result;
    }

    private function doHbData()
    {


        $data = $this->request->all();

        $log = new SysLog();

        $log->addLog('接收的参数：' . print_r($data, true), 'hb');

        $gwId = $data['gw_id'];

        $statusCode = isset($data['statuscode']) ? $data['statuscode'] : '';

        $objOper = DB::table('wind_operation_score');

        //首先先更新快照表中操作的处理状态

        if ($statusCode) {

            $objOper->where('id', $statusCode)->update(['is_send' => 1]);
        }


        //查看是否是新的网关
        $objGate = DB::table('wind_gate');

        $gateInfo = $objGate->where('gw_id', $gwId)->first();

        $ip = $this->request->ip();

        if (!$gateInfo) {

            $gateFiled = [
                'gw_id' => $gwId,
                'ip' => $ip,
                'update_time' => time()
            ];

            $objGate->insert($gateFiled);

        } else {

            $objGate->where('gw_id', $gwId)->update(['update_time' => time(), 'ip' => $ip]);
        }
        //首次发送传感器列表
        $objSensors = DB::table("wind_sensors");

        if (isset($data['sn_list'])) {

            $str = str_replace('[', "", $data['sn_list']);
            $str = str_replace(']', "", $str);
            $snList = explode(',', $str);

            foreach ($snList as $item) {

                $item = trim($item, "'");
                //查看是否已经存在该记录
                $where = [
                    ['gw_id', '=', $gwId],
                    ['sn_id', '=', $item]
                ];

                $sensorsInfo = $objSensors->where($where)->first();

                if (!$sensorsInfo) {

                    $sensorFiled = [
                        'gw_id' => $gwId,
                        'sn_id' => $item,
                        'create_time' => time(),

                    ];

                    $objSensors->insert($sensorFiled);
                }

            }

            //首次启动直接返回
            $result = ['code' => 200, 'msg' => 'sn_rec', 'data' => '', 'statuscode' => ''];

            $log->addLog('返回的参数：' . print_r($result, true), 'hb');

            return $result;
        }

        //从快照表中取出最新的操作发送给网关
        $result = ['code' => 200, 'msg' => '', 'data' => '', 'statuscode' => ''];

        $operWhere = [

            ['gw_id', '=', $gwId],
            ['is_send', '=', 0],
        ];
        //取第一条
        $operation = $objOper->where($operWhere)->first();

        $flag = [1 => 'gw_config', 2 => 'gw_reboot', 3 => 'download_logs', 4 => 'sn_config', 5 => 'del_snId', 6 => 'back_connect'];


        if ($operation) {

            if (in_array($operation->type, [1, 4])) {

                $operationData = json_decode($operation->data);

                $result['data'] = $operationData;


            } else if ($operation->type === 5) {

                $result['data'] = $operation->data;
            }

            $result['msg'] = $flag[$operation->type];

            $result['statuscode'] = $operation->id;

        } else {

            $result['msg'] = 'success';
        }

        $log->addLog('返回的参数：' . print_r($result, true), 'hb');

        return $result;

    }

    public function configGw()
    {

        $config = $this->request->all();

        $id = $config['id'];

        $gwId = $config['gw_id'];

        unset($config['id'], $config['gw_id']);

        $updateRes = DB::table('wind_gate')->where('id', $id)->update($config);

        if ($updateRes) {

            $dataConfig = [
                'key' => '',
                'data_status' => $config['data_status'],
                'reboot_time' => $config['reboot_time'],
                'system_time' => time(),
                'hb_status' => $config['hb_status'],
                'gw_id' => $gwId
            ];
            //加入快照表
            $arrFiled = [];

            $arrFiled['gw_id'] = $gwId;
            $arrFiled['type'] = 1;
            $arrFiled['is_send'] = 0;
            $arrFiled['data'] = json_encode($dataConfig);
            $arrFiled['create_time'] = time();

            DB::table("wind_operation_score")->insert($arrFiled);


        }


    }

    public function sendLog()
    {

        $gwId = $this->request->gw_id;

        //加入快照表
        $arrFiled = [];

        $arrFiled['gw_id'] = $gwId;
        $arrFiled['type'] = 3;
        $arrFiled['is_send'] = 0;
        $arrFiled['data'] = '';
        $arrFiled['create_time'] = time();

        DB::table("wind_operation_score")->insert($arrFiled);
    }


    public function configSensor()
    {

        $config = $this->request->all();

        $id = $config['id'];

        $gwId = $config['gw_id'];

        unset($config['id'], $config['gw_id']);

        $arrSensor = $config;

        $arrSensor['create_time'] = time();

        $updateRes = DB::table('wind_sensors')->where('id', $id)->update($config);

        if ($updateRes) {
            //加入快照表
            $arrFiled = [];

            $arrFiled['gw_id'] = $gwId;
            $arrFiled['sn_id'] = $config['sn_id'];
            $arrFiled['type'] = 4;
            $arrFiled['is_send'] = 0;
            $arrFiled['data'] = json_encode($config);
            $arrFiled['create_time'] = time();

            DB::table("wind_operation_score")->insert($arrFiled);

        }


    }

    public function gwReboot()
    {

        $gwId = $this->request->gw_id;

        //加入快照表
        $arrFiled = [];

        $arrFiled['gw_id'] = $gwId;
        $arrFiled['type'] = 2;
        $arrFiled['is_send'] = 0;
        $arrFiled['data'] = '';
        $arrFiled['create_time'] = time();

        DB::table("wind_operation_score")->insert($arrFiled);
    }

    public function getLogFile()
    {

        $gwId = $this->request->gw_id;

        $pageSize = $this->request->pageSize ? $this->request->pageSize : config('common.pageSize');

        $logInfo = DB::table('wind_gate_log')->where('gw_id', $gwId)->paginate($pageSize);


        $logInfo->each(function ($item) {

            $item->create_time = date("Y-m-d H:i:s", $item->create_time);
        });

        return $this->setPage($logInfo->toArray());
    }

    public function backConnect()
    {

        $gwId = $this->request->gw_id;

        //加入快照表
        $arrFiled = [];

        $arrFiled['gw_id'] = $gwId;
        $arrFiled['type'] = 6;
        $arrFiled['is_send'] = 0;
        $arrFiled['data'] = '';
        $arrFiled['create_time'] = time();

        DB::table("wind_operation_score")->insert($arrFiled);

    }

    public function getDayData()
    {

        $gwId = $this->request->gw_id;



        $sT = $this->request->ST;

        $snId = $this->request->sn_id;

        $startTime = $this->request->start_time ? round($this->request->start_time/1000) : strtotime('today');

        $endTime = $this->request->end_time ? round($this->request->end_time/1000) : time();


        $where = [

            ['create_time', '>=', $startTime],
            ['create_time', '<=', $endTime],
        ];

        if ($gwId) {

            array_push($where, [

                'gw_id', '=', $gwId
            ]);
        }else{

            //如果没有网关直接放回空数据
            return [];
        }
        if ($sT) {

            array_push($where, [

                'ST', '=', $sT
            ]);
        }
        if ($snId) {

            array_push($where, [

                'sn_id', '=', $snId
            ]);
        }

        $list = DB::table('wind_sensors_data')->select('id','sn_id', 'temp','create_time')->where($where)->get();

        //处理数据

        $arrList = [];

        $list->each(function($item) use(&$arrList){

            $arr = [date('Y-m-d H:i:s',$item->create_time),$item->temp];

            $arrList[$item->sn_id][] = $arr;
        });


        return $arrList;
    }

    public function getGwAndSn()
    {

        $list = DB::table('wind_gate')->select('id','gw_id as name')->get();

        //查看有几种类型传感器
        $listSensorST = DB::table('wind_sensors')->distinct()->pluck('ST');


        //处理数据
        foreach($list as $key=>$item){

            $arrChild = [];

            foreach($listSensorST as $k=>$v){

                $arrSn= [];
                $where = [
                    ['gw_id','=',$item->name],
                    ['ST','=',$v],
                ];

                $arrSnList = DB::table('wind_sensors')->select('id','sn_id as name')->where($where)->get();

                $arrSn['id'] = $k;
                $arrSn['name'] = $v;

                $arrSnList->each(function($value,$index) use (&$arrSn){

                    $arrSn['children'] [$index]=$value;

                });

                $arrChild[$k]=$arrSn;

            }

            $item->children = $arrChild;

        }


        return $list->toArray();

    }

}