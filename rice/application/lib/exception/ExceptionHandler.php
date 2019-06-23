<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/15
 * Time: 19:40
 */

namespace app\lib\exception;


use think\Config;
use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle
{
    /*
     * 全局异常的处理函数
     * */
    private $code;
    private $msg;
    private $error_code;
    //url
    public function render(\Exception $e)
    {
        //$e是属于BaseException这个类的情况下是自定义异常
        if ($e instanceof BaseException){
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->error_code = $e->error_code;
        }
        else{
            //$e是服务器异常的话，分是调试情况还是生产环境，
            //调试测试返回具体信息调用父类，生产的话返回json格式的错误信息
            if(Config::get('app_debug'))
            {
                //调试情况
                return parent::render($e);
            }
            else
            {
                //生产环境
                $this->code = 500;
                $this->msg = "服务器内部错误";
                $this->error_code = 999;

                //记录日志
                $this->recordExceptionLog($e);
            }
        }
        //返回结果
        $result = [
            "msg" =>$this->msg,
            "error_code" =>$this->error_code,
            "request_url" => Request::instance()->url()
        ];
        return json($result,$this->code);
    }

    //启用记录日志
    public function recordExceptionLog(\Exception $e){
        //日志的初始化
        log::init([
            'type'=>'File',
            'path'=>LOG_PATH,
            'level'=>['error']
        ]);
        Log::record($e->getMessage(),"error");
    }
}