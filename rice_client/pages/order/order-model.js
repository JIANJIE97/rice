import {
  Base
} from '../utils/base.js';

class Order extends Base {
  constructor() {
    super();
    this._storageKeyName = 'newOrder';
  }

  //下订单
  doOrder(param, callback) {
    var that = this;
    var allParams = {
      url: 'order',
      type: 'post',
      data: {
        products: param
      },
      sCallback: function(data) {
        that.execSetStorageSync(true);
        callback && callback(data);
      },
      eCallback: function() {}
    };
    this.request(allParams);
  }
  //本地缓存保存更新
  execSetStorageSync(data) {
    wx.setStorageSync(this._storageKeyName, data);
  }

  //支付
  execPay(orderNumber, callback) {
    var allParams = {
      url: 'pay/pre_order',
      type: 'post',
      data: {
        id: orderNumber
      },
      sCallback: function(data) {
        var timeStamp = data.timeStamp;
        if (timeStamp) {
          //可以支付
          wx.requestPayment({
            timeStamp: timeStamp.toString(),
            nonceStr: data.nonceStr,
            package: data.package,
            signType: data.signType,
            paySign: data.paySign,
            success: function() {
              callback && callback(2);
            },
            fail: function() {
              callback && callback(1);
            }
          });
        } else {
          callback && callback(0);
        }
      }
    };
    this.request(allParams);
  }

  //根据订单编号获取订单信息的具体内容
  getOrderInfoById(id, callback) {
    var that = this;
    var allParams = {
      url: 'order/' + id,
      type: 'get',
      sCallback: function(data) {
        callback && callback(data);
      },
      eCallback: function() {}
    };

    this.request(allParams);
  }

  //获得所有历史订单信息，从1开始
  getOrders(pageIndex, callback) {
    var that = this;
    var allParams = {
      url: 'order/by_user',
      data: {
        page: pageIndex
      },
      type: 'get',
      sCallback: function(data) {
        callback && callback(data);
      }
    };

    this.request(allParams);
  }

  //是否有新的订单
  hasNewOrder(){
    var flag = wx.getStorageSync(this._storageKeyName);
    return flag == true;
  }


}

export {
  Order
};