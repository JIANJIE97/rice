<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/22
 * Time: 19:41
 */

namespace app\api\controller\V1;


use app\api\validate\Count;
use app\api\model\Product as ProductModel;
use app\api\validate\IDMustBePostiveInt;
use app\lib\exception\ProductException;

class Product
{
    /*
     * @url=/product/by_category?id=
     * @http=GET
     * */


    public function getAllInCategory($id){
        (new IDMustBePostiveInt())->goCheck();
        $products=ProductModel::getProductByCategoryID($id);
        if ($products->isEmpty()){
            throw new ProductException();
        }
        $products=$products->hidden(['summary']);
        return$products;
    }

    //url=/product/2
    public function getOne($id){
        (new IDMustBePostiveInt())->goCheck();
        $product = ProductModel::getProductDetail($id);
        if(!$product){
            throw new ProductException();
        }
        return $product;
    }
}