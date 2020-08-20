<template lang="html">
    <el-card class="box-card">
        <div>
            <div style="margin-bottom:10px;">
                <el-button type="success" icon="el-icon-plus" size="mini" @click="handleNewMenu(1,0)">新增顶级部门</el-button>
            </div>

            <div class="custom-tree-container">

                <div class="block">

                    <el-tree
                        :data="menuList"
                        node-key="id"
                        default-expand-all
                        :expand-on-click-node="false">
                          <span class="custom-tree-node" slot-scope="{ node, data}">
                            <span>{{ data.name }}</span>
                            <span>
                              <el-button type="text" size="mini" @click="() => handleNewMenu(2,data.id)">
                                添加子部门
                              </el-button>
                                <el-button type="text" size="mini" @click="() => handleEditMenu(3,data)">
                                编辑
                              </el-button>
                              <el-button type="text" size="mini" @click="() => removeNode(data)">
                                删除
                              </el-button>
                            </span>
                          </span>
                    </el-tree>
                </div>
            </div>

            <!-- 新增类目弹窗 -->
            <el-dialog
                :title="objVisible.add.visibleTitle"
                :visible.sync="objVisible.add.visible"
                @close="onDialogClose()"
                width="50%">
                <el-form label-width="100px" :rules="rules" ref="menuForm" :model="menuForm">
                    <el-row>
                        <el-col :span="24">
                            <el-form-item label="部门名称" prop="name">
                                <el-input v-model="menuForm.name"></el-input>
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
                    name: '',
                    parent_id: '',
                    deep_code: '',
                },
                menuList: [],
                tableStyle: setTableStyle(),
                // 弹窗开关
                objVisible: {
                    isTop: true,//是否是添加顶级菜单
                    add: {
                        visible: false,
                        visibleTitle: '新增顶级部门'
                    },
                },
                // 表单验证规则
                rules: {

                    name: [
                        {
                            required: true,
                            message: '请填部门名称',
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
            addChild(){


            },

            removeNode(dept){

                let _that = this
                this.$confirm('确定要删除该部门么？', '提示', {
                    confirmButtonText: '确定',
                    cancelButtonText: '取消',
                    type: 'warning'
                }).then(() => {
                    let data = {
                        id:dept.id,
                    }
                    _that.$http.post('/SystermApi/deleteDept',data).then(function (res) {

                        Message.success("成功！");
                        _that.search();

                    })

                })

            },
            // 关闭弹窗时的回调
            onDialogClose() {
                //初始化表单数据
                this.menuForm = {

                    id: '',
                    name: '',
                    parent_id: '',
                    deep_code: '',

                }
                this.objVisible.isTop = true
            },

            handleNewMenu(type, id) {

                let typeObj = {1: "添加顶级部门", 2: "添加子部门"}

                this.objVisible.isTop = type === 1 ? true : false;

                this.menuForm.type = type === 1 ? 1 : '';

                this.objVisible.add.visible = true
                this.objVisible.add.visibleTitle = typeObj[type]
                this.menuForm.parent_id = id;

            },
            handleEditMenu(type, row) {

                this.objVisible.add.visible = true
                this.objVisible.add.visibleTitle = '编辑部门'

                if (row.parent_id === 0) {

                    this.objVisible.isTop = true

                } else {

                    this.objVisible.isTop = false
                }
                for (let key of Object.keys(this.menuForm)) {

                    this.menuForm[key] = row[key]
                }
            },


            cancelSubmitMenu() {
                this.objVisible.add.visible = false

            },
            // 提交表单
            submitMenuForm() {

                let _that = this

                _that.$refs['menuForm'].validate((valid) => {

                    if (valid) {

                        _that.$http.post('/SystermApi/createOrEditDept', _that.menuForm).then(function (res) {

                            Message.success("成功！");
                            _that.objVisible.add.visible = false
                            _that.search()
                        })
                    }

                })

            },
            search() {
                let _that = this
                this.$http.get('/SystermApi/getDeptData').then(res => {

                    _that.menuList = res

                })
            },

            /********************************************/


        }
    }
</script>

<style>
    .custom-tree-node {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 14px;
        padding-right: 8px;
    }
</style>
