<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/29
 * Time: 13:40
 */

namespace app\api\controller\v1;


use app\api\model\Banner as BannerModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\BannerMissException;


class Banner
{
    /*
     * 获取指定id号的banner信息
     * @url=/banner/:id
     * @http GET
     * @id=banner的id号
     * */

    public function getBanner($id){
        //参数验证，验证$id必须是正正数，不是就会返回错误信息
        (new IDMustBePostiveInt())->goCheck();

        //调用模型层的函数，通过$id号获取banner信息
        $banner = BannerModel::getBannerByID($id);

        //判断banner信息是否存在，不存在抛出异常
        if(!$banner){
            throw new BannerMissException();
        }
        return $banner;
    }
}