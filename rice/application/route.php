<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

//banner接口
Route::get("api/:version/banner/:id", "api/:version.Banner/getBanner");

//类目接口
Route::get("api/:version/category/all", "api/:version.Category/getAllCategories");

//路由分组
Route::group("api/:version/product", function () {
    //根据分类id查询对应商品接口
    Route::get("/by_category", "api/:version.Product/getAllInCategory");
    //根据id查询对应商品详细信息接口
    Route::get("/:id", "api/:version.Product/getOne", [], ['id' => '\d+']);
});

//token令牌接口
Route::post("api/:version/token/user", "api/:version.Token/getToken");
Route::post("api/:version/token/verify", "api/:version.Token/verifyToken");
Route::post("api/:version/token/app", "api/:version.Token/getAppToken");

//地址保存接口
Route::post("api/:version/address", "api/:version.Address/createOrUpdateAddress");
Route::get("api/:version/address", "api/:version.Address/getUserAddress");

//订单接口
Route::post("api/:version/order", "api/:version.Order/placeOrder");
//根据user获得历史订单信息接口
Route::get("api/:version/order/by_user", "api/:version.Order/getSummaryByUser");
//获取历史订单详情接口
Route::get("api/:version/order/:id", "api/:version.Order/getDetail", [], ['id' => '\d+']);
//cms获取全部订单接口
Route::get("api/:version/order/paginate", "api/:version.Order/getSummary");
//发送微信模板消息接口
Route::put("api/:version/order/delivery", "api/:version.Order/delivery");


//支付接口
Route::post("api/:version/pay/pre_order", "api/:version.Pay/getPreOrder");

//微信支付结果回调接口
Route::post("api/:version/pay/notify", "api/:version.Pay/receiveNotify");

//断点调试微信支付接口
Route::post("api/:version/pay/re_notify", "api/:version.Pay/redirectNotify");