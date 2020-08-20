<?php
/**
 * Created by PhpStorm.
 * User: zhaojinsheng
 * Date: 2018/10/8
 * Time: 上午10:17
 */


namespace App\Http\Controllers;

use App\Library\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Library\Traits\Functions;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{

    use ApiResponse,Functions;

    public $request;


    public function __construct(Request $request)
    {

        $this->request = $request;


    }
    public function validateParam($arr){

        list($rule,$message,$attribute) = $arr;

        //验证表单
        $validator = Validator::make($this->request->all(),$rule,$message,$attribute);

        if ($validator->fails()) {

            $msg = $this->formatValidatorError($validator->errors());

            return $msg ;

        }

        return true;


    }
    public function formatValidatorError($error){

        $arr = $error->toArray();
        $res = [];
        foreach($arr as $key=>$item){
            if($key=='captcha'){
                $item[0] = "验证码错误";
            }
            array_push($res,$item[0]);
        }

        return current($res);
    }

}