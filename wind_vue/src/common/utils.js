/**
 * Created by zhaojinsheng at 2019-04-18 09:03
 *
 * Desc :
 */
import pathToRegexp from "path-to-regexp";
import store from '../store/index'

export const getSessionKey = (key, defaultValue) => {
    const item = window.sessionStorage.getItem(key);
    if (item == undefined && defaultValue != undefined) {
        return defaultValue
    }
    return item;
}

export const getBaseUrl = (url) => {
    var reg = /^((\w+):\/\/([^\/:]*)(?::(\d+))?)(.*)/;
    reg.exec(url);
    return RegExp.$1;
}

export const keyMirror = (obj) => {
    let key
    let mirrored = {}
    if (obj && typeof obj === 'object') {
        for (key in obj) {
            if ({}.hasOwnProperty.call(obj, key)) {
                mirrored[key] = key
            }
        }
    }
    return mirrored
}

/**
 * 数组格式转树状结构
 * @param   {array}     array
 * @param   {String}    id
 * @param   {String}    pid
 * @param   {String}    children
 * @return  {Array}
 */
export const arrayToTree = (array, id = 'id', pid = 'pid', children = 'children') => {
    let data = array.map(item => ({...item}))
    let result = []
    let hash = {}
    data.forEach((item, index) => {
        hash[data[index][id]] = data[index]
    })

    data.forEach((item) => {
        let hashVP = hash[item[pid]]
        if (hashVP) {
            !hashVP[children] && (hashVP[children] = [])
            hashVP[children].push(item)
        } else {
            result.push(item)
        }
    })
    return result
}

export function getCurrentMenu(location, arrayMenu) {
    if (!!arrayMenu) {
        let current = []
        for (let i = 0; i < arrayMenu.length; i++) {
            const e = arrayMenu[i];
            const child = getCurrentMenu(location, e.children);
            if (!!child && child.length > 0) {
                child.push({...e, children: null});
                return child;
            }
            if (e.href && pathToRegexp(e.href).exec(location)) {
                current.push({...e, children: null});
                return current;
            }
        }
        return current;
    }
    return null;
}

/**
 * 验证不能为空
 *
 *
 */
export function stringIsNull(str) {
    if (str) {
        if (str.toString().trim().length === 0) {
            return false
        } else {
            return true
        }
    } else {
        return false
    }
}

/**
 * 去前后空格
 */
export function stringTrimeFn(str) {
    if (str.length) {
        return str.trim();
    }
}

/*
 *  设置表格样式
 */
export function setBoxHeight() {
    return document.body.clientHeight;
}

/*
 *  设置表格样式
 */
export function setTableStyle() {
    return {
        headerRowStyle: {
            'font-size': '14px',
            'color': '#333'
        },
        headerCellStyle:{
            'background-color': '#9bc4ee',
            'height': '35px',
            'color': '#3b4f60',
            'font-size': '12px',
        },
        rowStyle:{
            'height': '35px',
            'font-size': '12px',
        },
        cellStyle:{
            'padding': 0,

        }
    }
}

//图片路径
export function getImgURL(host, files, url) {
    if (url != "" && typeof url === "string") {
        if (url.indexOf('http') > -1) {
            return url
        }
        let preUrlFile = url.toString().substring(0, 8)
        return host + '/' + files + '/' + preUrlFile + '/' + url
    }
    return ""

}

//获取菜单操作权限
export function contolAuth() {
    let myuth = window.localStorage.getItem("userAuth");
    let obj = {};
    if (myuth && myuth.length > 0) {
        myuth = JSON.parse(myuth);
        myuth.forEach((res, index) => {
            obj[res] = res;
        })

    } else {

    }
    return obj;
}


/**
 * 验证isNull
 * */
export function isNull(str) {
    //验证isNull为0，为"" 为false,undefined NaN
    return !str && str !== 0 && typeof str !== "boolean" ? true : false;
}

/**
 * 电话号码验证
 * */
export function checkTel(id) {
    var obj = document.getElementById(id);
    var value = obj.value;
    var regTel1 = /^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$/.test(value); //带区号的固定电话
    var regTel2 = /^(\d{7,8})(-(\d{3,}))?$/.test(value); //不带区号的固定电话
    var regTel3 = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/.test(value); //手机电话
    if (value != "") {
        if (!regTel1 && !regTel2 && !regTel3) {
            //alert("电话号码输入有误！");
            obj.focus();
            return false;
        }
    } else {
        //alert("请输入电话号码！");
        return false;
    }
    //alert("电话号码输入正确！");
    return true;
}

export function checkPhone(value) {
    var phone = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(16[0-9]{1})|(17[0-9]{1})|(18[0-9]{1})|(19[0-9]{1}))+\d{8})$/.test(value); //手机电话
    if (value != "") {
        if (!phone) {
            return {
                isRight: false,
                cNmessage: '手机号码格式有误',
                eNmessage: 'Invalid Phonenumber'
            };
        }
    } else {

        return {
            isRight: false,
            cNmessage: '手机号不能为空',
            eNmessage: 'Enter Phonenumber'
        }
    }
    //alert("电话号码输入正确！");
    return {
        isRight: true,
        //message:'验证通过'
    };
}

/**
 * 验证邮箱
 */
export function isEmail(str) {
    var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/;
    return reg.test(str);
}

/**
 * 验证身份证号码
 *
 * */
export function isCP(str) {
    //身份证号（18位）正则
    var cP = /^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
    return cP.test(str);
}

/**
 *
 *
 * 验证中文
 *
 *
 * */
export function isZH(str) {
    var re1 = new RegExp("^([\u4E00-\uFA29]|[\uE7C7-\uE7F3]|[a-zA-Z0-9])*$");
    return rel.test(str);
}

/**
 * 验证url
 * */
export function isURL(str) {
    //URL正则
    var urlP = /^((https?|ftp|file):\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
    return urlP.test(str)
}

/**
 * 密码强度验证
 */
export function isPassword(str) {
    //密码强度正则，最少6位，包括至少1个大写字母，1个小写字母，1个数字，1个特殊字符
    if (str.lengt < 6) {
        return '密码长度小于6位'
    }
    if (str === "") {
        return "密码不能为空"
    }
    var pPattern = /^.*(?=.{6,})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*? ]).*$/;
    return pPattern.test(str);
}

/**
 * 获取操作系统
 */
export function getExplore() {

    var sys = {},
        ua = navigator.userAgent.toLowerCase(),
        s;
    (s = ua.match(/rv:([\d.]+)\) like gecko/)) ? sys.ie = s[1] : (s = ua.match(/msie ([\d\.]+)/)) ? sys.ie = s[1] : (s = ua.match(/edge\/([\d\.]+)/)) ? sys.edge = s[1] : (s = ua.match(/firefox\/([\d\.]+)/)) ? sys.firefox = s[1] : (s = ua.match(/(?:opera|opr).([\d\.]+)/)) ? sys.opera = s[1] :
        (s = ua.match(/chrome\/([\d\.]+)/)) ? sys.chrome = s[1] : (s = ua.match(/version\/([\d\.]+).*safari/)) ? sys.safari = s[1] : 0;
    // 根据关系进行判断
    if (sys.ie) {
        return ('IE: ' + sys.ie)
    }
    if (sys.edge) {
        return ('EDGE: ' + sys.edge)
    }
    if (sys.firefox) {
        return ('Firefox: ' + sys.firefox)
    }
    if (sys.chrome) {
        return ('Chrome: ' + sys.chrome)
    }
    if (sys.opera) {
        return ('Opera: ' + sys.opera)
    }
    if (sys.safari) {
        return ('Safari: ' + sys.safari)
    }
    return 'Unkonwn'
}


/**
 *
 * @desc 获取操作系统类型
 * @return {String}
 */
export function getOS() {
    var userAgent = 'navigator' in window && 'userAgent' in navigator && navigator.userAgent.toLowerCase() || '';
    var vendor = 'navigator' in window && 'vendor' in navigator && navigator.vendor.toLowerCase() || '';
    var appVersion = 'navigator' in window && 'appVersion' in navigator && navigator.appVersion.toLowerCase() || '';

    if (/mac/i.test(appVersion)) {
        return 'MacOSX'
    }
    if (/win/i.test(appVersion)) {
        return 'windows'
    }
    if (/linux/i.test(appVersion)) {
        return 'linux'
    }
    if (/iphone/i.test(userAgent) || /ipad/i.test(userAgent) || /ipod/i.test(userAgent)) {
        return 'ios'
    }
    if (/android/i.test(userAgent)) {
        return 'android'
    }
    if (/win/i.test(appVersion) && /phone/i.test(userAgent)) {
        return 'windowsPhone'
    }

}

/*识别是手机端*/
export function mobilecheck() {
    var e = !1;
    return function (t) {
        (/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(t) || /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(t.substr(0, 4))) && (e = !0)
    }(navigator.userAgent || navigator.vendor || window.opera), e
}

//替换时间格式T为空格
export function timerFormat(time) {
    if (time) {
        var myTime = time.toString().split(":")[0] + ":" + time.toString().split(":")[1];
        myTime = myTime.replace('T', ' ');
        return myTime
    }
    return time.toString().replace('T', ' ');
}

//一位数字前面补0
export function stringFrontZore(first, type, secend) {
    first = first.toString().length >= 2 ? first : ('0' + first);
    secend = secend.toString().length >= 2 ? secend : ('0' + secend);
    return (first + type + secend);
}

export const hasPermission = (permission)=>{

    let myBtns = store.getters.getButton

    return myBtns.includes(permission)
}
