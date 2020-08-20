<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-05-21
 * Time: 16:39
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Library\SendMail\SendMail;

class SyncSendEmail extends Command{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SyncSendEmail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '异步发送工单邮件提醒';

    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        echo "--Start--\r\n";

        $objEmail  = DB::connection("mysql_zentao")->table('zt_email_cron')->where('status',0)->get();

        $objServer  = new SendMail();

        if(!$objEmail->isEmpty()){

            foreach ($objEmail as $item){

                echo "-- 开始发送数据 Id :({$item->id}) \r\n Body : {$item->body} \r\n 收件人：{$item->user}--\r\n";

                $strUser = $item->user;

                $item->user = explode(',',$item->user);

                $data = get_object_vars($item);

                $isSuccess = $objServer->send($data);

                if(!$isSuccess){

                    echo "--Id :{$item->id}  收件人：{$strUser} 的数据发送失败\r\n";

                    $time = date("Y-m-d,H:i:s");

                    echo "--Time:{$time}\r\n";

                    continue;
                }else{

                    //更改状态
                    DB::connection("mysql_zentao")->table('zt_email_cron')->where('id',$item->id)->update(['status'=>1,'send_time'=>date("Y-m-d,H:i:s")]);

                    echo "--发送成功\r\n";

                    $time = date("Y-m-d,H:i:s");

                    echo "--Time:{$time}\r\n";
                }

            }
        }else{

            echo "--没有可处理的数据\r\n";
        }



        echo "--END--\r\n";
    }
}