<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/17
 * Time: 20:45
 */

namespace app\api\service;

use app\api\model\Product;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class WxNotify extends \WxPayNotify
{
    public function NotifyProcess($data, &$msg)
    {
        //判断微信支付是否成功
        if ($data['result_code'] == 'SUCCESS') {
            //如果成功继续业务逻辑
            //获得订单号
            $orderNo = $data['out_trade_no'];
            //使用事务与锁防止多次减库存
            //开始事务
            Db::startTrans();
            try {
                //根据订单号查询订单模型
                //查询语句的锁
                $order = OrderModel::where('order_no', '=', $orderNo)
                    ->lock(true)
                    ->find();
                //判断数据库中订单的支付状态是否为未支付
                if ($order->status == 1) {
                    //进行库存量检测
                    $service = new OrderService();
                    $stockStatus = $service->checkOrderStock($order->id);
                    if ($stockStatus['pass']) {
                        //更新订单状态
                        $this->updateOrderStatus($order->id, true);
                        //去数据库中减去商品数量
                        $this->reduceStock($stockStatus);
                    } else {
                        $this->updateOrderStatus($order->id, false);
                    }

                }
                //事务提交
                Db::commit();
                return true;
            } catch (Exception $exception) {
                //出错后回滚操作
                Db::rollback();
                //记录日志
                Log::error($exception);
                return false;
            }
        } else {
            //如果失败的话还是返回True，告诉客户端我知道你失败了，
            //不然你返回false的话，一直重复调用的话是没有意义的
            return true;
        }
    }

    //去数据库中减去商品数量
    private function reduceStock($stockStatus)
    {
        foreach ($stockStatus['pStatusArray'] as $singlePStatus) {
            Product::where('id', '=', $singlePStatus['id'])
                ->setDec('stock', $singlePStatus['count']);
        }
    }

    //更新订单状态
    private function updateOrderStatus($orderID, $success)
    {
        $status = $success ?
            OrderStatusEnum::PAID :
            OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id', '=', $orderID)
            ->update(['status' => $status]);
    }
}