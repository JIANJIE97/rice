import {
  Config
} from 'config.js';

import {
  Token
} from 'token.js';

class Base {

  constructor() {
    this.baseRequestUrl = Config.restUrl;
  }

  request(params, noRefetch) {
    var that = this;
    var url = this.baseRequestUrl + params.url;
    //console.log(params.data);

    if (!params.type) {
      params.type = 'GET';
    }

    wx.request({
      url: url,
      method: params.type,
      data: params.data,
      header: {
        'Content-Type': 'application/json',
        'token': wx.getStorageSync('token')
      },
      success: function(res) {
        var code = res.statusCode.toString();
        var starChar = code.charAt(0);

        if (starChar == '2') {
          //普通方法
          /*if(params.sCallback){
            params.sCallback(res);
          }*/
          //简洁方法
          params.sCallback && params.sCallback(res.data);
        } else {
          if (code == '401') {
            //如果noRefetch为true就不执行未授权（没有token令牌）重试函数
            if (!noRefetch) {
              that._refetch(params);
            }
          }
          params.eCallback && params.eCallback(res.data);
        }


      },
      fail: function(error) {
        console.log(error);
      }
    })
  }

  //‘401’第一次执行没有token令牌时先请求获取token再重新访问接口
  _refetch(params) {
    var token = new Token();
    token.getTokenFromServer((token) => {
      this.request(params, true);
    });
  }

  //获得元素上绑定的值
  getDataSet(event, key) {
    return event.currentTarget.dataset[key];
  }
}

export {
  Base
};