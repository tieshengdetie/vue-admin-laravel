<?php

/*
 * This file is part of jwt-auth.
 *
 * (c) Sean Tymon <tymon148@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [


    'key' => md5('tianyun'),
    'iss' => '',
    'aud' => '',
    'lat' => time(),    //签发时间
    'nbf' => time(),    //生效时间
    'exp' => time() + 24* 3600  //过期时间24小时

];
