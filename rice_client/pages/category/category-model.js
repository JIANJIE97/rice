import {
  Base
} from '../utils/base.js';

class Category extends Base {
  constructor() {
    super();
  }

  //获得所有分类
  getCategoryType(Callback) {
    var params = {
      url: 'category/all',
      method: 'GET',
      sCallback: function(res) {
        Callback && Callback(res);
      }
    }
    this.request(params);
  }

  //获得某种分类的商品
  getProductsByCategory(id, Callback) {
    var params = {
      url: 'product/by_category?id=' + id,
      method: 'GET',
      sCallback: function(res) {
        Callback && Callback(res);
      }
    }
    this.request(params);
  }
}

export {
  Category
};