<?php
/**
 * Desc: 加密和验证密码
 * User: Zhaojinsheng
 * Date: 2020/8/7
 * Time: 15:24
 * Filename:PasswordHash.php
 */

namespace App\Library\Password;

final class PasswordHash
{


    public static function createPassword($str)
    {

        return password_hash($str, PASSWORD_DEFAULT);

    }

    public static function verifyPassword($str,$password)
    {


        return password_verify($str,$password);


    }

}