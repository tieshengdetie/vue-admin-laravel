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

class DeleteZentaoData extends Command{


    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'DeleteZentaoData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除已经处理过的禅道数据';

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
        echo "--开始删除数据--\r\n";

        $num = DB::table('zentao_sync_data')->where('status',1)->delete();

        echo "--本次删除 ({$num}) 条数据--\r\n";
    }
}