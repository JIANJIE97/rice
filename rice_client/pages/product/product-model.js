import {
  Base
} from '../utils/base.js';

class Product extends Base{
  constructor() {
    super();
  }

  getDetailInfo(id, Callback) {
    var params = {
      url: 'product/' + id,
      method: 'GET',
      sCallback: function (res) {
        Callback && Callback(res);
      }
    }
    this.request(params);
  }
}

export {Product};