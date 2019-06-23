// pages/category/category.js

import {
  Category
} from 'category-model.js';

var category = new Category;

Page({

  /**
   * 页面的初始数据
   */
  data: {
    categoryTypeArr: {},
    categoryProducts: {},
    currentMenuIndex: 0,
    loadedData: {}
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this._loadData();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function() {},

  //判断当前分类下的商品数据是否已经被加载过
  isLoadedData: function(index) {
    if (this.data.loadedData[index]) {
      return true;
    }
    return false;
  },

  _loadData: function() {
    category.getCategoryType((categoryData) => {
      this.setData({
        'categoryTypeArr': categoryData
      });

      //一定要在回调里再进行获取分类详情的方法调用
      category.getProductsByCategory(categoryData[0].id, (data) => {
        var dataObj = {
          products: data,
          topImgUrl: categoryData[0].img.url,
          title: categoryData[0].name
        };

        //数据绑定
        this.setData({
          'categoryProducts': dataObj
        });
        //第一次加载保存到loadedData中
        this.data.loadedData[0] = dataObj;
      });
    })


  },

  onProductsItemTap: function(event) {
    var id = category.getDataSet(event, 'id');
    wx.navigateTo({
      url: '../product/product?id=' + id,
    })
  },

  changeCategory: function(event) {
    var id = category.getDataSet(event, 'id');
    var index = category.getDataSet(event, 'index');

    this.setData({
      'currentMenuIndex': index
    });


    if (!this.isLoadedData(index)) {
      //如果没有加载过当前分类的商品数据
      category.getProductsByCategory(id, (data) => {
        var dataObj = {
          products: data,
          topImgUrl: this.data.categoryTypeArr[index].img.url,
          title: this.data.categoryTypeArr[index].name
        };

        this.setData({
          'categoryProducts': dataObj
        });
        //第一次加载保存到loadedData中
        this.data.loadedData[index] = dataObj;
      });
    } else {
      //不是第一次加载就使用loadedData
      this.setData({
        'categoryProducts': this.data.loadedData[index]
      });
    }



  }
})