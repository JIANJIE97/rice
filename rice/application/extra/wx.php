<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/23
 * Time: 16:54
 */

return [
    'app_id' => 'wx53ee16a091421f41',
    'app_secret' => 'fcea20047c797a01c2b63280f8492c77',
    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => 'https://api.weixin.qq.com/sns/jscode2session?' .
        'appid=%s&secret=%s&js_code=%s&grant_type=authorization_code',
    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
        "grant_type=client_credential&appid=%s&secret=%s",
];