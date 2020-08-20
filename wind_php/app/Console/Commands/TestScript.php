<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-06-19
 * Time: 14:49
 */

namespace App\Console\Commands;

use App\Library\SendMail\SendMail;
use Illuminate\Console\Command;
use App\Library\Traits\Functions;

class TestScript extends Command
{
    use Functions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'TestScript';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试用的脚本';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    private $_time;



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
        $data =
            [
                'subject'=>'测试发送邮件',
                'body'=>'测试测试测试测试',
                'user'=>['zhaojinsheng@yuechenggroup.com','huochangsheng@yuechenggroup.com'],
            ];

        $objEmail = new SendMail(true);

        $objEmail->send($data);



    }






}
