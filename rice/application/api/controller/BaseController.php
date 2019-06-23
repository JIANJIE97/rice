<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/6
 * Time: 18:01
 */

namespace app\api\controller;


use app\api\service\Token as TokenService;
use think\Controller;

class BaseController extends Controller
{
    //调用TokenService的权限控制函数
    //需要用户或者管理员权限才可以访问
    public function checkPrimaryScope(){
        TokenService::needPrimaryScop();
    }

    //只有用户权限才可以访问
    public function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }
}