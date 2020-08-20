/**
 * Created by zhaojinsheng at 2019/1/23 11:31 AM
 *
 * Desc :
 */
import{Message,Loading} from 'element-ui';
import router from '../router';

let objLoading ;
export default {
    setError: function(err){

        if (err && err.data.code == 400) {
            Message({
                message: err.data.message,
                type: 'warning'
            });
        }else if(err && err.data.code == 401){
            router.push('/login');
        } else {
            Message({
                message: "服务器错误！",
                type: 'warning'
            });
        }
    },
    startLoading:function(){

        objLoading = Loading.service({
            lock: true,
            text: '拼命加载中……',
            background: 'rgba(0, 0, 0, 0.7)'
        });
    },
    endLoading:function(){
        objLoading.close();
    },

}
