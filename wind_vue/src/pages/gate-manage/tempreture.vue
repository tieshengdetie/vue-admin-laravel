<template>
    <el-card class="box-card">
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" class="fl" size="mini">

                <el-form-item label="网关序列号">
                    <el-input v-model="searchData.gw_id" placeholder="网关序列号"
                              @keyup.enter.native="onSearch()"></el-input>
                </el-form-item>
                <el-form-item label="传感器ID">
                    <el-input v-model="searchData.sn_id" placeholder="传感器ID"></el-input>
                </el-form-item>
                <el-form-item label="开始时间">

                    <el-date-picker
                        placeholder="开始时间"
                        v-model="searchData.start_time"
                        type="datetime"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        style="width: 100%;">

                    </el-date-picker>


                </el-form-item>
                <el-form-item label="结束时间">


                    <el-date-picker
                        v-model="searchData.end_time"
                        type="datetime"
                        placeholder="结束时间"
                        value-format="yyyy-MM-dd HH:mm:ss"
                        style="width: 100%;">

                    </el-date-picker>

                </el-form-item>

                <el-form-item>
                    <el-button type="text" @click="handleReset" size="mini">重置</el-button>
                    <el-button type="primary" icon="el-icon-search" @click="initList" size="mini">查询</el-button>
                </el-form-item>
            </el-form>

        </div>

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
                    prop="gw_id"
                    label="网关序列号"
                    min-width="250">
                </el-table-column>
                <el-table-column
                    prop="sn_id"
                    label="传感器ID"
                    width="200">
                </el-table-column>
                <el-table-column
                    prop="ST"
                    label="类型"
                    width="100">
                </el-table-column>
                <el-table-column
                    prop="ip"
                    label="发送ip"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="temp"
                    label="数值"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="create_time"
                    label="发送时间"
                    width="230">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="300"
                    >
                    <template slot-scope="scope">
                        <div>
                            <el-button
                                type="primary"
                                size="mini"
                                @click=""
                            >配置
                            </el-button>
                            <el-button
                                type="success"
                                size="mini"
                                @click=""
                            >发送
                            </el-button>
                            <el-button
                                type="warning"
                                size="mini"
                                @click=""
                            >本日统计
                            </el-button>
                            <el-button
                                type="danger"
                                size="mini"
                                @click=""
                            >删除
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
    import {setTableStyle} from '@/common/utils'
    export default {
        name: "tempreture",
        data() {
            return {
                searchData: {
                    gw_id: '',
                    sn_id: '',
                    start_time:'',
                    end_time:''

                },
                page: {
                    currentPage: 1,
                    total: 0,
                    pageSize: 20,
                },
                tableData: [],
                tableStyle:setTableStyle(),

            }
        },
        mounted() {

            this.initList()
        },
        methods: {

            handleReset() {
                this.searchData = {
                    gw_id: '',
                    sn_id: '',
                    startTime:'',
                    endTime:''
                }
            },
            handleSizeChange(val) {
                this.page.pageSize = val
                this.initList();
            },
            handleCurrentChange(val) {
                this.page.currentPage = val
                this.initList();
            },
            initList() {
                let _that = this

                let searchData = this.searchData;

                searchData.page = this.page.currentPage

                searchData.pageSize = this.page.pageSize

                _that.$http.post('/SystermApi/getTempData', searchData, true).then(function (res) {

                    _that.page.pageSize = res.per_page
                    _that.page.total = res.total
                    _that.tableData = res.data

                })

            },
        },
    }
</script>

<style scoped>

</style>
