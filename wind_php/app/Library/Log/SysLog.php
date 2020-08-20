<?php

namespace App\Library\Log;

use Monolog\Logger as MonologLogger;

use Monolog\Handler\{RotatingFileHandler,ErrorLogHandler,StreamHandler,SyslogHandler};

use Monolog\Processor\{WebProcessor,PsrLogMessageProcessor};


use Monolog\Formatter\{LineFormatter,NormalizerFormatter,HtmlFormatter,JsonFormatter};



/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-04-02
 * Time: 14:53
 */
class SysLog {

    /**
     * The Log levels.
     *
     * @var array
     */
    protected $levels = [
        'debug'     => MonologLogger::DEBUG,
        'info'      => MonologLogger::INFO,
        'notice'    => MonologLogger::NOTICE,
        'warning'   => MonologLogger::WARNING,
        'error'     => MonologLogger::ERROR,
        'critical'  => MonologLogger::CRITICAL,
        'alert'     => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];
    /**
     * The Monolog logger instance.
     *
     * @var \Monolog\Logger
     */
    protected $monolog;
    /*
     *  filename of log
     */
    protected $filename;
    /*
     * api type
     */
    protected $arrApiTypeName = ['request','response','common','email','hb','data'];
    /*
     * create a new monolog instance
     *
     */
    public function __construct()
    {
        $this->monolog = new MonologLogger(Config::$channel);

    }
    public function addLog($content,$apiType){

        $apiTypeName = in_array($apiType ,$this->arrApiTypeName) ? $apiType : $this->arrApiTypeName[3];

        $this->filename = sprintf('%s%s/%s.log', Config::$path, 'api',$apiTypeName);

        $this->{'create'.Config::$type.'Log'}();

        $this->monolog->info($content);

    }

    /*
     * crate a daily log
     */
    private function createDailyLog($level='debug'){

        $handler = new RotatingFileHandler($this->filename, Config::$maxfile, $this->parseLevel($level));

        $handler->setFormatter($this->getDefaultFormatter());

        $this->monolog->pushHandler($handler);

        $this->monolog->pushProcessor(new WebProcessor());


    }
    /*
     * create a single log
     */
    public function createSingleLog(){

    }
    public function createSysLog(){

    }
    public function createErrorLog(){

    }
    /*
     * format the log info
     */
    public function getDefaultFormatter(){
        // the default date format is "Y-m-d H:i:s"
//        $dateFormat = "Y n j, g:i a";
        $dateFormat = "Y-m-d H:i:s";
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
//        $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
        $output = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n\n";
        // finally, create a formatter
        $formatter = new LineFormatter($output, $dateFormat,true,true);
//        $formatter = new JsonFormatter(1);
        return $formatter;
    }
    /*
     * return monolog log level
     */
    protected function parseLevel($level)
    {
       return  $this->levels[$level];
    }
    /*
     * return monolog instance
     */
    public  function getMonolog(){
        return $this->monolog;
    }

}