<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-06-19
 * Time: 14:49
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Library\Proxy\TokenProxy;
use App\Library\Traits\Functions;


class SyncEmaiToItservice extends Command
{
    use Functions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncEmaiToItservice';

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

    private $_emailInfo;

    private $_poxy;


    public function __construct()
    {
        parent::__construct();
        $this->_poxy = new TokenProxy();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $lock_file =storage_path().'/logs/lock_log.txt';

        $ftp = fopen($lock_file, 'w+');

        if (flock($ftp, LOCK_EX)) {
            $this->startRun();
            flock($ftp, LOCK_UN);
            fclose($ftp);
        }else{
            echo "--上一个脚本还未执行完毕！--END--\r\n";die;
        }

    }

    public function startRun(){

        //获取email数据
        echo "--开始获取邮件数据.......\r\n";

        $arrEmail = $this->getEmailData();

        if(empty($arrEmail['data']['result'])){


            echo "--当前时间段无数据--END--\r\n";die;
        }
        $arrEmail = $arrEmail['data']['result'];

        //处理数据
        foreach($arrEmail as $key=>$item){

            $arrCurrentEmail = $this->_emailInfo[$item['accountNumber']];

            $type = $arrCurrentEmail->type;

            $autoAssignedTo = $arrCurrentEmail->autoAccount;

            $objProject= $this->getProjectByAccount($type,$autoAssignedTo);

            if(empty($item['emailDataJson'])||($item['emailDataJson']=='[]')) {

                //更新邮箱最后执行时间
                $this->updateEmailTime($arrCurrentEmail);

                echo "--邮箱：{$item['accountNumber']} 在当前时间段内无数据 \r\n";
                continue;

            }

            echo "--开始处理邮箱：{$item['accountNumber']} 的数据..........\r\n";

            $itemEmail = json_decode($item['emailDataJson'],true);

            foreach($itemEmail as $k=>$v){

                //获取人员guid
                $arrZentaoUser = $this->getZentaoUserByEmail($v['From'],$type);

                if(empty($arrZentaoUser)){

                    echo "--当前发件人{$v['From']} 获取不到用户信息 \r\n";continue;
                }

                //                $autoAssignedTo = $this->getAutoAssignedTo($arrZentaoUser,$type);



                $arrFiled = [
                    'project' =>(int)$objProject->id,
                    'is_itservice'=>1,
                    'push_guid'=>$arrZentaoUser->guid,
                    'push_dept'=>$arrZentaoUser->strParentDept,
                    'pushBy'=>$arrZentaoUser->account,
                    'estimate'=>0,
                    'left'=>0,
                    'story'=>0,
                    'pri' =>3,
                    'itType'=> $type == 1 ? 29 : 18,
                    /*       'estStarted'=>'0000-00-00',
                           'deadline'=>'0000-00-00',*/
                    'status'=>'wait',
                    'type'=>'design',
                    'autoAccount'=>$autoAssignedTo,
                    'openedBy'=>'itservice',
                    'openedDate'=>date('Y-m-d,H:i:s',time()),
                    'mailto'=>'kangjing',
                    'desc'=>$v['Body'] ? $this->mynl2br($v['Body'])  : '',
                    'name'=>$v['Subject'] ? $v['Subject'] : '无标题',
                    'adminDesc'=> $this->mynl2br($v['SubjectEN']."\r\n".$v['BodyEN']),

                ];

                //插入数据
                $id = $this->getOrm($type)->table('zt_task')->insertGetId($arrFiled);

                if($v['HasAttachments']==true){

                    $this->dealFile($id,$v['Attachments'],$type);


                }
                //推动app消息
                $this->pushMessage($id,$type,$arrZentaoUser);

                echo "--邮件 {$v['Subject']} 生成工单完毕 \r\n";
            }

            //更新邮箱最后执行时间
            $this->updateEmailTime($arrCurrentEmail);

            echo "--邮箱 {$item['accountNumber']} 处理完毕 \r\n";

        }

        echo "-----END------ \r\n";
    }
    /*
     * 根据虚拟账号获取项目
     */
    public function getProjectByAccount($type,$account){

        return $this->getOrm($type)->table('zt_project')->select('id')->where('code',$account)->first();
    }
    //更新邮件的最后执行时间
    public function updateEmailTime($arrCurrentEmail){

        //更新邮箱最后执行时间
        $upData = ['lastTime'=>$arrCurrentEmail->currentTime];

        $this->getOrm(1)->table('zt_emailtime')->where('id',$arrCurrentEmail->id)->update($upData);
    }
    /*
     * 获取任务自动流转虚拟账号
     */
    public function getAutoAssignedTo($user,$type)
    {

        //当前推送人部门
        $strDeptId = $user->dept;

        $arrDept = $this->getOrm($type)->table('zt_dept')->find($strDeptId);

        //所有父亲
        $arrPath = explode(',', trim($arrDept->path, ','));

        $arrParents = $this->getOrm($type)->table('zt_dept')->whereIn('id',$arrPath)->get()->toArray();

        $arrParentPath = array_map(function($val){
            return $val->guid;
        },$arrParents);

        //虚拟账号配置
        $arrAssignUser = config('zentao.account')[$type]['autoAssignedAccount'];

        $arrIndex = [];

        foreach ($arrAssignUser as $key => $value) {

            if (in_array($key, $arrParentPath)) {

                $keyIndex = array_search($key, $arrParentPath);

                $arrIndex[] = $keyIndex;
            }
        }
        $key = !empty($arrIndex) ? $arrParentPath[max($arrIndex)] : 'others';

        //最大值
        return $arrAssignUser[$key];


    }
    //处理文件
    public function dealFile($id,$arrAttach,$type){

        foreach($arrAttach as $key=>$value){

            $arrFile = $this->saveFile($value['Content'],$value['Name'],$value['Size'],$type);

            $arrFile['objectType'] = 'task';
            $arrFile['objectID']   = $id;
            $arrFile['addedBy']    = 'itservice';
            $arrFile['addedDate']  = date('Y-m-d',time());
            $arrFile['extra']      = '';
            $arrFile['is_finished'] = 5;

            $this->getOrm($type)->table('zt_file')->insert($arrFile);

        }


    }
    /*
     * 获取用户guid
     */
    public function getUserInfo($email){


        $url = config('zentao.url.getUserGuid');

        $token  = $this->getMsgToken();

        $result = $this->_poxy->sendHttp($url, 'post', [
            'headers' => [
                'Authorization' => ' Bearer '.$token,
            ],
            'form_params' => ['email'=>$email]
        ]);

        return $result;
    }

    public function getZentaoUserByEmail($email,$type){

        $objUserData = $this->getOrm($type)->table('zt_user')->where('email',$email)->first();




        if(!$objUserData){

            $userName = substr($email,0,strpos($email,'@'));

            $objUserData = $this->getOrm($type)->table('zt_user')->where('account',$userName)->first();

            if(!$objUserData){

                return [];
            }

        }

        //查询出当前人的所有父亲部门
        $objDeptData = $this->getOrm($type)->table('zt_dept')->where('id',$objUserData->dept)->first();



        $objUserData->strParentDept = '';

        if(!empty($objDeptData->path)){

            $arrDeptId = explode(',',trim($objDeptData->path,','));

            $arrParentDept = $this->getOrm($type)->table('zt_dept')->whereIn('id',$arrDeptId)->pluck('name')->toArray();

            $objUserData->strParentDept = implode('>',$arrParentDept);
        }


        return $objUserData;




    }
    //推送消息时获取token
    public function getMsgToken(){

        //获取token
        $getTokenUrl = config('zentao.url.getToken');

        $params = [
            'grant_type' => 'client_credentials',
            'client_id' => 'yc.zentaocrm',
            'client_secret' => 'secret',
            'scope' => 'StaffInfo'
        ];


        $tokenRes = $this->_poxy->sendHttp($getTokenUrl, 'post', [

            'form_params'=>$params
        ]);

        if (!$tokenRes['data']) {

            return false;
        }

        return $tokenRes['data']['access_token'];

    }
    /*
     * 获取Email 数据
     */
    public function getEmailData()
    {
        //从数据库中取邮箱
        $objEmail = $this->getOrm(1)->table('zt_emailtime')->get();

        $arrParam = [];

        //构建参数
        foreach($objEmail as $key=>$value){

            $currentTime = date('Y-m-d H:i:s',time());

            echo "--邮箱：{$value->email} 开始时间：{$value->lastTime} 结束时间：{$currentTime}\r\n";

            array_push($arrParam,[
                'AccountNumber' => $value->email,
                'Pwd'=> $value->password,
                'LastRunEmailBeginTime'=>$value->lastTime,
                'LastRunEmailEndTime'=>$currentTime,
//                'LastRunEmailBeginTime'=>'2019-11-13 14:00:01',
//                'LastRunEmailEndTime'=>'2019-11-13 14:40:01',

            ]);

            $value->currentTime = $currentTime;
        }

        $this->_emailInfo = $this->formatObjectByKey($objEmail,'email');

        echo '--emailInfo：'; print_r($this->_emailInfo);

        $url = config('zentao.url.getEmailData');

        echo '--请求数据：'; print_r($arrParam);

        $result = $this->_poxy->sendHttp($url, 'POST', [
            'headers' => [
                'Content-Type' => 'application/json;charset=utf-8',
            ],
            'json' => $arrParam
        ]);

        echo '--返回数据：';print_r($result);

        return $result;
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
     * 写入文件
     *
     */
    public function saveFile($binStr,$fileName,$size,$type){

        $dirRoot = config('zentao.account')[$type]['root'];

        $ext = pathinfo($fileName,PATHINFO_EXTENSION);

        $saveName = md5($fileName.uniqid());

        $pathName = date('Ymd/',time()).$saveName.'.'.$ext;

        $savePath = $dirRoot.$pathName;

        $this->checkFile($savePath);

        $fp = fopen($savePath, 'w');

        fwrite($fp, base64_decode($binStr));

        fclose($fp);

        return [
            'pathname'=>$pathName,
            'title'=>$fileName,
            'extension'=>$ext,
            'size'=>$size
        ];
    }


    //检验文件是否存在不存在就创建
    public function checkFile($pathname){

        $path = pathinfo($pathname)['dirname'];

        if (!file_exists($path)) {
            mkdir($path,0777,true);

        }
    }
    //推送app消息

    public function pushMessage($id,$type,$user){

        //获取任务
        $task = $this->getOrm($type)->table('zt_task')->where('id',$id)->first();

        $strContent =' 创建了';

        $strTaskName = mb_strlen($task->name, 'utf8') <= 10 ? $task->name : $task->name . '...';
        //操作人
        $currentUserName = $user->realname;

        $zentaoConfig = config('zentao.account')[$type];

//        $accountWithDept = $zentaoConfig['accountWithDept'];

        $arrData = [];
        $arrEmailData = [];
        $content = sprintf("%s %s一条工单 【%s】 '%s'",$currentUserName,$strContent,$id,$strTaskName);
        $arrFiled = [
            'productType' => 'itService',
            'messageType' => $zentaoConfig['messageType'],
            'owner' => $currentUserName,
            'title' => $task->name,
            'content' => $content,
            'sendTime' => date('Y-m-d,H:i:s',time()),

        ];
        $ownerType  = [1=>'IT 服务支持',2=>'PM 呼叫台'];
        //构造发送邮件的数据
        $arrEmailFiled = [
            'subject' => "【{$ownerType[$type]}】{$task->name} ",
            'body' => $content,
            'type' => 2,
            'user'=> [],
        ];

        //给调度人推送消息
//        $diaoDuuser = $accountWithDept[$task->autoAccount]['user'];

//        $arrUsers = explode(',',$diaoDuuser);
        $arrDiaodu = $this->getUserByAccount($type,$task->autoAccount);

        if(empty($arrDiaodu)) return false;

        foreach($arrDiaodu as $key=>$value){

            $arrUser = $this->getOrm($type)->table('zt_user')->where('account',$value->user)->first();


            $arrDiaodu = array_merge($arrFiled,[
                'userCode' => $arrUser->guid,
                'redirectUrl'=> $task->id,
            ]) ;

            array_push($arrData,$arrDiaodu);

            array_push($arrEmailFiled['user'],$arrUser->email);

        }


        $this->sendHttpMessage($arrData);

        if($type==1){

            $this->insertEmailInfo($arrEmailFiled,$type);
        }



    }

    /*
     * 插入邮件信息表
     */
    public function insertEmailInfo($data,$type){

        $data['user'] = implode(',',$data['user']);
        $data['zentaoType'] = $type;

        $this->getOrm($type)->table('zt_email_cron')->insert($data);

    }
    /*
     * 根据虚拟账号获取调度人
     */
    public function getUserByAccount($type,$account){

        $objUser = $this->getOrm($type)->table('zt_account_user')->select('user')->where('account',$account)->get();

        return $objUser;

    }

    //发送
    public function sendHttpMessage($data){

        //获取token

        $token = $this->getMsgToken();

        if(!$token){

            return false;
        }

        $url = config('zentao.url.pushMessageUrl');

        $this->_poxy->sendHttp($url, 'Post',[

            'headers' => [
                'Content-Type' => 'application/json;charset=utf-8',
                'Authorization' => 'Bearer '.$token
            ],
            'json' => $data
        ]);
    }

    public function mynl2br($text) {
        return strtr($text, array("\r\n" => '<br />', "\r" => '<br />', "\n" => '<br />'));
    }


}
