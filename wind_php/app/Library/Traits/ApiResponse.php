<?php
/**
 * Created by PhpStorm.
 * User: admin<admin@yixia.com>
 * Date: 2018/10/8
 * Time: 上午9:51
 */



namespace App\Library\Traits;

use Symfony\Component\HttpFoundation\Response as Foundationresponse;
use \Response;

trait ApiResponse
{

    /**
     * @var int
     */
    protected $statusCode = Foundationresponse::HTTP_OK;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }


    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    public function respond($data, $header = [])
    {

        return Response::json($data,$data['code'],$header);
    }

    /**
     * @param $status
     * @param array $data
     * @param null $code
     * @return mixed
     */
    public function status($status, array $data,$code=Foundationresponse::HTTP_OK){

        if ($this->statusCode!=Foundationresponse::HTTP_OK){
            $code = $this->statusCode;
        }
        $status = [
            'status' => $status,
            'code' => $code
        ];

        $data = array_merge($status,$data);
        return $this->respond($data);

    }

    /**
     * @param $message
     * @param int $code
     * @param string $status
     * @return mixed
     */
    public function failed($message, $code= Foundationresponse::HTTP_BAD_REQUEST, $status = "error"){

        if(!config('app.debug')&&$code==500){
            $message = 'Internal Error!';
        }
        return $this->status($status,[
            'msg' => $message
        ],$code);
    }


    /**
     * @param $message
     * @param string $status
     * @return mixed
     */
    public function message($message, $status = "success"){

        return $this->status($status,[
            'msg' => $message
        ]);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function internalError($message = "Internal Error!"){

        return $this->setStatusCode(Foundationresponse::HTTP_INTERNAL_SERVER_ERROR)
            ->failed($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function created($message = "created")
    {
        return $this->setStatusCode(Foundationresponse::HTTP_CREATED)
            ->message($message);

    }

    /**
     * @param $data
     * @param string $status
     * @return mixed
     */
    public function success($data='', $status = "success"){

        return $this->status($status,compact('data'));
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function notFond($message = 'Not Fond!')
    {
        return $this->setStatusCode(Foundationresponse::HTTP_NOT_FOUND)->failed($message);
    }


}