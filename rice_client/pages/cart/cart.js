// pages/cart/cart.js
import {
  Cart
} from 'cart-model.js';

var cart = new Cart();

Page({

  /**
   * 页面的初始数据
   */
  data: {

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function() {
    var cartData = cart.getCartDataFromLocal();
    //var countsInfo = cart.getCartTotalCount(true);
    var cal = this._calcTotalAccountAndCounts(cartData);

    this.setData({
      selectedCounts: cal.selectedCounts,
      selectedTypeCounts: cal.selectedTypeCounts,
      account: cal.account,
      cartData: cartData
    });
  },

  onHide() {
    cart.execSetStorageSync(this.data.cartData);
  },

  _calcTotalAccountAndCounts: function(data) {
    var len = data.length,
      //所需计算的总价格,但是必须是选中的总价格
      account = 0,
      //购买商品的总个数
      selectedCounts = 0,
      //购买商品种类的总数
      selectedTypeCounts = 0;
    let multiple = 100;

    for (let i = 0; i < len; i++) {
      if (data[i].selectStatus) {
        account += data[i].counts * multiple * Number(data[i].price) * multiple;
        selectedCounts += data[i].counts;
        selectedTypeCounts++;
      }
    }
    return {
      selectedCounts: selectedCounts,
      selectedTypeCounts: selectedTypeCounts,
      account: account / (multiple * multiple)
    };

  },
  toggleSelect: function(event) {
    var id = cart.getDataSet(event, 'id'),
      status = cart.getDataSet(event, 'status'),
      index = this._getProductIndexById(id);

    this.data.cartData[index].selectStatus = !status;
    this._resetCartData();

  },
  //重新计算数据方法
  _resetCartData: function() {
    //重新计算总金额和商品总数
    var newData = this._calcTotalAccountAndCounts(this.data.cartData);
    this.setData({
      selectedCounts: newData.selectedCounts,
      selectedTypeCounts: newData.selectedTypeCounts,
      account: newData.account,
      cartData: this.data.cartData
    });
  },

  //全选文本事件
  toggleSelectAll: function(event) {
    //status代表选中的数量是否是购物车的数量即是否全选
    var status = cart.getDataSet(event, 'status') == 'true';
    var data = this.data.cartData,
      len = data.length;
    for (let i = 0; i < len; i++) {
      data[i].selectStatus = !status;
    }

    this._resetCartData();
  },


  //根据商品id得到商品所在下标
  _getProductIndexById: function(id) {
    var data = this.data.cartData;
    var len = data.length;
    for (let i = 0; i < len; i++) {
      if (data[i].id == id) {
        return i;
      }
    }
  },
  //改变购物车的商品数量
  changeCounts: function(event) {
    var id = cart.getDataSet(event, 'id'),
      type = cart.getDataSet(event, 'type'),
      index = this._getProductIndexById(id),
      counts = 1;

    if (type == 'add') {
      cart.addCounts(id);
    } else {
      counts = -1;
      cart.cutCounts(id);
    }
    this.data.cartData[index].counts += counts;
    this._resetCartData();
  },
  //删除事件
  delete: function(event) {
    var id = cart.getDataSet(event, 'id'),
      index = this._getProductIndexById(id);

    this.data.cartData.splice(index, 1);
    this._resetCartData();
  },
  submitOrder: function(event) {
    wx.navigateTo({
      url: '../order/order?account=' + this.data.account + '&from=cart',
    });
  }
})