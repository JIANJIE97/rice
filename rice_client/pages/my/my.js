// pages/my/my.js

import {
  My
} from 'my-model.js';

import {
  Order
} from '../order/order-model.js';

import {
  Address
} from '../../pages/utils/address.js';

var my = new My();
var address = new Address();
var order = new Order();


Page({

  /**
   * 页面的初始数据
   */
  data: {
    pageIndex: 1,
    orderArr: [],
    isLoadedAll: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this._loadData();
    this._getAddressInfo();
  },

  onShow: function() {
    var newOrderFlag = order.hasNewOrder();
    if (newOrderFlag) {
      this.refresh();
    }

  },

  //重新加载历史订单
  refresh: function() {
    var that = this;
    this.data.orderArr = []; //订单初始化
    this._getOrders(() => {
      that.data.isLoadedAll = false; //是否加载完全
      that.data.index = 1;
      order.execSetStorageSync(false);
    });
  },
  //获取地址信息
  _getAddressInfo: function() {
    address.getAddress((addressInfo) => {
      this._bindAddressInfo(addressInfo);
    });
  },

  //绑定地址信息
  _bindAddressInfo: function(addressInfo) {
    this.setData({
      addressInfo: addressInfo
    });
  },

  //初始化
  _loadData: function() {
    //获取用户名称与头像信息(接口升级已废弃)
    /*
    my.getUserInfo((data) => {
      this.setData({
        userInfo: data
      });
    });
    */

    this._getOrders();
  },

  _getOrders: function(callback) {
    order.getOrders(this.data.pageIndex, (res) => {
      var data = res.data;
      if (data.length) {
        this.data.orderArr.push.apply(this.data.orderArr, data);
        this.setData({
          orderArr: this.data.orderArr
        });
      } else {
        this.data.isLoadedAll = true;
      }
      callback && callback();
    });
  },

  onReachBottom: function() {
    if (!this.data.isLoadedAll) {
      this.data.pageIndex++;
      this._getOrders();
    }
  },

  //去订单详情页面
  showOrderDetailInfo: function(event) {
    var id = order.getDataSet(event, 'id');
    wx.navigateTo({
      url: '../order/order?from=order&id=' + id,
    });
  },

  //历史订单的再次付款
  rePay: function(event) {
    var id = order.getDataSet(event, 'id'),
      index = order.getDataSet(event, 'index');
    //this._execPay(id, index);
    this.showTips('支付提示', '本产品仅用于演示，支付系统已屏蔽');
  },

  _execPay: function(id, index) {
    var that = this;
    order.execPay(id, (statusCode) => {
      if (statusCode > 0) {
        var flag = statusCode == 2;

        //更新订单显示状态
        if (flag) {
          that.data.orderArr[index].status = 2;
          that.setData({
            orderArr: that.data.orderArr
          });
        }

        //跳转到成功页面
        wx.navigateTo({
          url: '../pay-result/pay-result?id=' + id + '&flag=' + flag + '&from=my',
        });
      } else {
        that.showTips('支付失败', '商品已下架或库存不足');
      }
    });
  },

  editAddress: function (event) {
    var that = this;
    wx.chooseAddress({
      success: function (res) {
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

  /*
   *提示窗口
   *title-{string}标题
   *content{string}内容
   */
  showTips: function(title, content) {
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      success: function(res) {}
    })
  }
})