<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/8
 * Time: 下午2:38
 */
namespace App\Repository;
use App\Library\Traits\Functions;
use Illuminate\Http\Request;
use App\Exceptions\ApiException;
use App\Library\Proxy\TokenProxy;

class BaseRepository {

    use Functions;
    protected $request;
    protected $userInfo;
    protected $perpage;
    public function __construct(Request $request)
    {
        $this->request = $request;

        $this->userInfo = $this->getUserInfo();

        $this->perpage = config('common.perpage');
    }
    /*
     * 获取用户信息
     */
    public function getUserInfo(){
        return $this->request->get('userInfo');
    }
    /*
     * 根据guid获取用户信息
     */
    public function getUserByGuid($guid=''){
        $token = $this->getToken();
        $params = [];
        $params['headers'] = [
            'Authorization' => $token,
            'Accept' => 'application/json',
        ];
        if($guid){
            $params['form_params'] =[
                'Guid'=>$guid,
            ];
        }
        $proxy = new TokenProxy();
        $ssoUrl = config('common.apiUrl.checkToken');
        return  $proxy->sendHttp($ssoUrl, "GET", $params);
    }
    /*
     * 抛异常错误返回
     */
    public function setError( \Exception $e){
        if($e instanceof ApiException){
            $res = [
                'success' => false,
                'code' => 400,
                'message' => $e->getMessage()
            ];
        }else{
            $res = [
                'success' => false,
                'code' => 500,
            ];
            if(config('app.debug')){
                $res['message'] = [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ];
            }else{
                $res['message'] = 'Internal Error!';
            }

        }
        return $res;
    }
    /*
     * 处理分页信息
     */
    public function setPage($obj){

        $arr = ['current_page','data','per_page','total'];
        $res = [];
        foreach($obj as $key=>$value){
            if(in_array($key,$arr))$res[$key]=$value;
        }

        return $res;

    }
    /*
     * 处理字符串变成数组，方便入库
     */
    public function formatStrToArray($arrStr,$arrId){

        $res = array_map(function ($item) use ($arrId) {
            return [
                $arrId['filed_1'] => $arrId['id'],
                $arrId['filed_2'] => $item
            ];
        }, $arrStr);
        return $res;

    }
    /*
     * 给二维数组中添加指定的键 值
     */
    public function addDataToArray($arr,$key,$value){
        if(empty($arr)) return [];
        foreach ($arr as &$item){
            $item = array_add($item,$key,$value);
        }
        return  $arr;
    }
    /*
     * 获取authorization token
     */
    public function getToken(){
        $token = $this->request->header('authorization');
        return $token;
    }
    /*
     *获取二维数组中指定键的值
     */
    public function getKeyValue($arr,$key){
        if(empty($arr)) return [];
        $res = [];
        foreach($arr as $k=>$v){
            if(array_key_exists($key,$v)){
                array_push($res,$v[$key]);
            }else{
                continue;
            }
        }
        return $res;
    }
    /*
     * 获取二维对象的指定键值
     */
    public function getObjectKeyValue($obj,$key){
        if($obj->isEmpty()) return [];
        $obj = collect($obj);
        $res = $obj->map(function($item,$k) use($key){
            if(collect($item)->has($key)){
                return $item->{$key};
            }
        });
        return $res->all();
    }
    /*
     * 用对象中指定值作为key 返回
     */
    public function formatObjectByKey($obj,$key){
        if(!$obj) return [];
        $obj = collect($obj);
        $arrRes = [];
        foreach($obj as $k=>$item){
            if(collect($item)->has($key)){
                $arrRes[$item->$key] = $item;
            }
        }
        return $arrRes;
    }
    /*
     * 用二维数组中指定值作为key 返回
     */
    public function formatArrayByKey($arr,$key){
        if(empty($arr)) return [];
        $res = [];
        foreach($arr as $k=>$v){
            if(array_key_exists($key,$v)){
                $res[$v[$key]] = $v;
            }else{
                continue;
            }
        }
        return $res;
    }
    /*
     * 递归处理分类
     */
    public function getTreeNode($arr,$parentId=0){
        if(empty($arr)) return [];
        $res = [];
        foreach ( $arr as $key=> $item) {
            if($item->parent_id == $parentId){
                $children = $this->getTreeNode($arr,$item->id);
                if($children){
                    $item->children = $children ;
                }

                $res[] = $item;
            }
        }
        return $res;
    }
    /*
     * 拼图片路径
     */
    public function getRightUrl($url){

        if(!preg_match("/^(http|https):\/\/[A-Za-z0-9]+/i",$url)){

            $url = config('common.HOST').$url;
        }
        return $url;
    }
    /*
     * 获取文件类型
     */
    public function getResType($type){

        $allowType = config('common.upload.allowtype');

        foreach ($allowType as $key=>$value){

            if(in_array($type,$value)){

                $resouceType = $key;
                continue;

            }else{

                $resouceType = 1;
            }
        }
        return $resouceType;
    }
}