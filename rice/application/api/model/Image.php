<?php

namespace app\api\model;

class Image extends BaseModel
{
    //隐藏模型的属性
    protected $hidden = ['id','from','delete_time','update_time'];

    //当读取url这个属性时就会调用getUrlAttr这个方法
    //读取器：读取数据库中的这个模型表的url属性值
    public function getUrlAttr($value,$data){
        //读取器return值会显示出要读取的值，例如getUrlAttr会显示url这个值
        return $this->prefixImgUrl($value,$data);
    }
}
