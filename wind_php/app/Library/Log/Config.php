<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2019-04-02
 * Time: 14:54
 */
namespace App\Library\Log;

define('WEB_ROOT_LOG',storage_path());

final class Config{

    static $channel ='wind';
    static $maxfile = 5;
    static $level ='debug';
    static $type = 'Daily';
    static $path = WEB_ROOT_LOG.'/logs/';

}