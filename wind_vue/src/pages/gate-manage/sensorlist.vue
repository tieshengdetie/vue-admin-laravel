<template>
    <el-card class="box-card">
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" class="fl" size="mini">

                <el-form-item label="传感器ID">
                    <el-input v-model="searchData.sn_id" placeholder="网关序列号"
                              @keyup.enter.native="initList"></el-input>

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

<!--                <el-table-column-->
<!--                    prop="id"-->
<!--                    label="ID"-->
<!--                    width="60">-->
<!--                </el-table-column>-->

                <el-table-column
                    prop="gw_id"
                    label="网关序列号"
                    min-width="120">
                </el-table-column>

                <el-table-column
                    prop="sn_id"
                    label="传感器Id"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="status_name"
                    label="状态"
                    width="130">
                    <template slot-scope="scope">
                        <div>

                            <el-button v-if="scope.row.status===false" type="danger" size="mini">
                                {{scope.row.status_name}}
                            </el-button>
                            <el-button v-if="scope.row.status===true" type="success" size="mini">
                                {{scope.row.status_name}}
                            </el-button>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column
                    prop="temp"
                    label="最新数值"
                    width="130">
                </el-table-column>

                <el-table-column
                    prop="update_time"
                    label="数据更新时间"
                    width="180">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="200">
                    <template slot-scope="scope">
                        <div>

                            <el-button
                                size="mini"
                                type="primary"
                                @click="hanldeConfig(scope.row)"
                            >配置
                            </el-button>

                            <el-button
                                size="mini"
                                type="danger"
                                @click="goToGraph(scope.row)"
                            >数据统计
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

        <!--        配置弹窗-->

        <el-dialog title="传感器配置" :visible.sync="visible.configVisible" @close="onDialogClose()">
            <el-form :model="configForm" ref="form">
                <el-form-item label="网关序列号" label-width="150px">
                                       <span>{{configForm.gw_id}}</span>

                </el-form-item>

                <el-form-item label="传感器ID" label-width="150px">
                    <span>{{configForm.sn_id}}</span>

                </el-form-item>

                <el-form-item label="传感器类型" label-width="150px">
                    <span>{{configForm.ST}}</span>

                </el-form-item>
                <el-form-item label="心跳Api地址" label-width="150px">
                    <el-input v-model="configForm.hb_api_address"></el-input>

                </el-form-item>
                <el-form-item label="DataApi地址" label-width="150px">
                    <el-input v-model="configForm.data_api_address"></el-input>

                </el-form-item>
                <el-form-item label="心跳发送时间间隔" label-width="150px">
                    <el-input v-model="configForm.hb_interval"></el-input>

                </el-form-item>
                <el-form-item label="Data发送时间间隔" label-width="150px">
                    <el-input v-model="configForm.data_interval"></el-input>

                </el-form-item>
                <el-form-item label="版本" label-width="150px">
                    <el-input v-model="configForm.version"></el-input>

                </el-form-item>


            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="mini" @click="visible.configVisible = false">取 消</el-button>
                <el-button size="mini" type="primary" @click="submitConfig()">确 定</el-button>
            </div>
        </el-dialog>
    </el-card>
</template>

<script>
    import {setTableStyle} from '@/common/utils'
    import {Message} from "element-ui";
    export default {
        name: "tempreture",
        data() {
            return {
                searchData: {

                    sn_id: '',

                },
                timer:'',
                visible:{

                    configVisible:false,

                },
                configForm:{

                    id:'',
                    gw_id:'',
                    sn_id:'',
                    ST:'',
                    hb_api_address:'',
                    data_api_address:'',
                    hb_interval:'',
                    data_interval:'',
                    version:''


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

            // this.timer = setInterval(this.initList,30000);
        },

        beforeDestroy() {

            // clearInterval(this.timer);
        },
        methods: {

            submitConfig(){

                let _that = this

                _that.$http.post('/SystermApi/configSensor',this.configForm).then(function (res) {

                    _that.visible.configVisible = false

                    Message.success("配置成功！")

                    _that.initList()

                })

            },

            goToGraph(row){

                this.$router.replace('/gate/datagraph')

                this.$router.push({
                    path: 'datagraph',
                    query: {
                        gw_id: row.gw_id,
                        sn_id: row.sn_id
                    }
                })
            },
            onDialogClose() {


            },

            hanldeConfig(row){


                for(let key of Object.keys(this.configForm)){

                    this.configForm[key] = row[key]
                }


                this.visible.configVisible = true
            },

            handleReset() {
                this.searchData = {

                    sn_id: '',

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

                _that.$http.post('/SystermApi/getSensorList', searchData, true).then(function (res) {

                    _that.tableData = res.data || []
                    _that.page.pageSize = res.per_page
                    _that.page.total = res.total

                })

            },
        },
    }
</script>

<style scoped>

</style>
