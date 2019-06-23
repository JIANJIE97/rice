<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/6
 * Time: 15:53
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;
use app\api\validate\OrderPlace;
use \app\api\service\Token as TokenService;
use \app\api\service\Order as OrderService;
use \app\api\model\Order as OrderModel;
use app\api\validate\PagingParameter;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;

class Order extends BaseController
{
    /*
     * 下单与支付的业务流程解析（需要编写“订单”与“支付”接口）
     * 1、用户在选择完商品后，向api接口提交包含它选择商品的相关信息
     * 2、API接收到信息后，需要检查订单相关商品的库存量（因为库存量不是同步的，所以显示有货，但是可能是没货了）
     * 3、有库存，把订单数据存入数据库=下单成功，生成订单，返回客户端消息，告诉客户端可以支付了
     *
     * 4、当客户端点击支付时，调用我们的服务端支付接口，进行支付
     * 5、API被调用后，还需要再次进行库存量检测（因为客户下了订单不一定马上支付，这之间可能被其他客户买光了）
     * 6、有库存，才可以继续调用接口，我们的服务端支付接口是调用微信的支付接口来实现支付功能的
     *
     * （我们的API接口会调用微信服务器的预订单的接口，微信服务器会返回一个支付参数）
     * （我们的API接口把支付参数返回小程序）
     * （小程序就会拉起支付界面支付，向微信服务器发送一个支付参数）
     * （微信服务器就会异步的推送支付成功还是失败给小程序和API接口）
     *
     * 7、微信服务器会返回一个我们的支付结果（异步，不一定会及时返回）
     * 8、成功：再次进行库存量检测（异步，不一定会及时返回结果），小概率事件
     * 9、有库存：才进行库存量的扣除
     * */

    //前置方法列表
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getDetail, getSummaryByUser']
    ];

    //订单接口函数
    public function placeOrder()
    {
        //验证器，验证Products参数信息
        (new OrderPlace())->goCheck();

        //获取提交的Products参数信息
        $products = input('post.products/a');
        //获取用户id
        $uid = TokenService::getCurrentUID();

        //创建orderService模型对象
        $order = new OrderService();
        $status = $order->place($uid, $products);
        return $status;


    }

    //分页历史订单接口
    public function getSummaryByUser($page = 1, $size = 15)
    {
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUID();
        $pagingOrders = OrderModel::getSummaryByUser($uid, $page, $size);

        if ($pagingOrders->isEmpty()) {
            return [
                'data' => [],
                'current_page' => $pagingOrders->getCurrentPage()
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address', 'prepay_id'])->toArray();
        return [
            'data' => $data,
            'current_page' => $pagingOrders->getCurrentPage()
        ];
    }

    //历史订单详情接口
    public function getDetail($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $orderDetail = OrderModel::get($id);
        if (!$orderDetail) {
            throw new OrderException();
        }
        return $orderDetail->hidden(['prepay_id']);
    }

    /*
    * cms获取全部订单简要信息（分页）
    * @param int $page
    * @param int $size
    * @return array
    * @throw \app\lib\exception\ParameterException
    * */

    public function getSummary($page = 1, $size = 20)
    {
        (new PagingParameter())->goCheck();
        $pagingOrders = OrderModel::getSummary($page, $size);
        if ($pagingOrders->isEmpty()) {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }

    /*
     * 调用发送模板消息接口
     * */
    public function delivery($id)
    {
        (new IDMustBePostiveInt())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        if($success){
            return new SuccessMessage();
        }
    }

}