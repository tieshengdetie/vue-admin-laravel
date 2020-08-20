<template lang="html">
    <el-card class="box-card">
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" class="fl" size="mini">

                <el-form-item label="登录名称">
                    <el-input v-model="searchData.login_name" placeholder="登录名称"
                              @keyup.enter.native="onSearch()"></el-input>
                </el-form-item>
                <el-form-item label="真实姓名">
                    <el-input v-model="searchData.realname" placeholder="真实姓名"></el-input>
                </el-form-item>
                <el-form-item label="手机">
                    <el-input v-model="searchData.mobile" placeholder="手机"></el-input>
                </el-form-item>
                <el-form-item label="邮箱">
                    <el-input v-model="searchData.email" placeholder="邮箱"></el-input>
                </el-form-item>
                <el-form-item label="部门">
                    <el-input v-model="searchData.dept_id" placeholder="部门"></el-input>
                </el-form-item>
                <el-form-item label="职位">
                    <el-input v-model="searchData.post_id" placeholder="职位"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="text" @click="handleReset" size="mini">重置</el-button>
                    <el-button type="primary" icon="el-icon-search" @click="initList" size="mini">查询</el-button>
                </el-form-item>
            </el-form>

        </div>
        <div class="tools-bar">
            <el-button type="success" icon="el-icon-plus" size="mini" @click="dialogVisible = true;dialogTitle='新增用户'">
                新增用户
            </el-button>
        </div>
        <div>
            <el-table
                v-loading.body="tableLoading"
                ref="singleTable"
                :data="tableData"
                border
                size="small"
                highlight-current-row
                :header-cell-style="tableStyle.headerCellStyle"
                :row-style="tableStyle.rowStyle"
                :cell-style="tableStyle.cellStyle"
                style="width: 100%">
                <el-table-column
                    prop="id"
                    label="ID"
                    width="60">
                </el-table-column>

                <el-table-column
                    prop="login_name"
                    label="登录名"
                    min-width="120">
                </el-table-column>
                <el-table-column
                    prop="realname"
                    label="真实姓名"
                    min-width="120">
                </el-table-column>
                <el-table-column
                    prop="sex_name"
                    label="性别"
                    width="100">
                </el-table-column>
                <el-table-column
                    prop="mobile"
                    label="联系电话"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="dept_name"
                    label="部门"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="post_name"
                    label="职位"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="leaderList"
                    :formatter="leaderFormatter"
                    min-width="210"
                    label="领导">
                </el-table-column>
                <el-table-column
                    prop="roleList"
                    :formatter="roleFormatter"
                    min-width="210"
                    label="角色">
                </el-table-column>
                <el-table-column
                    prop="address"
                    min-width="200"
                    label="联系地址">
                </el-table-column>
                <el-table-column
                    prop="email"
                    label="电子邮箱"
                    width="250">
                </el-table-column>
                <el-table-column
                    prop="lastLoginTime"
                    label="最后登录时间"
                    width="180">
                </el-table-column>
                <el-table-column
                    prop="is_use_name"
                    label="是否禁用"
                    width="100">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="170">
                    <template slot-scope="scope">
                        <div>
                            <el-button
                                type="text"
                                size="small"
                                @click="handleEdit(scope.$index, scope.row)"
                            >编辑
                            </el-button>
                            <el-button
                                type="text"
                                size="small"
                                @click="handleResetPwd(scope.$index, scope.row)"
                            >重置密码
                            </el-button>
                            <el-button
                                v-if="scope.row.is_use==1"
                                type="text"
                                size="small"
                                @click="setIsUse(scope.$index, scope.row)"
                            >禁用
                            </el-button>
                            <el-button
                                v-if="scope.row.is_use==0"
                                type="text"
                                size="small"
                                @click="setIsUse(scope.$index, scope.row)"
                            >启用
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div class="pagination-bar">
                <el-pagination
                    size="mini"
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                    :page-sizes="[20, 25, 50, 100]"
                    :page-size="page.pageSize"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="page.total">
                </el-pagination>
            </div>
        </div>
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" @close="onDialogClose()">
            <el-form ref="dataForm" :rules="rules" :model="dataForm" label-width="80px" size="small">
                <el-form-item label="登录名" prop="login_name">
                    <template v-if="dialogTitle=='修改用户信息'">{{dataForm.login_name}}</template>
                    <el-input v-else v-model="dataForm.login_name" placeholder="登录名"></el-input>
                </el-form-item>
                <el-form-item label="用户角色" prop="roleIds">
                    <el-select v-model="dataForm.role_ids" multiple placeholder="请选择" style="width: 100%;">
                        <el-option
                            v-for="item in roles"
                            :key="item.id"
                            :label="item.roleName"
                            :value="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="真实姓名" prop="realname">
                    <el-input v-model="dataForm.realname" placeholder="真实姓名"></el-input>
                </el-form-item>
                <el-form-item label="性别" prop="sex">
                    <el-radio-group v-model="dataForm.sex" size="mini">
                        <el-radio-button label="1">男</el-radio-button>
                        <el-radio-button label="2">女</el-radio-button>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="联系电话" prop="mobile">
                    <el-input v-model="dataForm.mobile" placeholder="联系电话"></el-input>
                </el-form-item>
                <el-form-item label="联系地址" prop="address">
                    <el-input v-model="dataForm.address" placeholder="联系地址"></el-input>
                </el-form-item>
                <el-form-item label="电子邮箱" prop="email">
                    <el-input v-model="dataForm.email" placeholder="电子邮箱"></el-input>
                </el-form-item>
                <el-form-item label="上级领导" prop="leader">
                    <template>
                        <el-select
                            v-model="dataForm.leader"
                            multiple
                            filterable
                            remote
                            reserve-keyword
                            placeholder="请输入关键词"
                            :remote-method="getUserByName"
                            :loading="loading"
                            style="width:100%">
                            <el-option
                                v-for="item in leaderData"
                                :key="item.id"
                                :label="item.login_name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                    </template>
                </el-form-item>
                <el-form-item label="部门" prop="dept_id">
                    <el-input v-model="dataForm.dept_id" placeholder="部门"></el-input>
                </el-form-item>
                <el-form-item label="职位" prop="post_id">
                    <el-input v-model="dataForm.post_id" placeholder="职位"></el-input>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogVisible = false">取 消</el-button>
                <el-button type="primary" @click="onDialogSubmit('dataForm')" v-if="dialogTitle=='修改用户信息'">保存</el-button>
                <el-button type="primary" @click="onDialogSubmit('dataForm')" v-else>立即创建</el-button>
            </div>
        </el-dialog>
    </el-card>
</template>
<script>


    import { Message } from 'element-ui'
    import {setTableStyle} from '@/common/utils'


    export default {
        data() {
            return {

                tableLoading: false,
                dialogVisible: false,
                dialogTitle: '新增用户',
                page:{
                    currentPage:1,
                    total: 0,
                    pageSize: 20,
                },
                loading:false,
                tableStyle:setTableStyle(),
                roles:[],
                defaultTreeProps: {
                    children: 'childPermList',
                    label: 'permissionName'
                },
                leaderData:{

                },
                rules: {
                    login_name: [
                        {
                            required: true,
                            message: '登录名不能为空',
                            trigger: 'blur'
                        },
                        {
                            min: 1,
                            max: 50,
                            message: '登录名长度在 1 到 50 个字符',
                            trigger: 'blur'
                        }
                    ],
                    realname: [
                        {
                            required: true,
                            message: '真实姓名不能为空',
                            trigger: 'blur'
                        },
                        {
                            min: 1,
                            max: 20,
                            message: '真实姓名长度在 1 到 20 个字符',
                            trigger: 'blur'
                        }
                    ],
                    mobile: [
                        {
                            required: true,
                            message: '联系电话不能为空',
                            trigger: 'blur'
                        },
                        {
                            pattern: /^(13|15|18|14|17)[0-9]{9}$/,
                            message: '手机号码格式不正确',
                            trigger: 'blur'
                        }
                    ],
                    email: [
                        {
                            required: true,
                            message: '请输入邮箱地址',
                            trigger: 'blur'
                        },
                        {
                            type: 'email',
                            message: '邮箱格式不正确',
                            trigger: 'blur, change'
                        }
                    ],
                    dept_id: [
                        {
                            required: true,
                            message: '请选择部门',
                            trigger: 'blur'
                        },
                    ],
                    post_id: [
                        {
                            required: true,
                            message: '请选择职位',
                            trigger: 'blur'
                        },

                    ]
                },
                searchData: {
                    login_name: '',
                    realname: '',
                    mobile: '',
                    email: '',
                    dept_id: '',
                    post_id: ''

                },
                dataForm: {
                    id:'',
                    login_name: '',
                    role_ids: [],
                    sex: 1,
                    realname: '',
                    mobile: '',
                    address: '',
                    email: '',
                    dept_id: '',
                    post_id: '',
                    leader:''


                },
                tableData: []
            }
        },
        created() {

            this.initList()
        },
        methods: {
            initList() {
                let _that = this

                let searchData = this.searchData;

                searchData.page = this.page.currentPage

                searchData.pageSize =this.page.pageSize

                _that.$http.post('/SystermApi/getUserList',searchData,true).then(function(res){

                     let data = res.userData

                     _that.page.pageSize = data.per_page
                     _that.page.total = data.total
                     _that.tableData = data.data
                     _that.roles = res.roleData
                 })

            },
            getUserByName(value){
                if(value.length===0){
                    return;
                }
                let _that = this
                _that.loading = true

                let id = this.dataForm.id;

                _that.$http.post("/SystermApi/getUserByName",{name:value,id:id}).then(function (res) {
                    _that.loading = false
                    _that.leaderData = res


                })
            },
            onDialogClose() {
                this.dataForm.role_ids = []
                this.$refs.dataForm.resetFields()
            },
            handleSizeChange(val) {
                this.page.pageSize = val
                this.initList();
            },
            handleCurrentChange(val) {
                this.page.currentPage = val
                this.initList();
            },
            handleReset() {
                this.searchData = {
                    login_name: '',
                    realname: '',
                    mobile: '',
                    email: '',
                    dept_id: '',
                    post_id: ''
                }
                this.initList();
            },

            roleFormatter(row, column, cellValue) {
                let result = []
                if (row.rolelist.length > 0) {
                    for (let item of row.rolelist) {
                        result.push(item.roleName)
                    }
                }
                return result.join('，')
            },
            leaderFormatter(row){
                let result = []
                if (row.leaderList.length > 0) {
                    for (let item of row.leaderList) {
                        result.push(item.realname)
                    }
                }
                return result.join('，')
            },

            handleResetPwd(index, row) {
                let _that = this
                this.$confirm('确认重置该用户的登录密码？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let data = {
                        id:row.id,
                    }
                    _that.$http.post('/SystermApi/resetPwd',data).then(function (res) {

                        Message.success("成功！");

                    })

                })
            },
            handleEdit(index, row) {

                this.dialogVisible = true
                this.dialogTitle = '修改用户信息'

                this.dataForm = {
                    id:row.id,
                    login_name: row.login_name,
                    sex: row.sex,
                    realname: row.realname,
                    mobile: row.mobile,
                    address: row.address,
                    email: row.email,
                    dept_id: row.dept_id,
                    post_id: row.post_id,
                    leader:row.leader,

                }

                if(row.role_ids.length>0){
                    let roleIds = []
                    for(let item of row.role_ids){

                        roleIds.push(parseInt(item));
                    }
                    this.dataForm.role_ids = roleIds;
                }

                if(row.leader.length>0){
                    let leaderIds = []
                    for(let item of row.leader){

                        leaderIds.push(parseInt(item));
                    }
                    this.dataForm.leader = leaderIds;
                }

                this.leaderData=row.leaderList

            },
            onDialogSubmit(formName) {

                let _that = this
                _that.$refs[formName].validate((valid) => {
                    if (valid) {
                        _that.$http.post('/SystermApi/createUser',_that.dataForm).then(function (res) {

                            Message.success("成功！");
                            _that.dialogVisible = false
                            _that.initList()

                        })
                    } else {
                        return false;
                    }
                })
            },
            setIsUse(index,row){

                let _that = this
                this.$confirm('确定要禁用该用户么？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {

                    let data = {
                        id:row.id,
                        is_use:row.is_use
                    }
                    _that.$http.post('/SystermApi/setIsUse',data).then(function (res) {

                        Message.success("成功！");

                        _that.initList()
                    })
                })


            }
        }
    }
</script>

<style lang="scss">

    .fl {
        float: left;
    }

    .search-bar {
        overflow: hidden;
    }
</style>

<style>
    .tools-bar {
        margin-bottom: 20px;
    }
</style>
