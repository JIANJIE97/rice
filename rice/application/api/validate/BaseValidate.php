<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 17:56
 */

namespace app\api\validate;


use app\lib\exception\ParameterException;
use think\Request;
use think\Validate;

class BaseValidate extends Validate
{

    //验证器的业务方法，即调用这个函数就是对参数进行验证了
    public function goCheck()
    {

        //1、获取前端传来的参数
        $param = Request::instance()->param();
        //2、对参数进行校验
        $result = $this->batch()->check($param);

        if (!$result) {
            $e = new ParameterException([
                'msg' => $this->error
            ]);
            throw $e;
        } else {
            return true;
        }


    }

    //把判断是否为正整数的业务抽象到基类，因为其他验证层有使用到。但是是保护类型所以抽象到基类
    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        } else {
            return false;
        }
    }

    //判断参数是否为空的自定义验证规则
    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    //判断参数是否为手机号码的自定义验证规则
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * 1、参数过滤，只允许获取通过rule校验的指定数据
        在BaseValidate 里定义getDateByRule（$arrays）方法过滤，$arrays为客户端传过来的所有参数；
        1.1、先判断传过来的参数中是否存在user_id或者uid这两个参数，存在就抛出异常
        1.2、不存在就定义空的的关联数组，使用foreach导入$this->rule as $key =>$value
        1.3、返回新的参数数组
       2、验证器调用参数过滤函数（input（'post.'））
     * */

    //参数过滤，只允许获取通过rule校验的指定数据，在地址创建时调用，客户端传输地址信息不可以传输user_id参数，太危险
    public function getDateByRule($arrays)
    {
        if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
            throw new ParameterException([
                'msg' => '参数中包含非法的参数名user_id或者uid'
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }

        return $newArray;
    }


}