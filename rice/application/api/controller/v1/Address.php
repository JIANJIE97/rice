<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/25
 * Time: 14:51
 */

namespace app\api\controller\V1;


use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\service\Token as TokenService;
use app\api\model\UserAddress;
use app\api\validate\AddressNew;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;

class Address extends BaseController
{
    //定义前置方法列表
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdateAddress,getUserAddress']
    ];

    //获取数据库中的address信息
    public function getUserAddress(){
        $uid = TokenService::getCurrentUID();
        $userAddress = UserAddress::where('user_id',$uid)->find();
        if(!$userAddress){
            throw new UserException([
                'msg' => '用户地址不存在',
                'error_code' => 60001
            ]);
        }
        return $userAddress;
    }

    public function createOrUpdateAddress(){
        //1、控制器里new验证器
        //2、根据token获取用户UID：
        //3、根据uid来查找用户数据，判断用户是否存在，如果不存在抛出异常
        //4、获取用户从客户端提交过来的地址信息
        //5、根据用户地址信息是否存在，从而判断是添加地址还是更新地址
        $validate = new AddressNew();
        $validate->goCheck();

        //2、根据token获取用户UID：
        $uid = TokenService::getCurrentUID();

        //3、根据uid来查找用户数据，判断用户是否存在，如果不存在抛出异常
        $user = UserModel::get($uid);
        if(!$user){
            throw new UserException();
        }

        //4、获取用户从客户端提交过来的地址信息（参数过滤，客户端提交过来的信息不能包含有user_id或uid）
        $dataArray = $validate->getDateByRule(input('post.'));
        $userAddress = $user->address;

        //5、根据用户地址信息是否存在，从而判断是添加地址还是更新地址
        if(!$userAddress){
            //添加地址
            $user->address()->save($dataArray);
        }
        else{
            //更新地址
            $user->address->save($dataArray);
        }

        return json(new SuccessMessage(),201);


    }
}