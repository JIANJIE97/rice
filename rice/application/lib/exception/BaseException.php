<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/15
 * Time: 19:41
 */

namespace app\lib\exception;


use think\Exception;

class BaseException extends Exception
{
    /*
     * 返回异常信息的基类
     * */
//    状态码
    public $code = 400;
//    错误信息
    public $msg = "参数错误";
//    自定义错误码
    public $error_code = 10000;

    //构造函数获取数据
    public function __construct($parame = [])
    {
        if(!is_array($parame))
        {
            return ;
        }

        if(array_key_exists('code',$parame))
        {
            $this->code=$parame['code'];
        }

        if(array_key_exists('msg',$parame))
        {
            $this->msg=$parame['msg'];
        }

        if(array_key_exists('error_code',$parame))
        {
            $this->error_code=$parame['error_code'];
        }
    }
}