import {
  Base
} from '../utils/base.js';

class My extends Base {
  constructor() {
    super();
  }

  getUserInfo(callback){
    var that = this;
    wx.login({
      success:function(){
        wx.getUserInfo({
          success:function(res){
            typeof callback == 'function' && callback(res.userInfo);
          },
          fail:function(res){
            typeof callback == 'function' && callback({
              avatarUrl:'../../imgs/icon/user@default.png',
              nickName:'用户信息获取失败'
            });
          }
        });
      }
    });
  }
}

export {
  My
};