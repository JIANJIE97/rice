<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/15
 * Time: 17:39
 */

namespace app\api\model;






class Banner extends BaseModel
{
    //隐藏模型的属性
    protected $hidden =['delete_time','update_time','id'];

    //这个模型与BannerItem模型关联方法
    public function items(){
        return $this->hasMany('BannerItem','banner_id','id');
    }

    //模型层的业务方法，是被控制层调用的
    public static function getBannerByID($id){
            $banner = self::with(['items','items.images'])->find($id);
            return $banner;
    }
}