<template>
    <el-card class="box-card">

        <div>
            <el-table
                :data="tableData"
                border
                size="small"
                highlight-current-row
                style="width: 100%">

                <el-table-column
                    prop="file_name"
                    label="文件名称"
                    min-width="120">
                </el-table-column>

<!--                <el-table-column-->
<!--                    prop="file_url"-->
<!--                    label="文件地址"-->
<!--                    min-width="130">-->
<!--                </el-table-column>-->

                <el-table-column
                    prop="create_time"
                    label="创建时间"
                    width="180">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="100">
                    <template slot-scope="scope">
                        <div>

                            <el-button
                                size="mini"
                                type="primary"
                                @click="downloadLog(scope.row)"
                            >下载
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
    </el-card>
</template>

<script>
    export default {
        name: "gatelog",

        props:{
            gw_id: String,
        },
        mounted() {

            // this.getFile()
        },
        data(){

            return {
                tableData:[],
                page: {
                    currentPage: 1,
                    total: 0,
                    pageSize: 20,
                },
            }
        },
        methods:{

            getFile(){
                let _that = this
                let gw_id = this.gw_id
                let data = {
                    gw_id:gw_id,
                    pageSize:this.page.pageSize
                }

                _that.$http.post('/SystermApi/getLogFile',data).then(function (res) {

                    _that.page.pageSize = res.per_page
                    _that.page.total = res.total
                    _that.tableData = res.data
                })
            },
            downloadLog(row){

                let _that = this

                let data = {
                    id:row.id
                }
                window.location.href = '/api/SystermApi/downloadLog?id='+row.id;

                // _that.$http.post('/SystermApi/downloadLog',data).then(function (res) {
                //
                //     console.log(res)
                //
                //     blob = new Blob([res])
                //
                //     var a = document.createElement('a');
                //
                //     a.download = 'data.xlsx';
                //
                //     a.href=window.URL.createObjectURL(blob)
                //
                //     a.click()
                // })

            },
            handleSizeChange(val) {
                this.page.pageSize = val
                this.getFile();
            },
            handleCurrentChange(val) {
                this.page.currentPage = val
                this.getFile();
            },

        },
    }
</script>

<style scoped>

</style>
