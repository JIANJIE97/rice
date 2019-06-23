<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25
 * Time: 18:58
 */

namespace app\api\model;


class UserAddress extends BaseModel
{
    //隐藏模型属性
    protected $hidden =['delete_time','id','user_id'];
}