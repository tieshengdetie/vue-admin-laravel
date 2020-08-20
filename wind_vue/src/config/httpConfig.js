import axios from 'axios'
import store from '@/store/index.js'
import baseURL from './baseUrl'
import {Message} from 'element-ui'
import functions from "../common/functions";

const http = {}



axios.defaults.timeout = 10000;
axios.defaults.baseURL =baseURL;


//http request 拦截器
axios.interceptors.request.use(
    config => {
        // 请求头添加token
        if (store.state.UserToken) {
            config.headers.Authorization = `Bearer ${store.state.UserToken}`
        }

        if(config.showloading === true){

        }
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);


//http response 拦截器
axios.interceptors.response.use(
    response => {


        return response.data;
    },
    error => {

        if(error.response.status==401){

            Message.warning({
                message: '授权失败，请重新登录'
            })
            store.commit('LOGIN_OUT')
            setTimeout(() => {
                window.location.reload()
            }, 1000)
        }else{
            Message.warning({
                message: error.response.data.msg
            })

        }
        if(error.response.config.showloading === true){
            functions.endLoading();
        }

        return Promise.reject(error.response)
    }
)



http.get = function (url ,params,showloading = false,config={}) {

    if(showloading === true){

        functions.startLoading();
    }

    return new Promise((resolve, reject) => {
        axios.get(url, {
            params: params,
            showloading:showloading,
            config
        }).then(response => {

            if(showloading === true){

                functions.endLoading();
            }

            if(response.code===200){

                resolve(response.data);
            }

        }).catch(err => {
            reject(err)
        })
    })
}
http.post = function (url,params,showloading=false,config={}) {

    if(showloading === true){

        functions.startLoading();
    }

    let obj = Object.assign({showloading:showloading},config)

    return new Promise((resolve, reject) => {
        axios.post(url, params,obj).then(response => {


            if(showloading === true){

                functions.endLoading();
            }

            if(response.code===200){

                resolve(response.data);
            }
        }).catch(err=>{
            reject(err)
        })
    })
}

export default http
