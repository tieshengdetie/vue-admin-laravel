import axios from '@/config/httpConfig'

let options = {isShowLoading:false}
/************************登录相关接口**************************/

export function login(data) {
    // return axios.post('/user/login', data,data,{isShowLoading:false})
    return axios.post('/SystermApi/login')
}

/************************权限相关接口**************************/
// 获取所有权限
export function getAllPermissiion() {
    return axios.get('/SystermApi/getAllPermissiion')
}
// 获取一级权限列表
export function getFirstLevel() {
    // return axios.get('/permission/resource')
    return axios.get('/SystermApi/resource')
}
// 获取次级权限列表
export function getNextLevel(id) {
    return axios.get(`/permission/level?id=${id}`)
}
export function fetchPermission() {
    // return axios.get('/user/info')
    return axios.post('/SystermApi/getUserInfo')
}
export function createOrEditMenu(data){

    return axios.post('/SystermApi/createOrEditMenu',data,options)
}


// 获取用户列表
export function getUserList(data) {
    // return axios.get('/user/list')
    return axios.post('/SystermApi/getUserList',data,options)
}
//创建用户
export function createUser(data) {
    // return axios.get('/user/list')
    return axios.post('/SystermApi/createUser',data,options)
}
export function createRole(data) {
    return axios.post('/SystermApi/createRole',data,options)
}
export function deleteRole(data) {

    return axios.post('/SystermApi/deleteRole',data,options)
}
// 获取角色列表
export function getRoleList(data) {
    return axios.post('/SystermApi/getRoleList',data,options)
}
//设置是否禁用
export function setIsUse(data) {
    return axios.post('/SystermApi/setIsUse',data,options)
}

export function resetPwd(data) {
    return axios.post('/SystermApi/resetPwd',data,options)
}


