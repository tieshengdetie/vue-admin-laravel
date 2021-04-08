// 配件申请详情页
<template>
    <el-card>
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
                    prop="index"
                    label="序号"
                    min-width="80">
                </el-table-column>

                <el-table-column
                    prop="department"
                    label="申领单位"
                    min-width="130">
                </el-table-column>

                <el-table-column
                    prop="name"
                    label="物品名称"
                    width="130">
                </el-table-column>

                <el-table-column
                    prop="type"
                    label="物品规格"
                    width="130">
                </el-table-column>

                <el-table-column
                    prop="number"
                    label="物品数量"
                    width="130">
                </el-table-column>

                <el-table-column
                    prop="remark"
                    label="备注"
                    width="200">
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
        <div class="btn-group">
            <el-button v-if="this.$route.query.status==='未核准'" type="primary" @click="approval">核准</el-button>
            <el-button @click="goBack">返回</el-button>
        </div>
    </el-card>
</template>

<script>
import { setTableStyle } from '@/common/utils'
export default {
    name: "apply-detail",
    data() {
        return {
            tableData: [
                {
                    department: "质检部",
                    name: "鼠标",
                    type: "双飞燕",
                    number: 5,
                    remark: ""
                },
                {
                    department: "质检部",
                    name: "键盘",
                    type: "双飞燕",
                    number: 3,
                    remark: ""
                },
                
            ],
            tableStyle: setTableStyle(),
            page: {
                currentPage: 1,
                total: 0,
                pageSize: 20
            }
        }
    },
    mounted() {
        this.page.total = this.tableData.length;
    },
    methods: {
        approval() {
            console.log(this.$route.query.status);
        },
        goBack() {
            this.$router.go(-1);
        }
    }
}
</script>
<style>
.btn-group {
    margin-top: 80px;
    text-align: center;
}
</style>