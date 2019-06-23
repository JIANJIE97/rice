<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/31
 * Time: 20:54
 */

namespace app\api\controller\v1;

use app\api\service\AppToken;
use app\api\validate\AppTokenGet;
use app\api\validate\TokenGet;
use app\api\service\UserToken;
use app\lib\exception\ParameterException;
use app\api\service\Token as TokenService;

class Token
{
    /*
     * @url=/Token/user?code
     * @http=post
     * */
    public function getToken($code=''){
        (new TokenGet())->goCheck();
        $ut=new UserToken($code);
        $token=$ut->get();

        return [
            'token' => $token
        ];
    }

    /*
     * 第三方应用获取令牌
     * @url=/app?ac=ac&se=secret
     * @http=post
     * */
    public function getAppToken($ac = '', $se = '')
    {
        (new AppTokenGet())->goCheck();
        $app = new AppToken();
        $token = $app->get($ac, $se);
        return [
            'token' => $token
        ];
    }

    //对token令牌校验接口
    public function verifyToken($token = '')
    {
        if (!$token) {
            throw new ParameterException([
                'token不允许为空'
            ]);
        }
        $valid = TokenService::verifyToken($token);
        return [
            'isValid' => $valid
        ];
    }
}