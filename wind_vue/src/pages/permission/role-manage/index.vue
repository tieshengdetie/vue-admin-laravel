<template lang="html">
    <el-card class="box-card">
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" ref="searchData" class="fl" size="mini">
                <el-input style="display: none;"></el-input>
                <el-form-item label="角色名称">
                    <el-input v-model="searchData.roleName" placeholder="角色名称"
                              @keyup.enter.native="onSearch()"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="text" @click="handleReset" size="mini">重置</el-button>
                    <el-button type="primary" icon="el-icon-search" @click="onSearch" size="mini">查询</el-button>
                </el-form-item>
            </el-form>

        </div>
        <div class="tools-bar">
            <el-button type="success" icon="el-icon-plus" size="mini" @click="handleAdd()">新增角色
            </el-button>
        </div>
        <div>
            <el-table
                ref="singleTable"
                :data="tableData"
                border
                size="small"
                :header-cell-style="tableStyle.headerCellStyle"
                :row-style="tableStyle.rowStyle"
                :cell-style="tableStyle.cellStyle"
                highlight-current-row
                style="width: 100%">
                <el-table-column
                    prop="id"
                    label="ID"
                    width="60">
                </el-table-column>
                <el-table-column
                    prop="roleName"
                    label="角色名"
                    width="120">
                </el-table-column>
                <el-table-column
                    label="操作权限"
                    prop="objPermission"
                    :formatter="permListFormatter">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="180">
                    <template slot-scope="scope">
                        <el-button type="text" size="small" @click="handleEditRoleName(scope.$index, scope.row)">修改角色名
                        </el-button>
                        <el-button type="text" size="small" @click="handlePower(scope.$index, scope.row)">授权</el-button>
                        <el-button type="text" size="small" @click="handleDelete(scope.$index, scope.row)">删除
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <div class="pagination-bar">
                <el-pagination
                    @size-change="handleSizeChange"
                    @current-change="handleCurrentChange"
                    :page-sizes="[20, 25, 50, 100]"
                    :page-size="page.pageSize"
                    :current-page.sync="page.currentPage"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="page.total">
                </el-pagination>
            </div>
        </div>
        <el-dialog :title="dialogTitle" :visible.sync="dialogVisible" @close="onDialogClose()" width="80%">
            <el-form label-width="120px">


                <el-form-item label="权限">
                    <el-tabs type="border-card">
                        <template v-for="(role, key) in menuTree">
                            <el-tab-pane :key="key" :label="role.menu_name">
                                <el-tree
                                    :data="role.children"
                                    show-checkbox
                                    default-expand-all
                                    node-key="id"
                                    ref="tree"
                                    :check-strictly='strictly'
                                    :default-checked-keys="role.checkedMenu"
                                    highlight-current
                                    :props="menuTreeProps"

                                >
                                </el-tree>
                            </el-tab-pane>
                        </template>
                    </el-tabs>
                </el-form-item>
            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button @click="dialogVisible = false">取 消</el-button>

                <el-button type="primary" @click="submitPower">保存</el-button>

            </div>
        </el-dialog>
        <!--    添加角色-->
        <el-dialog :title="dialogFormVisibleTitle" :visible.sync="dialogFormVisible" @close="onDialogClose()">
            <el-form :model="form" ref="form">
                <el-form-item label="名称" label-width="120px">
                    <el-input v-model="form.roleName"></el-input>
                </el-form-item>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="mini" @click="dialogFormVisible = false">取 消</el-button>
                <el-button size="mini" type="primary" @click="addOrEditRole()">确 定</el-button>
            </div>
        </el-dialog>

    </el-card>
</template>

<script>
    import {getRoleList, getAllPermissiion, createRole, deleteRole} from '@/api/permission'
    import {Message} from "element-ui";
    import {setTableStyle} from '@/common/utils'

    export default {
        data() {
            return {
                menuTree: [],

                strictly:true,

                menuTreeProps: {
                    children: 'children',
                    label: 'menu_name'
                },
                form: {
                    id: '',
                    roleName: '',

                },
                dialogFormVisible: false,
                dialogFormVisibleTitle: "添加角色",
                dialogSize: 'large',

                dialogVisible: false,
                dialogTitle: '角色授权',

                tableStyle: setTableStyle(),

                page: {
                    currentPage: 1,
                    total: 0,
                    pageSize: 20,
                },

                roleId: '',
                rules: {
                    roleName: [
                        {
                            required: true,
                            message: '角色名称不能为空',
                            trigger: 'blur'
                        }
                    ]
                },
                searchData: {
                    roleName: ''
                },

                roleForm: {
                    id: '',
                    roleName: '',
                    permissions: '',
                },

                tableData: []
            }
        },
        created() {

            this.onSearch()
        },
        methods: {
            onCheck(data,node,is_checked){


            },
            addOrEditRole() {
                let _that = this
                if (!this.form.roleName) {
                    this.$message.error('请填写角色名字!')
                    return
                }
                _that.$http.post('/SystermApi/createRole', {
                    id: this.form.id,
                    roleName: this.form.roleName
                }).then(function (res) {

                    Message.success("成功！")
                    _that.dialogFormVisible = false
                    _that.onSearch()

                })

            },
            handleSizeChange(val) {
                this.page.pageSize = val
                this.onSearch()
            },
            handleCurrentChange(val) {

                this.page.currentPage = val
                this.onSearch()
            },
            handleReset() {
                this.searchData = {
                    roleName: ''
                }
            },
            onDialogClose() {

                this.form.id = '';
                this.form.roleName = '';
                this.roleId = '';
            },
            onSearch() {
                let _that = this
                let data = {
                    page: this.page.currentPage,
                    pageSize: this.page.pageSize,
                    roleName: this.searchData.roleName
                }

                this.$http.post('/SystermApi/getRoleList', data).then(res => {

                    _that.tableData = res.data || []
                    _that.page.pageSize = res.per_page
                    _that.page.total = res.total


                })
            },
            handleDelete(index, row) {
                let _that = this

                this.$confirm('确认删除该角色?', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {

                    _that.$http.post('/SystermApi/deleteRole', {id: row.id}).then(function (res) {

                        Message.success('成功！')

                        _that.onSearch();

                    })
                })
            },
            handlePower(index, row) {

                let _that = this
                this.roleId = row.id
                _that.$http.get('/SystermApi/getMenuByRoleId', {roleId: row.id}).then(function (res) {

                    _that.menuTree = res || [];


                })
                this.dialogSize = 'large'
                this.dialogVisible = true
                this.dialogTitle = '角色授权'
            },
            getParentNode(tree){

                tree.getNode()
            },
            submitPower() {

                let _that = this
                let resMenu = new Set()

                for (let tree of this.$refs.tree) {

                    let nodes = tree.getCheckedNodes()

                    //循环每一个树形被选中的节点
                    for (let node of nodes) {

                        resMenu.add(node.parent_id)

                        //需要把父亲的父亲也放进去才能保证左侧菜单正常显示

                        let parentNode = tree.getNode(node.parent_id)

                        if(parentNode && parentNode.data.parent_id>0){

                            resMenu.add(parentNode.data.parent_id)
                        }
                        resMenu.add(node.id)
                    }
                }

                if(resMenu.size ===0){

                    Message.error('请选择权限')
                    return;

                }
                let arrMenu = [...resMenu]

                let data ={roleId:this.roleId,menuArr:arrMenu}

                _that.$http.post('/SystermApi/handlePower',data).then(function (res) {

                    Message.success('成功！')
                    _that.dialogVisible = false

                    _that.$router.go(0)

                })


            },
            handleAdd() {
                this.dialogFormVisible = true
            },
            handleEditRoleName(index, row) {
                this.dialogSize = 'tiny'
                this.form.roleName = row.roleName;
                this.form.id = row.id;
                this.dialogFormVisible = true
                this.dialogFormVisibleTitle = '修改角色名称'
            },


            permListFormatter(row, column, cellValue) {
                let str = []
                for (let item of cellValue) {
                    str.push(item.menu_name)
                }
                return str.join('，')
            },



        }
    }
</script>

<style>
    .fr {
        float: right;
    }

    .fl {
        float: left;
    }

    .search-bar {
        overflow: hidden;
    }

    .tools-bar {
        margin-bottom: 20px;
    }
</style>
