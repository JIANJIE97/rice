<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/6
 * Time: 21:56
 */

namespace app\api\service;


use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\api\model\Order as OrderModel;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;

class Order
{
    //定义三个属性
    //定义订单商品信息
    protected $oProducts;

    //定义真实商品信息
    protected $products;

    //定义用户id
    protected $uid;

    //模型的业务方法，需要提交用户的id，还有订单商品列表
    //主方法
    public function place($uid, $oProducts)
    {
        $this->uid = $uid;
        //products和oProducts做对比得出库存量
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);

        //获取订单状态
        $status = $this->getOrderStatus();
        //判断订单是否通过
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        //开始创建订单
        //生成订单快照
        $orderSnap = $this->snapOrder($status);
        //调用创建订单函数
        $order = $this->createOrder($orderSnap);
        //设置订单是否通过
        $order['pass'] = true;
        return $order;
    }

    //创建订单
    private function createOrder($snap)
    {
        Db::startTrans();
        try {
            //生成订单编号
            $orderNo = self::makeOrderNo();
//            $orderNo = $this->makeOrderNo();
            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);

            //多对多模型保存因为商品表里面已有数据这里实质是一对多
            //一对多建议分两步单独保存
            //多对多拆分成一对多
            //order模型的单独保存
            $order->save();


            //获取订单表的id
            $orderID = $order->id;
            //遍历订单商品，添加订单商品列表的order_id属性

            //获取订单创建时间
            $create_time = $order->create_time;
            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $orderID;
            }

            //创建订单商品模型对象
            $orderProduct = new OrderProduct();
            //OrderProduct模型的单独保存
            $orderProduct->saveAll($this->oProducts);
            Db::commit();

            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

    }

    //生成订单编号
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

    //生成订单快照函数
    private function snapOrder($status)
    {
        //快照信息列表
        $snap = [
            //订单的总价格
            'orderPrice' => 0,
            //商品总数量
            'totalCount' => 0,
            //商品状态
            'pStatus' => [],
            //快照地址信息
            'snapAddress' => null,
            //商品名称
            'snapName' => '',
            //商品图片地址
            'snapImg' => '',
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];

        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }

        return $snap;
    }

    //获取用户地址
    private function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户收货地址不存在，下单失败',
                'error_code' => '60001'
            ]);
        }
        return $userAddress->toArray();
    }

    //抽象出库存量检查方法
    public function checkOrderStock($orderID)
    {
        $oProducts = OrderProduct::where("order_id", "=", $orderID)
            ->select();
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $status = $this->getOrderStatus();
        return $status;
    }

    //获得订单状态
    private function getOrderStatus()
    {
        //订单状态列表
        $status = [
            //订单是否通过，如果订单的某一个商品缺货，设为false
            'pass' => true,
            //订单的总价格，是所有订单商品价格的和
            'orderPrice' => 0,
            //商品总数量
            'totalCount' => 0,
            //订单详情列表，包含订单每一个商品的详情信息
            'pStatusArray' => []
        ];

        //遍历订单商品列表，获取订单状态列表
        foreach ($this->oProducts as $oProduct) {
            //调用获得商品状态的方法
            $pStatus = $this->getProductStatus(
                $oProduct['product_id'],
                $oProduct['count'],
                $this->products
            );
            //如果某一个商品状态中的有货变量为false，就设置订单状态的pass变量为false
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            //求得订单状态中的订单总价格
            $status['orderPrice'] += $pStatus['totalPrice'];

            //求得订单状态中的商品的总数量
            $status['totalCount'] += $pStatus['counts'];

            //订单详情列表的赋值
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    //获得商品状态
    private function getProductStatus($oPID, $oCount, $products)
    {
        //定义商品列表序号
        $pIndex = -1;
        //定义商品状态列表
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'counts' => 0,
            'price' => 0,
            'name' => '',
            'totalPrice' => 0,
            'main_img_url' => null
        ];

        //遍历商品列表，获取订单商品id在商品列表中的序号
        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }

        //如果商品列表中没有订单商品所对应的id就抛出异常
        if ($pIndex == -1) {
            throw new OrderException([
                'msg' => 'id为' . $oPID . '的商品不存在，创建订单失败'
            ]);
        } else {
            //存在就对商品状态列表赋值
            $product = $products[$pIndex];
            //订单商品id
            $pStatus['id'] = $product['id'];
            //订单商品购买的数量
            $pStatus['counts'] = $oCount;
            //订单商品的名称
            $pStatus['name'] = $product['name'];
            //订单商品的单价
            $pStatus['price'] = $product['price'];
            //订单中某一个商品的总价格
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            //订单商品的图片
            $pStatus['main_img_url'] = $product['main_img_url'];
            //如果库存足够就设为true
            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;
    }


    //根据订单商品列表获取真实商品列表
    private function getProductsByOrder($oProducts)
    {
        //定义订单商品id列表
        $oPIDs = [];
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['product_id']);
        }

        //根据订单商品id列表查询数据库中Product表中的商品信息
        $products = Product::all($oPIDs)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();

        return $products;
    }

    /*
     * 调用发送模板消息服务层接口
     * */
    public function delivery($orderID, $JumpPage = '')
    {
        $order = OrderModel::where('id', '=', $orderID)->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != OrderStatusEnum::PAID) {
            throw new OrderException([
                'msg' => '还没付款，不能发货',
                '$error_code' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order, $JumpPage);
    }
}