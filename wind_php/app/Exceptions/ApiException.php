<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/24
 * Time: 10:55 AM
 */
namespace App\Exceptions;

use Throwable;

class ApiException extends \Exception{

    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}