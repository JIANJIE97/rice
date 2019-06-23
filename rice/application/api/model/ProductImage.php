<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/24
 * Time: 18:28
 */

namespace app\api\model;


class ProductImage extends BaseModel
{

    //隐藏模型的属性
    protected $hidden=['img_id','delete_time','product_id'];

    //这个模型与Image模型关联方法
    public function imgUrl(){
        return $this->belongsTo('Image','img_id','id');
    }
}