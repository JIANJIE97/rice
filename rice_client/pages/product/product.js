// pages/product/product.js

import {
  Product
} from 'product-model.js';

import {
  Cart
} from '../cart/cart-model.js';

var product = new Product;
var cart = new Cart;
Page({

  /**
   * 页面的初始数据
   */
  data: {
    id: null,
    countsArray: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
    productCount: 1,
    tabs_text: ['商品详情', '产品参数', '售后保障'],
    currentTabsIndex: 0
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var id = options.id;
    this.data.id = id;
    this._loadData();
  },

  _loadData: function() {
    product.getDetailInfo(this.data.id, (res) => {
      this.setData({
        'cartTotalCounts': cart.getCartTotalCount(),
        'product': res
      });
    });
  },

  bindPickerChange: function(event) {
    var index = event.detail.value;
    var selectedCount = this.data.countsArray[index];

    this.setData({
      'productCount': selectedCount
    });
  },
  //点击了这个事件后获取你当前点击时的下标=》点击栏目的数组下标
  onTabsItemTap: function(event) {
    var index = product.getDataSet(event, 'index');
    this.setData({
      'currentTabsIndex': index
    });
  },
  //点击加入购物车事件
  onAddingToCartTap: function(event) {
    this.addToCart();
    var counts = this.data.cartTotalCounts + this.data.productCount
    this.setData({
      'cartTotalCounts': cart.getCartTotalCount()
    });
  },
  addToCart: function() {
    var tempObj = {};
    var keys = ['id', 'name', 'main_img_url', 'price'];

    //遍历商品的详情数据对象,根据keys对tempObj赋值
    for (var key in this.data.product) {
      if (keys.indexOf(key) >= 0) {
        tempObj[key] = this.data.product[key];
      }
    }
    cart.add(tempObj, this.data.productCount);
  },

  onCartTap:function(event){
    wx.switchTab({
      url: '/pages/cart/cart',
    });
  }

})