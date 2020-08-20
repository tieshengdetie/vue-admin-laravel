<?php
namespace App\Repository\DataService;
use Illuminate\Support\Facades\DB;
use App\Repository\BaseRepository;
use App\Library\Proxy\TokenProxy;
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-02-27
 * Time: 11:15
 */
class SyncUserRepository extends BaseRepository{

    public function receiveNotify(){
        //接收参数
        $arrDepts = $this->request->TrainingInterfaceOrg;   //机构详情
        $arrUsers = $this->request->TrainingInterfaceStaff; //用户信息
        $arrDeptsFiled = $this->formatInsertData($arrDepts,1);
        $arrUsersFiled = $this->formatInsertData($arrUsers,2);
        //合并
        $arrFiled = array_merge($arrDeptsFiled,$arrUsersFiled);
        if(!$arrFiled) return false;
        //插入数据
        DB::table("zentao_sync_data")->insert($arrFiled);



    }
    /*
     * 接受全量数据
     */
    public function getAllData(){
        //请求接口
        $type = $this->request->type;

        $objPorxy = new TokenProxy();

        $token = $this->getToken();

        $url = $type == 1 ? config('zentao.url.getAllData') : config('zentao.url.getAllWaibaoData');

        $result = $objPorxy->sendHttp($url, 'POST', [
            'headers' => [
                'Authorization' => $token,
                'Content-Type' => 'application/json;charset=utf-8',
            ],
        ]);

        if(isset($result['data']['result'])){
            $arrData = $result['data']['result'];
            $arrDept = $this->formatInsertData($arrData['trainingInterfaceOrg'],1) ;
            $arrUser = $this->formatInsertData($arrData['trainingInterfaceStaff'],2);
            $arrFiled = array_merge($arrDept,$arrUser);
            //插入数据
            DB::table("zentao_sync_data")->insert($arrFiled);

        }
    }
    /*
     * 格式化数据
     */
    private function formatInsertData($arr,$type){
        $arrFiled =[];
        $arrGuid = [];
        if(!$arr)return [];
        foreach($arr as $item){
            $arrGuid[]=$item['guid'];
            $arrItem = [
                'guid' => $item['guid'],
                'type' => $type,
                'status' => 0,
                'str_json' => json_encode($item),
                'add_time' =>time(),
            ];
            array_push($arrFiled,$arrItem);
        }
        $this->deleteReceiveData($arrGuid);
        return $arrFiled;
    }
    /*
     * 删除已经推送过了的数据
     */
    private function deleteReceiveData($arr){
        DB::table('zentao_sync_data')->whereIn('guid',$arr)->delete();
    }
}