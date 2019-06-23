import {
  Base
} from 'base.js';

import {
  Config
} from 'config.js';

class Address extends Base {

  constructor() {
    super();
  }

  //设置地址信息
  setAddressInfo(res) {

    var province = res.provinceName || res.province,
      city = res.cityName || res.city,
      country = res.countyName || res.country,
      detail = res.detailInfo || res.detail;

    var totalDetail = city + country + detail;

    if (!this.isCenterCity(province)) {
      totalDetail = province + totalDetail;
    }

    return totalDetail;
  }

  //判断是否为直辖市
  isCenterCity(name) {
    var centerCitys = ['北京市', '天津市', '上海市', '重庆市'],
      flag = centerCitys.indexOf(name) >= 0;
    return flag;
  }

  //更新保存地址信息到数据库
  submitAddress(data, callback) {
    data = this._setUpAddress(data);
    var param = {
      url: 'address',
      type: 'post',
      data: data,
      sCallback: function(res) {
        callback && callback(true, res);
      },
      eCallback(res) {
        callback && callback(false, res);
      }
    };
    this.request(param);
  }

  //保存地址(把微信官方地址接口变量转换为与数据库相对应的变量名称))
  _setUpAddress(res, callback) {
    var formData = {
      name: res.userName,
      province: res.provinceName,
      city: res.cityName,
      country: res.countyName,
      mobile: res.telNumber,
      detail: res.detailInfo
    };

    var aa = '{"name": "' + res.userName + '","mobile": "' + res.telNumber + '","province": "' + res.provinceName + '","city": "' + res.cityName + '","country": "' + res.countyName + '","detail": "' + res.detailInfo + '"}';

    var bb = '{"name": "' + res.userName + '","mobile": "15911394330","province": "' + res.provinceName + '","city": "' + res.cityName + '","country": "' + res.countyName + '","detail": "' + res.detailInfo + '"}';
    //console.log(formData1);

    return aa;
  }


  //访问服务器获取数据库中的address信息
  getAddress(callback) {
    var that = this;
    var param ={
      url:'address',
      sCallback:function(res){
        if(res){
          res.totalDetail = that.setAddressInfo(res);
          callback && callback(res);
        }
      }
    };
    this.request(param);
  }
}

export {
  Address
};