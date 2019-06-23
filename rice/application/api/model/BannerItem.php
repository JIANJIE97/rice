<?php

namespace app\api\model;

class BannerItem extends BaseModel
{
    //隐藏模型的属性
    protected $hidden=['id','img_id','banner_id','delete_time','update_time'];

    //这个模型与Image模型关联方法
    public function images(){
        return $this->belongsTo('Image','img_id','id');
    }
}
