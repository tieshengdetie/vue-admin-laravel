<?php
/**
 * Desc: 网关管理
 * User: Zhaojinsheng
 * Date: 2020/8/13
 * Time: 09:46
 * Filename:GateController.php
 */

namespace App\Http\Controllers\Api;

use App\Repository\Gate\GateRepository as Repository;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\DB;

class GateController extends ApiController
{


    public function getTempData(Repository $repository)
    {


        $res = $repository->getTempData();

        return $this->success($res);
    }

    public function getAllTempData(Repository $repository)
    {

        $res = $repository->getAllTempData();

        return $this->success($res);
    }

    public function getGateList(Repository $repository)
    {

        $res = $repository->getGateList();

        return $this->success($res);
    }

    public function getSensorList(Repository $repository)
    {

        $res = $repository->getSensorList();

        return $this->success($res);
    }


    public function saveHeartBeat(Repository $repository)
    {
        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required|string',

            ],
            [
                'required' => ':attribute is not post',
                'string' => ':attribute type error',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            $res = [
                'code' => 500,
                'msg' => $msg,
                'data' => ''
            ];

            return $this->respond($res);
        }

        $res = $repository->saveHeartBeat();

        return $this->respond($res);


    }


    public function saveSensorsData(Repository $repository)
    {

        //验证表单
        $validateArray = [
            [
                'post_data' => 'required',

            ],
            [
                'required' => ':attribute is not post',

            ],
            [
                'post_data' => 'post_data',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            $res = [
                'code' => 500,
                'msg' => $msg,
                'data' => ''
            ];

            return $this->respond($res);
        }

        $res = $repository->saveSensorsData();

        return $this->respond($res);
    }

    //上传文件log
    public function receiveLog()
    {
        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }

        $file = $this->request->file('file');

        if( !$file->isValid()){

            $res = [
                'code' => 500,
                'msg' => 'file error',
                'data' => ''
            ];

            return $this->respond($res);
        }

        $gw_id = $this->request->gw_id;

        $originalName = $file->getClientOriginalName();

        $dir = 'gatelog' . DIRECTORY_SEPARATOR . date("Ymd");

        $info = $file->storeAs($dir, $originalName);

        if ($info) {
            //上传成功,保存路径
            $insert_data['file_name'] = $originalName;

            $insert_data['gw_id'] = $gw_id;

            $insert_data['file_url'] = $info;

            $insert_data['create_time'] = time();

            Db::table('wind_gate_log')->insert($insert_data);

            $res = [
                'code' => 200,
                'msg' => 'get file',
                'data' => ''
            ];

            return $this->respond($res);

        } else {
            $res = [
                'code' => 500,
                'msg' => 'error',
                'data' => ''
            ];

            return $this->respond($res);
        }
    }

    //配置网关
    public function configGw(Repository $repository)
    {

        //验证表单
        $validateArray = [
            [
                'id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'id' => 'ID',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $repository->configGw();

        return $this->success();


    }

    //配置网关
    public function sendLog(Repository $repository)
    {

        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $repository->sendLog();

        return $this->success();


    }

    public function gwReboot(Repository $repository)
    {

        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $repository->gwReboot();

        return $this->success();


    }

    //配置传感器
    public function configSensor(Repository $repository)
    {

        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $repository->configSensor();

        return $this->success();


    }

    public function getLogFile(Repository $repository)
    {
        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $res = $repository->getLogFile();

        return $this->success($res);

    }

    public function backConnect(Repository $repository)
    {
        //验证表单
        $validateArray = [
            [
                'gw_id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'gw_id' => 'gw_id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $res = $repository->backConnect();

        return $this->success($res);

    }

    public function downloadLog(){

        //验证表单
        $validateArray = [
            [
                'id' => 'required',

            ],
            [
                'required' => '参数错误',

            ],
            [
                'id' => 'id',

            ]
        ];


        $msg = $this->validateParam($validateArray);

        if ($msg !== true) {

            return $this->failed($msg);
        }
        $id = $this->request->id;

        $fileInfo = DB::table('wind_gate_log')->find($id);

        return response()->download(public_path('uploads/'.$fileInfo->file_url));

    }

    public function getDayData(Repository $repository){

        //验证表单

        $res = $repository->getDayData();

        return $this->success($res);
    }

    public function getGwAndSn(Repository $repository){

        $res = $repository->getGwAndSn();

        return $this->success($res);
    }
}