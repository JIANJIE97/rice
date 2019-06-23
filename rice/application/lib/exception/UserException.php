<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25
 * Time: 19:18
 */

namespace app\lib\exception;


class UserException extends BaseException
{
    public $code = 404;
    public $msg = "用户不存在";
    public $error_code = 60000;
}