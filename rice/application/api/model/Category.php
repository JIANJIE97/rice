<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 21:24
 */

namespace app\api\model;


class Category extends BaseModel
{
    //隐藏模型的属性
    protected $hidden=['delete_time','update_time','topic_img_id'];

    //这个模型与Image模型关联方法
    public function img(){
        return $this->belongsTo('Image','topic_img_id','id');
    }

    //模型层的业务方法，是被控制层调用的，获取类目主要信息
    public static function getCategoryMain(){
        return self::all([],'img');
    }
}