<?php

namespace app\api\model;

use think\Model;

class BaseModel extends Model
{
    /*
     * 模型的基类
     * */

    //使用自定义配置文件把数据库中的部分url与url前面的部分连接，
    //这个方法应该写到对应的模型类中，但是控制器多次调用，所以把这个函数抽象到基类
    public function prefixImgUrl($value,$data){
        $findUrl=$value;
        if($data['from']==1){
            //拼凑url路径
            $findUrl = config('setting.img_prefix').$value;
        }
        return $findUrl;
    }
}
