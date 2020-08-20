<template>
    <div id="areaTree">
        <div class="box-title">
            <!--<a href="#">列表<i class="fa  fa-refresh" @click="freshArea"></i></a>-->

            <a href="#" @click="handlerAdd">添加根节点<i class="fa  fa-refresh"></i></a>
        </div>
        <div class="tree-box">
            <div class="zTreeDemoBackground left">
                <ul id="treeDemo" class="ztree"></ul>
            </div>
        </div>

        <el-dialog :title="objVisible.title" :visible.sync="objVisible.isShow" @close="onDialogClose()">
            <el-form :model="form" ref="form">
                <el-form-item label="名称" label-width="120px">
                    <el-input v-model="form.name"></el-input>
                </el-form-item>

            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="mini" @click="objVisible.isShow = false">取 消</el-button>
                <el-button size="mini" type="primary" @click="submitAdd">确 定</el-button>
            </div>
        </el-dialog>
    </div>
</template>

<script>
    export default {
        name: "ztree",
        props: {
            httpUrl: String,
        },
        data: function () {
            return {
                objVisible: {

                    title: "添加根节点部门",
                    isShow: false,
                },
                form: {
                    name: '',

                },
                setting: {
                    data: {
                        simpleData: {
                            enable: true,
                            idKey: "id",
                            pIdKey: "parent_id",
                            rootPId: 0
                        }
                    },
                    edit: {
                        editNameSelectAll: true,
                        enable: true,
                        showRemoveBtn: true,
                        showRenameBtn: true,
                        removeTitle: "删除",
                        renameTitle: "编辑名称"
                    },
                    view: {
                        addHoverDom: this.addHoverDom,
                        removeHoverDom: this.removeHoverDom,

                    },
                    callback: {
                        beforeRename: this.beforeRename,
                        beforeRemove: this.beforeRemove,
                    }
                },
                zNodes: [],

            }
        },
        methods: {
            onDialogClose() {
            },
            handlerAdd() {
                this.objVisible.isShow = true
            },

            freshArea: function () {
                this.getDataNode();
            },
            //获取树节点
            getDataNode() {
                let _that = this;
                let url = _that.httpUrl;
                _that.$http.post(url, '').then(function (res) {
                    _that.zNodes = res;
                    let treeObj = $.fn.zTree.init($("#treeDemo"), _that.setting, _that.zNodes);
                    treeObj.expandAll(true);
                })
            },
            //编辑名称
            beforeRename(treeId, treeNode, newName, isCancel) {
                let _that = this;
                let url = '/SystermApi/editDeptName';
                let data = {
                    id: treeNode.id,
                    name: newName
                }
                _that.$http.post(url, data).then(function (res) {
                    return true;
                })
            },
            //删除
            beforeRemove(treeId, treeNode) {
                this.deleteCat(treeNode);
                return false;

            },
            deleteCat(treeNode) {

                let _that = this;
                let url = "/SystermApi/deleteDept"
                let data = {
                    id: treeNode.id
                }

                _that.$http.post(url, data).then((res) => {
                    var zTree = $.fn.zTree.getZTreeObj("treeDemo");
                    zTree.removeNode(treeNode, false);
                })
            },
            addHoverDom(treeId, treeNode) {
                let _that = this;
                var sObj = $("#" + treeNode.tId + "_span");
                if (treeNode.editNameFlag || $("#addBtn_" + treeNode.tId).length > 0) return;
                var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
                    + "' title='添加新节点' onfocus='this.blur();'></span>";
                if (treeNode.level === 2) return;
                sObj.after(addStr);
                var btn = $("#addBtn_" + treeNode.tId);
                if (btn) btn.bind("click", function () {
                    _that.$prompt('请输入名称', '提示', {
                        confirmButtonText: '确定',
                        cancelButtonText: '取消',
                    }).then(({value}) => {
                        let url = "/SystermApi/createDept";
                        let data = {
                            name: value,
                            parent_id: treeNode.id,
                            deep_code: treeNode.deep_code
                        }
                        _that.$http.post(url, data).then(function (res) {
                            let newNode = res;
                            var zTree = $.fn.zTree.getZTreeObj("treeDemo");
                            zTree.addNodes(treeNode, newNode);

                        })
                    }).catch(() => {
                        _that.$message({
                            type: 'info',
                            message: '取消输入'
                        });
                    });

                });
            },
            //增加根节点
            submitAdd() {
                let _that = this
                let value = this.form.name
                let url = "/SystermApi/createDept";
                let data = {
                    name: value,
                    parent_id: 0,
                    deep_code: ''
                }
                _that.$http.post(url, data).then(function (res) {
                    let newNode = res;
                    var zTree = $.fn.zTree.getZTreeObj("treeDemo");
                    zTree.addNodes(null, newNode);
                    _that.objVisible.isShow = false

                })

            },
            removeHoverDom(treeId, treeNode) {
                $("#addBtn_" + treeNode.tId).unbind().remove();
            }
        },
        mounted() {
            this.getDataNode();
        }
    }


</script>

<style scoped>
    #areaTree {
        border: 1px solid #e5e5e5;
        margin-bottom: 2px;
        border-radius: 4px;
        overflow: hidden;
    }

    .box-title {
        border-radius: 3px 3px 0 0;
        background-color: #f5f5f5;
    }

    .box-title a {
        color: #2fa4e7;
        text-decoration: none;
        font-size: 14px;
        display: block;
        padding: 8px 15px;
        cursor: pointer;
    }

    .box-title .fa {
        float: right;
        line-height: 20px;
    }


</style>
