<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/13
 * Time: 20:58
 */

namespace app\api\service;


use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use \app\api\service\Order as OrderService;
use \app\api\model\Order as OrderModel;
use think\Loader;
use think\Log;

// extend/WxPay/WxPay.Api.php
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class Pay
{
    private $orderID;
    private $orderNO;

    function __construct($orderID)
    {
        if (!$orderID) {
            throw new Exception("订单号不允许为NULL");
        }
        $this->orderID = $orderID;
    }

    //主方法
    public function Pay()
    {
        //订单号根本不存在
        //订单号确实存在，但是，下订单的用户与当前操作支付的用户不匹配
        //订单可能已经被支付过了
        //进行库存量检测
        $this->checkOrderValid();
        $orderService = new OrderService();
        $status = $orderService->checkOrderStock($this->orderID);
        if (!$status['pass']) {
            //如果库存不足就return出去中断了代码
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);
    }

    //创建微信的预订单，使用sdk定义预订单属性结构
    private function makeWxPreOrder($totalPrice)
    {
        //openid
        $openid = Token::getCurrentTokenVar('openid');
        if (!$openid) {
            throw new TokenException();
        }

        $wxOrderDate = new \WxPayUnifiedOrder();
        //设置微信支付预订单编号
        $wxOrderDate->SetOut_trade_no($this->orderNO);
        //设置微信支付编码类型
        $wxOrderDate->SetTrade_type('JSAPI');
        //设置微信支付总价格
        $wxOrderDate->SetTotal_fee($totalPrice * 100);
        //设置微信支付的主体描述
        $wxOrderDate->SetBody('米店');
        //设置微信支付的openid
        $wxOrderDate->SetOpenid($openid);
        //设置微信支付回调参数
        $wxOrderDate->SetNotify_url(config('secure.pay_back_url'));

        return $this->getPaySignature($wxOrderDate);
    }

    //调用微信接收预订单接口
    private function getPaySignature($wxOrderDate)
    {
        //调用微信接收预订单接口
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderDate);
        if ($wxOrder['return_code'] != 'SUCCESS' ||
            $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败', 'error');
        }
        //去数据库中保存prepay_id
        $this->recordPreOrder($wxOrder);
        //返回负责拉起客户端支付界面的的支付参数（[参数+签名]）
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    //调用支付的类库自动生成签名
    private function sign($wxOrder)
    {
        //创建自动生成签名的对象
        $jsApiPayDate = new\WxPayJsApiPay();
        //设置对象的属性，以便生成签名以及还要返回负责拉起客户端支付界面的的支付参数
        $jsApiPayDate->SetAppid(config('wx.app_id'));
        $jsApiPayDate->SetTimeStamp((string)time());

        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayDate->SetNonceStr($rand);
        $jsApiPayDate->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayDate->SetSignType('md5');

        //自动生成签名
        $sign = $jsApiPayDate->MakeSign();
        //把对象转换为数组数据
        $rawValues = $jsApiPayDate->GetValues();
        //数组中再加上sign签名
        $rawValues['PaySing'] = $sign;
        //app_id用不上，不传回去
        unset($rawValues['app_id']);
        return $rawValues;
    }

    //保存prepay_id到数据库的order表中
    private function recordPreOrder($wxOrder)
    {
        OrderModel::where('id', '=', $this->orderID)
            ->update(['prepay_id' => $wxOrder['prepay_id']]);
    }

    //库存量检测前的检查
    private function checkOrderValid()
    {
        $order = OrderModel::where('id', '=', $this->orderID)->find();

        //检测订单号存不存在
        if (!$order) {
            throw new OrderException();
        }
        //判断是不是订单号确实存在，但是，下订单的用户与当前操作支付的用户不匹配
        if (!Token::isValidOperate($order->user_id)) {
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                '$error_code' => 10003
            ]);
        }

        //判断是不是订单可能已经被支付过了
        if ($order->status != OrderStatusEnum::UNPAID) {
            throw new OrderException([
                'msg' => '订单已支付',
                '$error_code' => 80003,
                'code' => 400
            ]);
        }

        $this->orderNO = $order->order_no;
        return true;
    }
}