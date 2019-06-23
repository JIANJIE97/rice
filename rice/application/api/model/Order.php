<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/4/7
 * Time: 21:14
 */

namespace app\api\model;


class Order extends BaseModel
{
    //隐藏模型的属性
    protected $hidden = ['user_id', 'delete_time', 'update_time'];

    //定义自动写入时间戳功能属性
    protected $autoWriteTimestamp = true;

    //获取历史订单模型接口
    public static function getSummaryByUser($uid, $page = 1, $size = 15)
    {
        $pagingDate = self::where('user_id', '=', $uid)
            ->order('create_time desc')
            ->paginate($size, true, ['page' => $page]);

        return $pagingDate;
    }

    //cms获取全部订单模型接口
    public static function getSummary($page = 1, $size = 20)
    {
        $pagingData = self::order('create_time desc')->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }

    //读取器
    public function getSnapItemsAttr($value)
    {
        if (empty($value)) {
            return null;
        }
        return json_decode($value);
    }

    public function getSnapAddressAttr($value)
    {
        if (empty($value)) {
            return null;
        }
        return json_decode($value);
    }
}