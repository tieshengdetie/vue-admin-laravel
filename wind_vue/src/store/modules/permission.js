import { fetchPermission } from '@/api/permission'
import router, { DynamicRoutes } from '@/router/index'
import { recursionRouter,getMenuList } from '@/router/recursion-router'
import dynamicRouter from '@/router/dynamic-router'

export default {
    namespaced: true,
    state: {
        permissionList: null /** 所有路由 */,
        sidebarMenu: [] /** 导航菜单 */,
        currentMenu: '' /** 当前active导航菜单 */,
        control_list: [] /** 完整的权限列表 */,
        avatar: ''/** 头像 */,
        account: '',/** 用户角色 */
        buttons:[] //按钮权限
    },
    getters: {

    },
    mutations: {
        SET_AVATAR(state, avatar) {
            state.avatar = avatar
        },
        SET_ACCOUNT(state, account) {
            state.account = account
        },
        SET_PERMISSION(state, routes) {
            state.permissionList = routes
        },
        CLEAR_PERMISSION(state) {
            state.permissionList = null
        },
        SET_MENU(state, menu) {
            state.sidebarMenu = menu
        },
        CLEAR_MENU(state) {
            state.sidebarMenu = []
        },
        SET_CURRENT_MENU(state, currentMenu) {
            state.currentMenu = currentMenu
        },
        SET_CONTROL_LIST(state, list) {
            state.control_list = list
        },
        SET_BUTTON(state, list){

            state.buttons = list

        }

    },
    actions: {
        async FETCH_PERMISSION({ commit, state }) {
            let permissionList = await fetchPermission()
            commit('SET_AVATAR', permissionList.avatar)
            commit('SET_ACCOUNT', permissionList.name)

            //按钮权限
            let button = permissionList.dataButton

            /*  dynamicRouter 需要验证权限的路由（根据后台返回的数据判断当前登录人有哪些路由权限 根据后台权限跟我们定义好的权限对比，筛选出对应的路由并加入到path=''的children */
            let routes = recursionRouter(permissionList.data, dynamicRouter)

            let menuList = getMenuList(permissionList.data, dynamicRouter)

            // DynamicRoutes 不需要验证权限的路由 容器路由
            let MainContainer = DynamicRoutes.find(v => v.path === '')
            // 首页路由
            let children = MainContainer.children

            let objMenu = JSON.parse(JSON.stringify(MainContainer))

            let menuChild = objMenu.children

            menuChild.push(...menuList)
            // 所有路由
            commit('SET_CONTROL_LIST', [...children, ...dynamicRouter])
            // 实际有权限的路由加入到主路由的children 里 对象的复制是原型对象
            children.push(...routes)
            // 设置左侧有权限的路由数据
            commit('SET_MENU', menuChild)
            commit('SET_BUTTON', button)
            // 最原始的登录路由
            let initialRoutes = router.options.routes
            router.addRoutes(DynamicRoutes)
            commit('SET_PERMISSION', [...initialRoutes, ...DynamicRoutes])
        },

        async updateMenu({ commit, state }) {

            let permissionList = await fetchPermission()

            let menuList = getMenuList(permissionList.data, dynamicRouter)

            let MainContainer = DynamicRoutes.find(v => v.path === '')

            let objMenu = JSON.parse(JSON.stringify(MainContainer))
            // 首页路由
            let children = objMenu.children

            children.push(...menuList)
            // 设置左侧有权限的路由数据
            commit('SET_MENU', children)


        }
    }
}
