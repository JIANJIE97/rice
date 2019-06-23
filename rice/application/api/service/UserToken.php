<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/23
 * Time: 16:18
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;
use think\Exception;

class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;


    //构造函数初始化要访问接口的地址
    function __construct($code){
        $this->code=$code;
        $this->wxAppID=config('wx.app_id');
        $this->wxAppSecret=config('wx.app_secret');
        $this->wxLoginUrl=sprintf(config('wx.login_url'),$this->wxAppID,$this->wxAppSecret,$this->code);

    }

    //访问微信服务器提供的接口，获得openid，并制作令牌
    public function get(){
        //调用微信服务器的提供的接口
        $result = curl_get($this->wxLoginUrl);
        //把获取到的openid与session_key的json字符串解码为数组
        $wxResult = json_decode($result,true);
        //判断是否从微信接口中获取到session_key及openID
        if(empty($wxResult)){
            throw new Exception("获取session_key及openID时异常，微信内部错误");
        }
        else{
            //虽然不为空，但是可能还是有异常，判断errcode存不存在，存在抛出异常，没错的话就制作令牌
            $loginFail = array_key_exists('errcode',$wxResult);
            if($loginFail){
                $this->processLoginError($wxResult);
            }
            else{
                return $this->grantToken($wxResult);
            }
        }
    }

    //使用openid制作令牌
    //主方法
    private function grantToken($wxResult){
        //拿到openid
        //查看数据库中这个openid是否已经存在
        //如果存在了不要理会，如果不存在需要生成一条user表记录
        //生成token令牌，准备缓存数据，写入缓存
        //key:令牌
        //value:$wxResult、$uid、scope
        //把令牌返回到客户端

        //拿到openid
        $openid=$wxResult['openid'];
        //查看数据库中这个openid是否已经存在
        $user =UserModel::getByOpenID($openid);
        //如果存在了不要理会，如果不存在需要生成一条user表记录
        if($user){
            //存在
            $uid=$user->id;
        }
        else{
            //不存在
            $uid=$this->newUser($openid);
        }
        //生成token令牌，准备缓存数据
        $cachedValue = $this->prepareCachedValue($wxResult,$uid);
        //写入缓存
        $token = $this->saveToCached($cachedValue);
        //返回令牌
        return $token;
    }

    //写入缓存
    private function saveToCached($cachedValue){
        $key = self::generateToken();
        $value = json_encode($cachedValue);
        $expire_in = config('setting.token_expire_in');

        $request = cache($key,$value,$expire_in);
        if(!$request){
            throw new TokenException([
                'msg'=>'服务器缓存异常',
                'error_code'=>'10005'
            ]);
        }
        return $key;
    }

    //准备缓存数据
    private function prepareCachedValue($wxResult,$uid){
        $cachedValue=$wxResult;
        $cachedValue['uid']=$uid;
        //scope=16代表app用户的权限数值
        $cachedValue['scope']=ScopeEnum::User;
//        $cachedValue['scope']= 15;
        //scope=32代表cms(管理员)用户的权限数值
        //$cachedValue['scope']=32;

        return $cachedValue;
    }


    //把$openID上传数据库创建一个记录，把该记录主键返回
    private function newUser($openID){
        $user = UserModel::create([
            'openid'=>$openID
        ]);
        return $user->id;
    }


    //处理登陆错误
    private function processLoginError($wxResult){
        throw new WeChatException([
            'msg'=>$wxResult['errmsg'],
            'error_code'=>$wxResult['errcode']
        ]);
    }
}