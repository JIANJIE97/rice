import {
  Config
} from 'config.js';

class Token {
  constructor() {
    this.verifyUrl = Config.restUrl + 'token/verify';
    this.tokenUrl = Config.restUrl + 'token/user';
  }

  verify() {
    var token = wx.getStorageSync('token');
    //判断token是否为空
    if (!token) {
      //为空重新请求token
      this.getTokenFromServer();
    } else {
      //非空通过服务器API检测token
      this._verifyFromServer(token);
    }
  }
  //通过服务器请求token
  getTokenFromServer(callBack) {
    var that = this;
    wx.login({
      //登陆获取code码
      success: function(res) {
        //访问服务器API
        wx.request({
          url: that.tokenUrl,
          method: 'POST',
          data: {
            code: res.code
          },
          success: function(res) {
            wx.setStorageSync('token', res.data.token);
            callBack && callBack(res.data.token);
          }
        });
      }
    })
  }

  //携带token令牌去服务器API校验token令牌
  _verifyFromServer(token) {
    var that = this;
    wx.request({
      url: that.verifyUrl,
      method: 'POST',
      data: {
        token: token
      },
      success: function(res) {
        //如果token无效再去服务器获取token
        var valid = res.data.isValid;
        if (!valid) {
          that.getTokenFromServer();
        }
      }
    });
  }
}

export {
  Token
};