<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/29
 * Time: 13:40
 */

namespace app\api\controller\v2;




class Banner
{
    /*
     * 获取指定id号的banner信息
     * @url=/banner/:id
     * @http GET
     * @id=banner的id号
     * */

    public function getBanner($id){

        return "这是测试版本号的";
    }
}