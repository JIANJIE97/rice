<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 20:47
 */

namespace app\api\service;


use app\lib\exception\OrderException;
use app\api\model\User as UserModel;
use app\lib\exception\UserException;

class DeliveryMessage extends WxMessage
{
    const DELIVERY_MSG_ID = '_-0f4gu1Xx6ywTSu0XTyTW9Yj4DUHS0yn6KnIOxx8_E';

    public function sendDeliveryMessage($order, $tplJumpPage)
    {
        if (!$order) {
            throw new OrderException();
        }

        $this->tplID = self::DELIVERY_MSG_ID;
        $this->formID = $order->prepay_id;
        $this->page = $tplJumpPage;
        $this->prepareMessageData($order);
        $this->emphasiskeyword = 'keyword2.DATA';
        return Parent::sendMessage($this->getUserOpenID($order->user_id));
    }

    private function prepareMessageData($order)
    {
        $dt = new \DateTime();
        $data = [
            'keyword1' => [
                'value' => '顺丰速运'
            ],
            'keyword2' => [
                'value' => $order->snap_name,
                'color' => '#274088'
            ],
            'keyword3' => [
                'value' => $order->order_no
            ],
            'keyword4' => [
                'value' => $dt->format("Y-m-d H:i")
            ],
        ];
        $this->data = $data;
    }

    private function getUserOpenID($uid)
    {
        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }
        return $user->openid;
    }
}