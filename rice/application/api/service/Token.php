<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/24
 * Time: 15:11
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{

    //生成token令牌
    public static function generateToken()
    {
        //32个字符组成一组随机字符串
        $randChars = getRandChars(32);
        //使用3组字符串使用md5加密,第一个为随机字符，第二个为时间戳，第三个为Token盐
        $timestame = $_SERVER['REQUEST_TIME_FLOAT'];
        $salt = config('secure.token_salt');
        return md5($randChars . $timestame . $salt);

    }

    //面向对象的方式封装获取UID方法：
    //在service模型里编写
    //getCurrentTokenVar(key):根据key来获取不同$value的值
    //getCurrentUID:方法里面调用getCurrentTokenVar('id')
    //1、先通过http的header里面获取Token
    //2、根据token获取缓存里面的value
    //3、判断value存不存在，不存在抛出异常
    //4、存在的话，再判断value是不是数组格式，不是的话要解码为数组格式json_decode($vars,true)
    //5、判断传进来的Key是不是数组Value中存在的key,是的话返回vars[key]，否则抛出异常。

    //根据key来获取不同$value的值
    //通过key = token获取缓存中$value
    //再通过$value[uid]获取缓存中的uid
    public static function getCurrentTokenVar($key)
    {
        $token = Request::instance()->header('token');
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }

    }

    //通过key为uid获取缓存中的uid的值
    public static function getCurrentUID()
    {
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }


    //需要用户和管理员权限都可以访问的权限
    public static function needPrimaryScop()
    {
        //根据token获取缓存中scope权限数值
        $scope = self::getCurrentTokenVar('scope');

        //判断scope是否存在,存在就继续判断权限是否足够，否则就抛出token异常
        if ($scope) {

            //判断scope是否大于或等于16，大于可以访问接口函数,否则抛出异常
            if ($scope >= ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    //只有用户才可以访问的权限
    public static function needExclusiveScope()
    {
        //根据token获取缓存中scope权限数值
        $scope = self::getCurrentTokenVar('scope');

        //判断scope是否存在,存在就继续判断权限是否足够，否则就抛出token异常
        if ($scope) {

            //判断scope是否等于16，等于就可以访问接口函数,否则抛出异常
            if ($scope == ScopeEnum::User) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    //检测是否是合法的用户
    public static function isValidOperate($checkedUID)
    {
        if (!$checkedUID) {
            throw new Exception("检测UID时必须传入一个被检查的UID");
        }
        //获取当前操作的UID
        $currentOperateUID = self::getCurrentUID();
        if ($currentOperateUID == $checkedUID) {
            return true;
        }
        return false;
    }

    //检测token是否有效
    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if ($exist) {
            return true;
        } else {
            return false;
        }
    }

}