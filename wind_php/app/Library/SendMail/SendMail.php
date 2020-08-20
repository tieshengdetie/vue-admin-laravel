<?php
/**
 * Created by PhpStorm.
 * User: zhaojinsheng<123844231@qq.com>
 * Date: 2019-12-11
 * Time: 10:56
 */

/**
 * Created by PhpStorm.
 * User: zhaojinsheng<123844231@qq.com>
 * Date: 2019-12-06
 * Time: 08:59
 */

namespace App\Library\SendMail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class SendMail
{
    //邮件对象
    public $mail;

    public $log;

    public $is_debug;

    public $env;

    public $config ;


    public function __construct($is_debug = false)
    {

        $this->mail = new PHPMailer(true);

        $this->is_debug = $is_debug ? 2 : 0;

        $this->env = "dev";

        $this->config = config('common.sendEmail');


    }

    public function send($arrEmailData)
    {

        app('log')->debug("参数：" . print_r($arrEmailData, true), ['time' => date("Y-m-d,H:i:s")]);

        $this->clear();

        try {

            $this->setEmailServer();

            $this->setFrom();


            $this->mail->Subject = $arrEmailData['subject'];
            $this->mail->Body = $arrEmailData['body'];

            $this->setAddress($arrEmailData['user']);

            $this->mail->send();


            app('log')->debug("发送完毕！！！", ['time' => date("Y-m-d,H:i:s")]);

            return true;


        } catch (Exception $e) {

            $error = "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";

            app('log')->debug($error, ['time' => date("Y-m-d,H:i:s")]);

            return false;

        }

        $this->mail->smtpClose();
    }

    public function clear()
    {
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();
    }

    public function setEmailServer()
    {

        //Server settings
        $this->mail->SMTPDebug = $this->is_debug;
        $this->mail->isSMTP();
        //正式地址
        $this->mail->Host = $this->config['host'];
        $this->mail->Username = $this->config['username'];
        $this->mail->Password = $this->config['password'];

        $this->mail->SMTPAuth = true;

        $this->mail->CharSet = "utf-8"; //utf-8;
        $this->mail->Encoding = "base64";
        //        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mail->Port = 25;
        $this->mail->isHTML(true);
    }

    public function setFrom($from = '')
    {
        $userFrom = $from ? $from : $this->config['from'];
        //        $userFrom = $from ? $from : 'zhangshipeng@yuechenggroup.com';



        echo "--发件账号 : {$userFrom}\r\n";

        $this->mail->setFrom($userFrom, 'Mailer');
    }

    public function setAddress($arrTo)
    {


        foreach ($arrTo as $value) {

            if ($this->env == 'dev' && in_array($value, ['nihaohua@yuechenggroup.com']))
                continue;

            $this->mail->addAddress($value);
        }
    }

}