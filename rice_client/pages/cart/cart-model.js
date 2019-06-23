import {
  Base
} from '../utils/base.js';

class Cart extends Base {

  constructor() {
    super();
    this._storageKeyName = 'cart';
  }
  /*
   *加入到购物车
   *如果之前没有这件商品，则直接添加一条记录，数量为counts
   *如果有，则只相应数量 + counts
   *item - {obj}商品对象
   *counts - {int}商品数量
   */
  add(item, counts) {
    var cartData = this.getCartDataFromLocal(); //cartData是数组，item是对象

    var isHasInfo = this._isHasThatOne(item.id, cartData);

    if (isHasInfo.index == -1) {
      item.counts = counts;
      item.selectStatus = true; //设置选中状态
      cartData.push(item);
    } else {
      cartData[isHasInfo.index].counts += counts;
    }

    wx.setStorageSync(this._storageKeyName, cartData);
  }

  /*
   *从缓存中读取购物车数据
   */
  getCartDataFromLocal(flag) {
    var res = wx.getStorageSync(this._storageKeyName);
    if (!res) {
      res = [];
    }

    //在下单时过滤掉不下单的商品
    if (flag) {
      var newRes = [];
      for (let i = 0; i < res.length; i++) {
        if (res[i].selectStatus) {
          newRes.push(res[i]);
        }
      }
      res = newRes;
    }
    return res;
  }
  /*
   *获取购物车中商品总数量
   */
  getCartTotalCount(flag) {
    var data = this.getCartDataFromLocal();
    var counts = 0;

    for (let i = 0; i < data.length; i++) {
      if (flag) {
        if (data[i].selectStatus) {
          counts += data[i].counts;
        }
      } else {
        counts += data[i].counts;
      }

    }
    return counts;
  }

  /*
   *判断某个商品是否已经添加到购物车，并且返回这个商品的数据以及所在数据的序号
   */
  _isHasThatOne(id, arr) {
    var item;
    var result = {
      index: -1
    };
    for (let i = 0; i < arr.length; i++) {
      item = arr[i];
      if (item.id == id) {
        result = {
          index: i,
          data: item
        };
        break; //遍历这个购物车数据，直到遍历到选中的购物车数据就break跳出for循环
      }
    }
    //不管是否是跳出循环都返回result
    return result;
  }

  /*
   *修改商品数目
   *id- {int} 商品id
   *counts - {int} 商品数量
   */

  _changeCounts(id, counts) {
    var cartData = this.getCartDataFromLocal();
    var isHasInfo = this._isHasThatOne(id, cartData);
    if (isHasInfo.index != -1) {
      if (isHasInfo.data.counts > 1) {
        cartData[isHasInfo.index].counts += counts;
      }
    }
    //更新本地缓存
    wx.setStorageSync(this._storageKeyName, cartData);
  }

  /*
   *商品数目加一
   */
  addCounts(id) {
    this._changeCounts(id, 1);
  }

  /*
   *商品数目减一
   */
  cutCounts(id) {
    this._changeCounts(id, -1);
  }

  delete(ids) {
    //ids不是一串数组
    if (!(ids instanceof Array)) {
      ids = [ids];
    }

    var cartData = this.getCartDataFromLocal();
    for (let i = 0; i < ids.length; i++) {
      var hasInfo = this._isHasThatOne(ids[i], cartData);
      if (hasInfo.index != -1) {
        cartData.splice(hasInfo.index, 1); //删除数组某一项
      }
    }

    //更新本地缓存
    wx.setStorageSync(this._storageKeyName, cartData);
  }

  execSetStorageSync(data) {
    wx.setStorageSync(this._storageKeyName, data);
  }


}

export {
  Cart
};