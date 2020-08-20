
// 权限管理
const Permission = () => import('@/pages/permission')
const UserManage = () => import('@/pages/permission/user-manage')
const RoleManage = () => import('@/pages/permission/role-manage')
const MenuManage = () => import('@/pages/permission/menu-manage')
const DeptManage = () => import('@/pages/permission/dept-manage/dept')
//网关管理
const Tempreture = () => import('@/pages/gate-manage/tempreture')
const Sensordata = () => import('@/pages/gate-manage/sensordata')
const Gatelist = () => import('@/pages/gate-manage/gatelist')
const Sensorlist = () => import('@/pages/gate-manage/sensorlist')
const Datagraph = () => import('@/pages/gate-manage/datagraph')
/* 需要权限判断的路由 */
const dynamicRoutes = [

    {
        path: '/permission',
        component: Permission,
        name: 'permission',
        meta: {
            name: '系统设置',
            is_menu:true,
            icon: 'table'
        },
        children: [
            {
                path: 'user',
                name: 'user-manage',
                component: UserManage,
                meta: {
                    name: '用户管理',
                    is_menu:true,
                    icon: 'table'
                }
            },
            {
                path: 'role',
                name: 'role-manage',
                component: RoleManage,
                meta: {
                    name: '角色管理',
                    is_menu:true,
                    icon: 'eye'
                }
            },
            {
                path: 'menu',
                name: 'menu-manage',
                component: MenuManage,
                meta: {
                    name: '菜单管理',
                    is_menu:true,
                    icon: 'tree'
                }
            },
            {
                path: 'department',
                name: 'department-manage',
                component: DeptManage,
                meta: {
                    name: '部门管理',
                    is_menu:true,
                    icon: 'tree'
                }
            },
            // {
            //     path: 'post',
            //     name: 'post-manage',
            //     component: MenuManage,
            //     meta: {
            //         name: '职位管理',
            //         icon: 'tree'
            //     }
            // }
        ]
    },
    {
        path: '/gate',
        component: Permission,
        name: 'gate',
        meta: {
            name: '监测管理',
            is_menu:true,
            icon: 'table'
        },
        children: [
            // {
            //     path: 'tempreture',
            //     name: 'gate-tempreture',
            //     component: Tempreture,
            //     meta: {
            //         name: '温度服务检测列表',
            //         icon: 'table'
            //     }
            // },
            {
                path: 'sensorlist',
                name: 'sensor-list',
                component: Sensorlist,
                meta: {
                    name: '传感器列表',
                    is_menu:true,
                    icon: 'eye'
                }
            },
            {
                path: 'sensordata',
                name: 'sensor-data',
                component: Sensordata,
                meta: {
                    name: '传感器数据查询',
                    is_menu:true,
                    icon: 'tree'
                }
            },
            {
                path: 'gatelist',
                name: 'gate-list',
                component: Gatelist,
                meta: {
                    name: '网关列表',
                    is_menu:true,
                    icon: 'tree'
                },
            },
            // {
            //     path: 'gatelog',
            //     name: 'gate-log',
            //     component: UserManage,
            //     meta: {
            //         name: '查看日志文件',
            //         is_menu:false,
            //         icon: 'table'
            //     }
            // },
            {
                path: 'datagraph',
                name: 'data-graph',
                component: Datagraph,
                meta: {
                    name: '数据统计',
                    is_menu:true,
                    icon: 'tree'
                }
            }
        ]
    },
    {
        path: '/basedata',
        component: Permission,
        name: 'basedata',
        meta: {
            name: '基础数据',
            is_menu:true,
            icon: 'table'
        },
        children: [
            {
                path: 'area',
                name: 'basedata-area',
                component: UserManage,
                meta: {
                    name: '区域管理',
                    is_menu:true,
                    icon: 'table'
                }
            },
            {
                path: 'factory',
                name: 'basedata-factory',
                component: RoleManage,
                meta: {
                    name: '风场管理',
                    is_menu:true,
                    icon: 'eye'
                }
            },
            {
                path: 'fan',
                name: 'basedata-fan',
                component: MenuManage,
                meta: {
                    name: '风机管理',
                    is_menu:true,
                    icon: 'tree'
                }
            },
            {
                path: 'device',
                name: 'basedata-device',
                component: MenuManage,
                meta: {
                    name: '台账设备',
                    is_menu:true,
                    icon: 'tree'
                },
                children:[
                    {
                        path: 'largeparts',
                        name: 'basedata-device-largeparts',
                        component: UserManage,
                        meta: {
                            name: '大部件',
                            is_menu:true,
                            icon: 'table'
                        }
                    },
                    {
                        path: 'parts',
                        name: 'basedata-device-parts',
                        component: UserManage,
                        meta: {
                            name: '部件',
                            is_menu:true,
                            icon: 'table'
                        }
                    },
                    {
                        path: 'subparts',
                        name: 'basedata-device-subparts',
                        component: UserManage,
                        meta: {
                            name: '子部件',
                            is_menu:true,
                            icon: 'table'
                        }
                    },
                ],
            },

        ]
    },
    {
        path: '/inspection',
        component: Permission,
        name: 'inspection',
        meta: {
            name: '巡检管理',
            is_menu:true,
            icon: 'table'
        },
        children: [
            {
                path: 'route',
                name: 'inspection-route',
                component: UserManage,
                meta: {
                    name: '巡检路线',
                    is_menu:true,
                    icon: 'table'
                }
            },
            {
                path: 'date',
                name: 'inspection-date',
                component: UserManage,
                meta: {
                    name: '巡检周期',
                    is_menu:true,
                    icon: 'table'
                }
            },
            {
                path: 'user',
                name: 'inspection-user',
                component: UserManage,
                meta: {
                    name: '巡检人',
                    is_menu:true,
                    icon: 'table'
                }
            },

        ]
    },

    {
        path: '/appmenu',
        component: Permission,
        name: 'appmenu',
        meta: {
            name: 'App菜单管理',
            is_menu:true,
            icon: 'table'
        },
        children: [
            {
                path: 'list',
                name: 'appmenu-list',
                component: UserManage,
                meta: {
                    name: '菜单列表',
                    is_menu:true,
                    icon: 'table'
                }
            },

        ]
    },


]

export default dynamicRoutes
