<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 21:25
 */

namespace app\api\controller\V1;
use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;


class Category
{
    /*
     * @url=/category/all
     * @http=GET
     * */

    //获取所有类目信息
    public function getAllCategories(){
        $categorys = CategoryModel::getCategoryMain();
        if($categorys->isEmpty()){
            throw new CategoryException();
        }
        return $categorys;
    }
}