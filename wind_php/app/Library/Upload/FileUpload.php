<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/11
 * Time: 下午12:00
 */

namespace App\Library\Upload;

use Illuminate\Support\Facades\DB;
use App\Library\Proxy\TokenProxy;
use App\Exceptions\ApiException;
class FileUpload
{

    private $_filename;
    private $_request;
    private $_isOk;
    private $_isAllow;
    private $_isVideo;
    private $_extension;
    private $_objFile;
    private $_md5;
    private $_originalName;
    private $_table;

    public function __construct($request)
    {
        $this->_request = $request;
    }
    /*
     * 初始化属性
     */
    public function init(){
        $this->_filename = 'resource';
        $this->_isOk = false;
        $this->_isAllow = false;
        $this->_isVideo =false;
        $this->_extension = '';
        $this->_md5 = '';
        $this->_originalName = '';
        $this->_objFile = '';
    }
    public function setTable($table){
        $this->_table = $table;
        return $this;
    }
    /*
     * 资源上传
     */
    public function uploadResource($filename, $is_resource = true, $type = 0)
    {
        $this->init();
        $this->_filename = $filename;
        //检测文件
        $this->checkFile();
        if (!$this->_isOk) {
            throw new ApiException('上传的资源不存在');
        }
        //检测上传文件类型是否允许
        $this->checkFileType($type);
        if (!$this->_isAllow) {
            throw new ApiException('上传的资源类型与所选类型不一致或不被允许');
        }

        //检测md5
        if ($is_resource) {
            $checkRes = $this->checkFileMd5();
        }else{
            $checkRes = $this->checkImageMd5();
        }
        //检测了不存在或者不需要检测都要上传
        if ($checkRes) {

            return $checkRes;
        }
        $res = $this->moveUpload();
        //如果是视频的话上传到保利威
        if($this->_isVideo===true){
            $strBase = public_path().DIRECTORY_SEPARATOR.$res['url'];
            $resPolyv = $this->moveVideoToPolyv($strBase);
            $res['path'] = $resPolyv['data']['data'][0]['vid'];
            if($resPolyv['data']['error']!=0){
                throw new ApiException("上传失败");
            }
        }

        return $res;

    }

    //检测文件是否存在
    private function checkFile()
    {

        if ($this->_request->hasFile($this->_filename) && $this->_request->file($this->_filename)->isValid()) {
            $this->_isOk = true;
            $this->_objFile = $this->_request->file($this->_filename);
            $this->_originalName = $this->_objFile->getClientOriginalName();
        }
    }

    //检测文件类型
    private function checkFileType($type)
    {
        $this->_extension = $this->_objFile->getClientOriginalExtension();

        $checkType = $type == 0 ? (int)$this->_request->get('type') : $type;
        
        $allowType = config('common.upload.allowtype')[$checkType];
        if (in_array($this->_extension, $allowType)) {
            $this->_isAllow = true;
            if($checkType==1){
                $this->_isVideo = true;
            }
        }
    }

    //上传
    private function moveUpload()
    {
        $dir = $this->_extension . DIRECTORY_SEPARATOR . date("Ymd");
        $size = $this->_objFile->getClientSize();

        $url = $this->_objFile->store($dir);
        return [
            'url' => 'uploads'.DIRECTORY_SEPARATOR . $url,
            'type' => $this->_extension,
            'size' => $size,
            'md5' => $this->_md5,
            'original_name' => $this->_originalName,
            'path'=>''
        ];

    }
    /*
     * 上传视频到保利威
     * Guzzle 方式
     */
    private function moveVideoToPolyv($file){
        $poxy = new TokenProxy();
        $config = config('common.upload.polyv');
        $title = $this->_originalName;
        $tag =$desc ='';
        $jsonrpc ='{"title":"' . $title . '","tag":"' . $tag . '","desc":"' . $desc . '"}';
        $param = [
            'multipart' => [
                [
                    'name'     => 'writetoken',
                    'contents' => $config['writetoken']
                ],
                [
                    'name'     => 'JSONRPC',
                    'contents' => $jsonrpc
                ],
                [
                    'name'     => 'cataid',
                    'contents' => $config['cataid']
                ],
                [
                    'name'     => 'Filedata',
                    'contents' => fopen($file,'r')
                ],
            ]
        ];
        return $poxy->sendHttp($config['url'],'post',$param);
    }
    /*
     * 获取polyv视频信息
     */
    public function getPolyvVideoInfo($vid){
        $poxy = new TokenProxy();
        $config = config('common.upload.polyv');
        $url = $config['getInfoUrl'].$config['userid'].'/get-video-msg';
        $format ='json';
        $secretkey = $config['secretkey'];
        $ptime = time() * 1000;
        $str="format=".$format."&ptime=".$ptime."&vid=".$vid.$secretkey;
        $sign = strtoupper(sha1($str));
        $param = [
            'form_params' => [
                'format'=>$format,
                'vid'=>$vid,
                'ptime'=>$ptime,
                'sign'=> $sign,

            ]
        ];
        $res = $poxy->sendHttp($url,'post',$param);
        if($res['data']['code']==200&&$res['data']['data']){
            $videoInfo = $res['data']['data'][0];
            $videoType = substr($videoInfo['mp4'],strripos($videoInfo['mp4'],'.')+1);
            $upResourceRes['type'] = $videoType;
            $upResourceRes['size'] = $videoInfo['source_filesize'];
            $upResourceRes['url'] =$videoInfo['mp4'];
            $upResourceRes['md5']= $videoInfo['md5checksum'];
            $upResourceRes['path'] = $vid;
            $upResourceRes['original_name'] = $videoInfo['title'];
            return $upResourceRes;
        }else{
            return [];
        }


    }
    /*
     * curl方式上传
     */
    function uploadfile($title,$desc,$tag,$cataid,$filename) {
        $JSONRPC = '{"title":"'.$title.'","tag":"'.$tag.'","desc":"'.$desc.'"}';
        $config = config('common.upload.polyv');
        $writetoken = $config['writetoken'];
        $secretkey = $config['secretkey'];
        $hash = sha1('cataid='.$cataid.'&JSONRPC='.$JSONRPC.'&writetoken='.$writetoken.$secretkey);
        //加上sign参数一起提交post
        $data = array(
            'JSONRPC' => $JSONRPC,
            'cataid'=>$cataid,
            'writetoken'=>$writetoken,
//            'sign'=>$hash,
            'Filedata'=>new \CURLFile(realpath($filename))
        );
        $uri = "http://v.polyv.net/uc/services/rest?method=uploadfile";
        $ch = curl_init() or die ( curl_error() );
        curl_setopt( $ch, CURLOPT_URL, $uri);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 360);
        $reponse = curl_exec ( $ch );
        curl_close ( $ch );
        $reponse = json_decode($reponse);
        return $reponse;
    }

    //检测该文件是否已经上传过
    private function checkFileMd5()
    {
        $this->_md5 = $this->getFileMd5();
        //查看数据库中是否存在
        $fileData = DB::table($this->_table)->where('md5', $this->_md5)->first();
        if ($fileData) {
            return [
                'url' => $fileData->url,
                'type' => $fileData->type,
                'size' => $fileData->size,
                'md5' => $fileData->md5,
                'original_name' => $fileData->original_name,
                'path'=>$fileData->path,
            ];
        } else {
            return [];
        }
    }
    /*
     * 检测图片是否上传过
     */
    private function checkImageMd5(){
        $this->_md5 = $this->getFileMd5();
        //查看数据库中是否存在
        $fileData = DB::table($this->_table)->where('face_image_md5', $this->_md5)->first();
        if ($fileData) {
            return [
                'url' => $fileData->face_image,
                'type' => $this->_extension,
                'size' => $this->_objFile->getClientSize(),
                'md5' => $this->_md5,
                'original_name' => $this->_originalName,
                'path'=>'',
            ];
        } else {
            return [];
        }
    }

    //获取该文件的MD5
    private function getFileMd5()
    {
        return md5_file($this->_objFile->path());

    }

}
