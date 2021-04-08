// 新增申请
<template>
    <el-card class="content">
        <div class="title">新增信息设备配件申请表</div>
        <el-form :model="form">
            <el-row>
                <el-col :span="12">
                    <el-form-item label="申领单位：" :label-width="formLabelWidth">
                        <el-select v-model="value" placeholder="请选择">
                            <el-option
                                v-for="item in options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                </el-col>
                <el-col :span="12">
                    <el-form-item label="申请人：" :label-width="formLabelWidth">
                        <el-col :span="10">
                            <el-input v-model="form.person" autocomplete="off"></el-input>
                        </el-col>
                    </el-form-item>
                </el-col>
            </el-row>
            <el-form-item label="申请物品：" :label-width="formLabelWidth">
                <el-button type="primary" icon="el-icon-plus" circle size="mini" @click="goodsAdd"></el-button>
            </el-form-item>

            <!-- <div class="goods-item">
                <el-row>
                    <el-col :span="8">
                        <el-form-item label="名称" :label-width="formLabelWidth">
                            <el-select v-model="value" placeholder="请选择">
                                <el-option
                                    v-for="item in options"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                    <el-col :span="16">
                        <el-form-item label="规格" :label-width="formLabelWidth">
                            <el-select v-model="value" placeholder="请选择">
                                <el-option
                                    v-for="item in options"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value">
                                </el-option>
                            </el-select>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row>
                    <el-col :span="8">
                        <el-form-item label="数量" :label-width="formLabelWidth">
                            <el-input v-model="form.number" autocomplete="off"></el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :span="16">
                        <el-form-item label="备注" :label-width="formLabelWidth">
                            <el-input v-model="form.remark" autocomplete="off"></el-input>
                        </el-form-item>
                    </el-col>
                </el-row>
                <el-row class="delele-item">
                    <el-button type="danger" icon="el-icon-close" plain circle size="mini"></el-button>
                </el-row>
            </div> -->

            <!-- 新增物品信息列表 -->
            <div class="goods-table">
                <el-table
                    :data="goodsTableData"
                    style="width: 100%">
                    <el-table-column
                        prop="name"
                        label="名称"
                        width="180">
                    </el-table-column>
                    <el-table-column
                        prop="type"
                        label="类型"
                        width="180">
                    </el-table-column>
                    <el-table-column
                        prop="number"
                        label="数量">
                    </el-table-column>
                    <el-table-column
                        prop="remark"
                        label="备注">
                    </el-table-column>
                </el-table>
            </div>

            <el-upload
                class="upload-demo"
                drag
                action="https://jsonplaceholder.typicode.com/posts/">
                <i class="el-icon-upload"></i>
                <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                <div class="el-upload__tip" slot="tip">只能上传jpg/png文件，且不超过500kb</div>
            </el-upload>

            <el-form-item class="btn-group">
                <el-button type="primary" @click="onSubmit">确定</el-button>
                <el-button @click="goBack">取消</el-button>
            </el-form-item>
        </el-form>

        <!-- 新增申请物品弹窗 -->
        <div>
            <el-dialog title="新增物品信息" :visible.sync="dialogFormVisible">
                <el-form :model="form">
                    <el-form-item label="物品名称" :label-width="formLabelWidth">
                        <el-select v-model="form.name" placeholder="请选择">
                            <el-option
                                v-for="item in options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="物品规格" :label-width="formLabelWidth">
                        <el-select v-model="form.type" placeholder="请选择">
                            <el-option
                                v-for="item in options"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="物品数量" :label-width="formLabelWidth">
                        <el-input v-model="form.number" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="备注" :label-width="formLabelWidth">
                        <el-input v-model="form.remark" autocomplete="off"></el-input>
                    </el-form-item>
                </el-form>
                <div slot="footer" class="dialog-footer">
                    <el-button @click="dialogFormVisible = false">取 消</el-button>
                    <el-button type="primary" @click="dialogFormVisible = false">确 定</el-button>
                </div>
            </el-dialog>
        </div>
    </el-card>
</template>

<script>
export default {
    data() {
        return {
            dialogTableVisible: false,
            dialogFormVisible: false,
            form: {
                date: "2021/04/07",
                person: "",
                number: "",
                remark: ""
            },
            formLabelWidth: "120px",
            options: [{
                value: '选项1',
                label: '黄金糕'
                }, {
                value: '选项2',
                label: '双皮奶'
                }, {
                value: '选项3',
                label: '蚵仔煎'
                }, {
                value: '选项4',
                label: '龙须面'
                }, {
                value: '选项5',
                label: '北京烤鸭'
            }],
            value: '',
            goodsTableData: [],
        }
    },
    methods: {
        goodsAdd() {
            this.dialogFormVisible = true;
        },
        onSubmit() {
            console.log("提交");
        },
        goBack() {
            this.$router.go(-1);
        }
    }
}
</script>

<style>
.content {
    padding-left: 50px;
    padding-right: 50px;
}
.title {
    text-align: center;
    font-size: 30px;
    margin-top: 30px;
    margin-bottom: 60px;
}
/* .goods-item {
    margin-left: 40px;
    padding-right: 30px;
    padding-top: 25px;
    padding-bottom: 15px;
    border: 1px solid #e5e5e5 !important;
}
.delele-item {
    text-align: center;
} */
.goods-table {
    padding-left: 60px;
    padding-right: 60px;
}
.upload-demo {
    margin-top: 80px;
    text-align: center;
}
.btn-group {
    margin-top: 80px;
    text-align: center;
}
</style>