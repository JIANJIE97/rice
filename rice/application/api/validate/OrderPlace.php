<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/6
 * Time: 18:49
 */

namespace app\api\validate;


use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate
{
    //先验证提交过来的参数是否是数组以及是否为空（使用模型验证规则）
    //然后foreach获取参数中每一个子参数是否是正整数（自定义子验证规则）

    //定义模型验证规则
    protected $rule = [
        //调用自定义规则方法
        'products' => 'checkProducts'
    ];

    //定义子验证规则
    protected $singleRule = [
        'product_id' => 'require|isPositiveInteger',
        'count' => 'require|isPositiveInteger'
    ];

    //自定义验证参数列表函数（即验证参数是否为空或是否为数组）
    protected function checkProducts($values)
    {
        //判断提交的参数是否为数组
        if (!is_array($values)) {
            throw new ParameterException([
                'msg' => '商品参数不正确'
            ]);
        }

        //判断提交的参数不能为空
        if (empty($values)) {
            throw new ParameterException([
                'msg' => '商品列表不能为空'
            ]);
        }

        //用foreach来使用子参数验证
        foreach ($values as $value) {
            $this->checkProduct($value);
        }

        return true;
    }

    //自定义子验证参数函数（即验证子参数中商品id和订单商品数量是否为正整数）
    protected function checkProduct($value)
    {
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if (!$result) {
            throw new ParameterException([
                'msg' => '商品列表参数错误'
            ]);
        }
    }
}