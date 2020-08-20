<template lang="html">
    <el-card class="box-card">
        <div>
            <div style="margin-bottom:10px;">
                <el-button type="success" icon="el-icon-plus" size="mini" @click="handleNewMenu(1,0)">新增顶级菜单</el-button>
            </div>
            <el-table
                :data="menuList"
                row-key="id"
                highlight-current-row
                border

            >
                <el-table-column label="菜单名称" align="left"  prop="menu_name">

                </el-table-column>
                <el-table-column label="类型"  prop="typeName">

                </el-table-column>
                <el-table-column label="菜单代码"  prop="menu_code">

                </el-table-column>
                <el-table-column label="操作" width="350">
                    <template slot-scope="scope">
                        <div>
                            <el-button
                                size="mini"
                                type="primary"
                                v-if="scope.row.menu_level<4 && scope.row.type!==2"
                                @click="handleNewMenu(2,scope.row.id)"
                            >添加子菜单
                            </el-button>

                            <el-button
                                size="mini"
                                type="success"
                                @click="handleEditMenu(3,scope.row)"
                            >编辑
                            </el-button>
                            <el-button
                                size="mini"
                                type="danger"
                                @click="deleteMenu(scope.row)"
                            >删除
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <!-- 编辑类目弹窗 -->

            <!-- 新增类目弹窗 -->
            <el-dialog
                :title="objVisible.add.visibleTitle"
                :visible.sync="objVisible.add.visible"
                @close="onDialogClose()"
                width="50%">
                <el-form label-width="100px" :rules="rules" ref="menuForm" :model="menuForm">
                    <el-row>
                        <el-col :span="24">
                            <el-form-item label="菜单名称" prop="menu_name">
                                <el-input v-model="menuForm.menu_name"></el-input>
                            </el-form-item>
                            <el-form-item label="菜单代码" prop="menu_code">
                                <el-input v-model="menuForm.menu_code"></el-input>
                            </el-form-item>
                            <el-form-item v-if="objVisible.isTop===false" label="菜单类型" prop="type">
                                <el-select v-model="menuForm.type" placeholder="请选择" style="width: 100%;">
                                    <el-option
                                        v-for="item in menuType"
                                        :key="item.id"
                                        :label="item.name"
                                        :value="item.id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-form>
                <span slot="footer" class="dialog-footer">
        <el-button @click="cancelSubmitMenu">取 消</el-button>
        <el-button type="primary" @click="submitMenuForm">确 定</el-button>
      </span>
            </el-dialog>
        </div>
    </el-card>
</template>

<script>

    import {Message} from "element-ui";
    import {setTableStyle} from '@/common/utils'

    export default {
        name: 'menumanage',
        data() {
            return {

                menuForm: {
                    id: '',
                    menu_name: '',
                    menu_code: '',
                    type: '',
                    parent_id: '',
                    menu_level: ''
                },
                menuList: [],
                tableStyle:setTableStyle(),
                menuType: [

                    {
                        id: 1,
                        name: '菜单(需要显示成左侧菜单)',
                    },
                    {
                        id: 2,
                        name: '按钮(按钮没有页面)',
                    },
                    {
                        id: 3,
                        name: '路由(不需要显示在左侧但是有页面)',
                    }

                ],
                // 弹窗开关
                objVisible: {
                    isTop:true,//是否是添加顶级菜单
                    add: {
                        visible: false,
                        visibleTitle: '新增顶级菜单'
                    },
                },
                // 表单验证规则
                rules: {

                    menu_name: [
                        {
                            required: true,
                            message: '请填写菜单名称',
                            trigger: 'blur'
                        },
                    ],
                    menu_code: [
                        {
                            required: true,
                            message: '请填写菜单代码',
                            trigger: 'blur'
                        },
                    ],
                    type: [
                        {
                            required: true,
                            message: '请选择菜单类型',
                            trigger: 'blur'
                        },
                    ],
                },

                /***********************************/

            }
        },
        mounted() {
            this.search()
        },
        methods: {
            // 关闭弹窗时的回调
            onDialogClose() {
                //初始化表单数据
                this.menuForm = {

                    id: '',
                    menu_name: '',
                    menu_code: '',
                    type: '',
                    parent_id: '',
                    menu_level: ''
                }
                this.objVisible.isTop =true
            },

            handleNewMenu(type, id) {

                let typeObj = {1:"添加顶级菜单",2:"添加子菜单"}

                this.objVisible.isTop = type === 1 ? true : false;

                this.menuForm.type = type === 1 ? 1 : '';

                this.objVisible.add.visible = true
                this.objVisible.add.visibleTitle = typeObj[type]
                this.menuForm.parent_id = id;

            },
            handleEditMenu(type,row){

                this.objVisible.add.visible = true
                this.objVisible.add.visibleTitle = '编辑菜单'

                if(row.parent_id === 0){

                    this.objVisible.isTop = true

                }else{

                    this.objVisible.isTop = false
                }
                for (let key of Object.keys(this.menuForm)) {

                    this.menuForm[key] = row[key]
                }
            },

            deleteMenu(row){

                let _that = this
                this.$confirm('确定要删除该菜单么？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let data = {
                        id:row.id,
                    }
                    _that.$http.post('/SystermApi/deleteMenu',data).then(function (res) {

                        Message.success("成功！");
                        _that.search();

                    })

                })
            },
            cancelSubmitMenu() {
                this.objVisible.add.visible = false

            },
            // 提交表单
            submitMenuForm() {

                let _that = this

                _that.$refs['menuForm'].validate((valid) => {

                    if (valid) {
                        let data = {
                            id: _that.menuForm.id,
                            menu_name: _that.menuForm.menu_name,
                            menu_code: _that.menuForm.menu_code,
                            type: _that.menuForm.type,
                            parent_id: _that.menuForm.parent_id,

                        }
                        _that.$http.post('/SystermApi/createOrEditMenu', data).then(function (res) {

                            Message.success("成功！");
                            _that.objVisible.add.visible = false
                            _that.search()
                        })
                    }

                })

            },
            search() {
                let _that = this
                this.$http.get('/SystermApi/getFisrtMenu').then(res => {

                    _that.menuList = res

                })
            },

            /********************************************/


        }
    }
</script>
<style lang="scss" scoped>
    .block-icon {
        border: 1px solid #1ab394;
        border-radius: 2px;
        color: #1ab394;
        display: inline-block;
        height: 14px;
        line-height: 14px;
        text-align: center;
        vertical-align: middle;
        width: 14px;
    }

    .category-row {
        display: inline-block;
        width: 100%;

        i.el-icon-edit {
            color: #1ab394;
            display: none;
            margin-left: 20px;
            font-size: 16px;
        }

        &:hover {
            i {
                display: inline-block;
            }
        }
    }

    .putaway-header {
        font-size: 14px;
        color: #606266;
        margin-bottom: 10px;
    }

    .el-tag {
        margin-left: 10px;
        margin-bottom: 10px;
    }

    .button-new-tag {
        margin-left: 10px;
        height: 32px;
        line-height: 30px;
        padding-top: 0;
        padding-bottom: 0;
    }

    .input-new-tag {
        width: 90px;
        margin-left: 10px;
        vertical-align: bottom;
    }

    .header-title {
        margin-bottom: 10px;
        color: #606266;
    }

    .not-belong-Web {
        min-height: 450px;
    }

    .category-item {
        display: inline-block;
        min-width: 220px;
        vertical-align: top;

        .title {
            text-align: center;
            font-size: 16px;
            line-height: 35px;
        }

        .content {
            width: 100%;
            border: 1px solid #888;
            height: 750px;
            box-sizing: border-box;
            overflow-y: hidden;
            padding: 10px 0;
            border-radius: 3px;
        }
    }

    .category-list {
        border: 1px solid #ccc;
        height: 100%;
        box-sizing: border-box;
        overflow-y: auto;
        width: 200px;
        display: inline-block;
        margin: 0 5px;
        font-size: 0;

        li {
            padding: 5px;
            width: 100%;
            font-size: 14px;
            box-sizing: border-box;
            cursor: pointer;

            i {
                float: right;
            }

            &.active {
                background: #58b7ff;
                color: #fff !important;
            }
        }
    }

    .colorBlue {
        color: #58b7ff;
    }

    .translate-item {
        margin-bottom: 10px;

        .left {
            display: inline-block;
            margin-right: 10px;
            width: 80px;
            text-align: right;
        }

        .right {
            display: inline-block;
        }

        .category-word {
            display: inline-block;
            margin-right: 10px;
        }
    }
</style>
<style>
    .bgc-yellow input {
        background-color: #fff000;
    }
</style>
