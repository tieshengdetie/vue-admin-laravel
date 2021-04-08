// 配件申请列表
<template>
    <el-card>
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" class="fl" size="mini">
                <el-form-item label="申领单位">
                    <el-input v-model="searchData.department" placeholder="申领单位"
                        @keyup.enter.native="onSearch()"></el-input>
                </el-form-item>
                <el-form-item label="申领人">
                    <el-input v-model="searchData.person" placeholder="申领人"></el-input>
                </el-form-item>
                <el-form-item label="申领时间">
                    <el-date-picker
                        placeholder="申领时间"
                        v-model="searchData.date"
                        type="date"
                        value-format="timestamp"
                        style="width: 100%;">
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="申请单状态">
                    <el-input v-model="searchData.status_name" placeholder="申请单状态"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="text" @click="handleReset" size="mini">重置</el-button>
                    <el-button type="primary" icon="el-icon-search" @click="handleSearch" size="mini">查询</el-button>
                </el-form-item>
            </el-form>
            <div>
                <el-button type="success" icon="el-icon-plus" size="small" @click="addApply()">
                    新增申请
                </el-button>
            </div>
            
        </div>
        <!-- <div class="search-bar">
            <el-row :gutter="20">
                <el-col :span="4">
                    <el-select v-model="value" filterable clearable placeholder="请选择">
                        <el-option
                            v-for="item in options"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value">
                        </el-option>
                    </el-select>
                </el-col>
                <el-col :span="2">
                    <el-button type="primary" icon="el-icon-search" @click="search()">
                        搜索
                    </el-button>
                </el-col>
                <el-col :span="2" :offset="14">
                    <el-button type="success" icon="el-icon-plus" @click="addApply()">
                        新增申请
                    </el-button>
                </el-col>
            </el-row>
        </div> -->
        <!-- <div class="add-bar">
            <el-button type="success" icon="el-icon-plus" size="small" @click="addApply()">
                新增申请
            </el-button>
        </div> -->
        <div>
            <el-table
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
                    prop="department"
                    label="申领单位"
                    min-width="130">
                </el-table-column>

                <el-table-column
                    prop="person"
                    label="申领人"
                    width="130">
                </el-table-column>

                <el-table-column
                    prop="date"
                    label="申领时间"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="status_name"
                    label="状态"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="file"
                    label="证明"
                    width="130">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="200">
                    <template slot-scope="scope">
                        <div>
                            <el-button type="primary" size="mini" @click="goToDetail(scope.row)">
                                详情
                            </el-button>
                            <el-button v-if="scope.row.status_name==='已核准'" type="success" size="mini" @click="receive(scope.row)">
                                领用
                            </el-button>
                            <el-button v-if="scope.row.status_name==='未核准'" type="danger" size="mini" @click="handleDelete(scope.row)">
                                删除
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
            </el-table>
            <div class="pagination-bar">
                <el-pagination
                    size="mini"
                    :page-sizes="[10, 15, 20, 50]"
                    :page-size="page.pageSize"
                    layout="total, sizes, prev, pager, next, jumper"
                    :total="page.total">
                </el-pagination>
            </div>
        </div>

        <!-- 领用弹框 -->
        <div>
            <el-dialog title="确认领用单" :visible.sync="dialogFormVisible" class="receive-dialog">
                <el-form :model="form">
                    <el-form-item label="领用日期" :label-width="formLabelWidth">
                        <el-input v-model="form.date" autocomplete="off" :disabled="true"></el-input>
                    </el-form-item>
                    <el-form-item label="领用人" :label-width="formLabelWidth">
                        <el-input v-model="form.person" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-upload
                        class="upload-demo"
                        drag
                        action="https://jsonplaceholder.typicode.com/posts/"
                        multiple>
                        <i class="el-icon-upload"></i>
                        <div class="el-upload__text">将文件拖到此处，或<em>点击上传</em></div>
                        <div class="el-upload__tip" slot="tip">只能上传jpg/png文件，且不超过500kb</div>
                    </el-upload>
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
import { setTableStyle } from '@/common/utils'
export default {
    name: "apply-list",
    data() {
        return {
            searchData: {
                department: '',
                person: '',
                date:'',
                status_name:''
            },
            // options: [{
            //     value: '选项1',
            //     label: '申领单位'
            // }, {
            //     value: '选项2',
            //     label: '申领人'
            // }, {
            //     value: '选项3',
            //     label: '申领时间'
            // }, {
            //     value: '选项4',
            //     label: '状态'
            // }, ],
            // value: '',
            tableData: [
                {
                    department: "质检部",
                    person: "胡",
                    date: "2021/03/04",
                    status_name: "已发放",
                    file: ""
                },
                {
                    department: "开源厂",
                    person: "王",
                    date: "2021/04/05",
                    status_name: "已核准",
                    file: ""
                },
                {
                    department: "信息中心",
                    person: "李",
                    date: "2021/04/06",
                    status_name: "未核准",
                    file: ""
                },
                
            ],
            tableStyle: setTableStyle(),
            page: {
                currentPage: 1,
                total: 0,
                pageSize: 20
            },
            // delItem: {},
            dialogTableVisible: false,
            dialogFormVisible: false,
            form: {
                date: "2021/04/07",
                person: ""
            },
            formLabelWidth: "120px"
        }
    },
    mounted() {
        this.page.total = this.tableData.length
    },
    methods: {
        handleReset() {
            this.searchData = {
                department: '',
                person: '',
                date:'',
                status_name:''
            };
        },
        handleSearch() {
            console.log("搜索");
        },
        // search() {
        //     console.log("搜索");
        // },
        addApply() {
            this.$router.push({
                path: "/goods/applyAdd",
            })
        },
        goToDetail(item) {
            console.log(item);
            this.$router.push({
                path: "/goods/applyDetail",
                query: {
                    status: item.status_name,
                }
            })
        },
        receive() {
            // console.log("弹出领取弹框");
            this.dialogFormVisible = true
        },
        handleDelete(item) {
            // this.delItem = item;
            this.openDelDialog();
            // console.log(item);
        },
        
        openDelDialog() {
            this.$confirm('是否删除该申请?', '提示', {
                confirmButtonText: '确定',
                cancelButtonText: '取消',
                type: 'warning'
                }).then(() => {
                //TODO delete
                this.$message({
                    type: 'success',
                    message: '删除成功!'
                });
            }).catch(() => {
                this.$message({
                    type: 'info',
                    message: '已取消删除'
                });          
            });
        },
        
    }
}
</script>

<style>
.search-bar {
    margin-bottom: 15px;
}
.add-bar {
    text-align: right;
}
/* .add-bar {
    margin-bottom: 20px;
} */
.el-form-item__content {
    margin-right: 40px;
}
.upload-demo {
    text-align: center;
}
</style>