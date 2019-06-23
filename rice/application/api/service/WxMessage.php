<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 20:48
 */

namespace app\api\service;


use think\Exception;

class WxMessage
{
    private $sendUrl = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?";
    private $touser;
    //不让子类控制颜色
    private $color = 'black';

    protected $tplID;
    protected $formID;
    protected $page;
    protected $emphasiskeyword;
    protected $data;

    function __construct()
    {
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $this->sendUrl = $this->sendUrl . "access_token=%s";
        $this->sendUrl = sprintf($this->sendUrl, $token);
    }

    //开发工具中拉起的微信支付prepay_id是无效的，需要在真机中拉起支付
    protected function sendMessage($openID)
    {
        $data = [
            'touser' => $openID,
            'template_id' => $this->tplID,
            'page' => $this->page,
            'form_id' => $this->formID,
            'data' => $this->data,
            'emphasis_keyword' => $this->emphasiskeyword,
        ];
        $result = curl_post($this->sendUrl, $data);
        $result = json_decode($result, true);
        if ($result['errcode'] == 0) {
            return true;
        } else {
            throw new Exception('模板消息发送失败，' . $result['errmsg']);
        }
    }
}