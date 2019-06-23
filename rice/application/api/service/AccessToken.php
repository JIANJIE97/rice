<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 20:49
 */

namespace app\api\service;


use think\Exception;

class AccessToken
{
    private $tokenUrl;
    const TOKEN_CACHE_KEY = 'access';
    const TOKEN_EXPIRE_IN = 7000;

    function __construct()
    {
        $url = config('wx.access_token_url');
        $url = sprintf($url, config('wx.app_id'), config('wx.app_secret'));
        $this->tokenUrl = $url;
    }

    public function get()
    {
        $token = $this->getFromCache();
        if (!$token) {
            return $this->getFromWxService();
        } else {
            return $token;
        }
    }

    public function getFromCache()
    {
        $token = cache(self::TOKEN_CACHE_KEY);
        if (!$token) {
            return null;
        }
        return $token['access_token'];
    }

    public function getFromWxService()
    {
        $token = curl_get($this->tokenUrl);
        $token = json_decode($token, true);
        if (!$token) {
            throw new Exception('获取AccessToken异常');
        }
        if (!empty($token['errcode'])) {
            throw new Exception($token['errmsg']);
        }
        $this->saveToCache($token);
        return $token['access_token'];
    }

    private function saveToCache($token)
    {
        cache(self::TOKEN_CACHE_KEY, $token, self::TOKEN_EXPIRE_IN);
    }
}