<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/14
 * Time: 16:54
 */

namespace app\api\validate;


class IDMustBePostiveInt extends BaseValidate
{
    //验证规则
    protected $rule =[
        'id'=>'require|isPositiveInteger',
    ];

    //报错信息提示
    protected $message=[
        'id'=>'id必须是正整数'
    ];


}