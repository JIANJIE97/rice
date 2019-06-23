// pages/order/order.js

import {
  Cart
} from '../cart/cart-model.js';

import {
  Order
} from 'order-model.js';

import {
  Address
} from '../../pages/utils/address.js';


var cart = new Cart();
var address = new Address();
var order = new Order();


Page({

  /**
   * 页面的初始数据
   */
  data: {
    //订单编号id，有则订单生成成功，没就失败
    id: null,
    //选择店食还是外卖，1外卖，2店食
    selectAddressStatus: 1,
    //第一次支付，可以生成订单，但是不能付款，之后都不能支付了
    firstPayStatus:1,
    //来源是哪里
    fromUrl: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var from = options.from;
    this.setData({
      fromUrl: from
    });
    if (from == 'cart') {
      this._fromCart(options.account);
    }
    else{
      var id = options.id;
      this._fromOrder(id);
    }
  },
  onShow: function() {
    if(this.data.id){
      this._fromOrder(this.data.id);
    }
  },

  _fromCart(account) {
    var productsArr;
    this.data.account = account;

    productsArr = cart.getCartDataFromLocal(true);
    this.setData({
      productsArr: productsArr,
      account: account,
      orderStatus: 0
    });

    //显示收货地址
    address.getAddress((res) => {
      this._bindAddressInfo(res);
    });
  },

  _fromOrder(id) {
    if (id) {
      //下单后，支付成功或者失败，点击左上角返回时能够更新订单状态，所以放在onshow中
      var that = this;
      //访问服务器，根据订单id获取数据库中订单信息
      order.getOrderInfoById(id, (data) => {

        that.setData({
          orderStatus: data.status,
          productsArr: data.snap_items,
          account: data.total_price,
          basicInfo: {
            orderTime: data.create_time,
            orderNo: data.order_no
          }
        });

        //快照地址
        var addressInfo = data.snap_address;
        addressInfo.totalDetail = address.setAddressInfo(addressInfo);
        that._bindAddressInfo(addressInfo);

      });
    }
  },

  editAddress: function(event) {
    var that = this;
    wx.chooseAddress({
      success: function(res) {
        var addressInfo = {
          name: res.userName,
          mobile: res.telNumber,
          totalDetail: address.setAddressInfo(res)
        }
        that._bindAddressInfo(addressInfo);

        //保存地址（保存到数据库中）
        address.submitAddress(res, (flag) => {
          if (!flag) {
            that.showTips('操作提示', '地址信息更新失败', true);
          }
        });
      }
    })
  },

  testPay:function(event){
    this.showTips('支付提示', '本产品仅用于演示，支付系统已屏蔽');
  },
  /*
   *提示窗口
   *title-{string}标题
   *content{string}内容
   *flag{bool}是否跳转到我的页面
   */
  showTips: function(title, content, flag) {
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      success: function(res) {
        if (flag) {
          wx.switchTab({
            url: '/pages/my/my',
          })
        }
      }
    })
  },

  //绑定地址信息
  _bindAddressInfo: function(addressInfo) {
    this.setData({
      addressInfo: addressInfo
    });
  },

 //选择店食事件
  selectNowStatus: function(event){
    this.setData({
      selectAddressStatus:1
    });
  },
 //选择外卖事件
  selectOutStatus: function (event) {
    this.setData({
      selectAddressStatus: 2
    });
  },






  //下单与支付
  pay: function() {
    //判断地址信息是否填写
    if (!this.data.addressInfo) {
      this.showTips('下单提示', '请填写你的收货地址');
      return;
    }
    //判断订单是否生成（购物车里的支付是还没生成订单，个人中心里面的历史订单是已经生成了订单）
    if (this.data.orderStatus == 0) {
      //第一次支付，先生成订单
      this._firstTimePay();
    } else {
      //不是第一次支付，已经存在订单直接支付
      this._oneMoresTimePay();
    }
  },

  //第一次支付
  _firstTimePay: function() {
    this.setData({
      firstPayStatus:2
    });
    var that = this;
    var orderInfo = [],
      productInfo = this.data.productsArr,
      order = new Order();
    for (let i = 0; i < productInfo.length; i++) {
      orderInfo.push({
        product_id: productInfo[i].id,
        count: productInfo[i].counts
      });
    }
    //支付分两步，第一步是生成订单号，然后根据订单号支付
    order.doOrder(orderInfo, (data) => {
      //订单生成成功
      if (data.pass) {
        //更新订单状态
        var id = data.order_id;
        that.data.id = id;
        that.data.fromCartFlag = false;
        //开始支付
        //that._execPay(id);
        that.showPay(id);
      } else {
        that._orderFail(data);
      }
    });
  },
  //开始支付
  _execPay: function(id) {
    var that = this;
    order.execPay(id, (statusCode) => {
      if (statusCode != 0) {
        //将已经下单的商品从购物车里删除
        that.deleteProducts();
        //如果statusCode==2，flag为true
        var flag = statusCode == 2;
        wx.navigateTo({
          url: '../pay-result/pay-result?id=' + id + '&flag=' + flag + '&from=order'
        });
      }
    });
  },

  //模拟支付弹窗
  showPay(id) {
    var that = this;
    wx.showModal({
      title: '支付提示',
      content: '本产品仅用于演示，支付系统已屏蔽',
      showCancel: false,
      success: function(res) {
        //将已经下单的商品从购物车里删除
        that.deleteProducts();
        var flag = false;
        wx.navigateTo({
          url: '../pay-result/pay-result?id=' + id + '&flag=' + flag + '&from=order'
        })
      }
    })
  },
  //下单失败data-{obj}订单结果信息
  _orderFail: function(data) {
    var nameArr = [],
      name = '',
      str = '',
      pArr = data.pStatusArray;
    for (let i = 0; i < pArr.length; i++) {
      if (!pArr[i].haveStock) {
        name = pArr[i].name;
        if (name.length > 15) {
          name = name.substr(0, 12) + '...';
        }
        nameArr.push(name);
        if (nameArr.length >= 2) {
          break;
        }
      }
    }
    str += nameArr.join('、');
    if (nameArr.length > 2) {
      str += '等';
    }
    str += '缺货';
    wx.showModal({
      title: '下单失败',
      content: str,
      showCancel: false,
      success: function(res) {}
    })
  },
  //将已经下单的商品从购物车中删除
  deleteProducts: function() {
    var ids = [],
      arr = this.data.productsArr;
    for (let i = 0; i < arr.length; i++) {
      ids.push(arr[i].id);
    }
    cart.delete(ids);
  }
})