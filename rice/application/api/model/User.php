<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/23
 * Time: 16:17
 */

namespace app\api\model;


class User extends BaseModel
{
    //关联UserAddress的关联方法
    public function address(){
        return $this->hasOne('UserAddress','user_id','id');
    }

    //模型层的业务方法，是被控制层调用的
    //通过OpenID获取User信息
    public static function getByOpenID($openid){
        $user= self::where('openid','=',$openid)->find();
        return $user;
    }
}