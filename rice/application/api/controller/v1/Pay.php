<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/13
 * Time: 20:49
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\WxNotify;
use app\api\validate\IDMustBePostiveInt;
use app\api\service\Pay as PayService;

class Pay extends BaseController
{
    //前置方法列表
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    public function getPreOrder($id = '')
    {
        (new IDMustBePostiveInt())->goCheck();
        $pay = new PayService($id);
        return $pay->Pay();
    }
    //断点调试微信支付的接口
    public function redirectNotify()
    {
        //通知频率为15/15/30/180/1800/1800/1800/1800/3600，单位：秒

        //先根据订单号查询到对应的模型，然后查看订单的支付状态，最后才检测库存量与超卖
        //更新这个订单的status状态
        //减去库存
        //如果处理成功，我们返回微信成功处理的信息。否则，我们需要返回没有成功处理

        //特点：使用post传输数据；数据是xml格式；路由里面不能在？后面携带参数

        $notify = new WxNotify();
        $notify->Handle();
    }

    //微信支付结果回调接口
    public function receiveNotify()
    {
        //通知频率为15/15/30/180/1800/1800/1800/1800/3600，单位：秒

        //先根据订单号查询到对应的模型，然后查看订单的支付状态，最后才检测库存量与超卖
        //更新这个订单的status状态
        //减去库存
        //如果处理成功，我们返回微信成功处理的信息。否则，我们需要返回没有成功处理

        //特点：使用post传输数据；数据是xml格式；路由里面不能在？后面携带参数

        /*$notify = new WxNotify();
        $notify->Handle();*/
        $xmlDate = file_get_contents('php://input');
        $result = curl_post_raw('http://r.cn/index.php/api/v1/pay/re_notify?XDEBUG_SESSION_START=12181',$xmlDate);
    }
}