<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/21
 * Time: 16:05
 */

namespace app\api\model;


class Product extends BaseModel
{
    //隐藏一点的属性
    protected $hidden =['delete_time','update_time','delete_time','category_id','create_time','pivot','from'];


    //创建与商品详情图片模型的关联关系
    public function imgs(){
        return $this->hasMany('ProductImage','product_id','id');
    }

    //创建与商品详情信息模型的关联关系
    public function properties(){
        return $this->hasMany('ProductProperty','product_id','id');
    }

    //读取器：获取模型中对应属性的值，data是一个对象
    public function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }

    //模型层的业务方法，是被控制层调用的
    //根据分类id查询查询对应的商品
    public static function getProductByCategoryID($categoryID){
        $product=self::where('category_id','=',$categoryID)->select();
        return $product;
    }



    //模型层的业务方法，是被控制层调用的
    public static function getProductDetail($id){
        //查询商品详情页面数据，图片安装order属性排列
        $product = self::with([
            'imgs'=>function($query){
            $query->with(['imgUrl'])->order('order', 'asc');
            }
        ])->with(['properties'])->find($id);
        return $product;
    }
}