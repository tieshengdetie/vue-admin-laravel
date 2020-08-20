<template>
    <el-card class="box-card">
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" class="fl" size="mini">

                <el-form-item label="网关序列号">
                    <el-input v-model="searchData.gw_id" placeholder="网关序列号"
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
                    prop="ip"
                    label="发送ip"
                    width="130">
                </el-table-column>
                <el-table-column
                    prop="status_name"
                    label="心跳状态"
                    width="100">
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
                    prop="update_time"
                    label="心跳更新时间"
                    width="180">
                </el-table-column>
                <el-table-column
                    label="操作"
                    fixed="right"
                    width="500">
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
                                type="success"
                                @click="goToLog(scope.row)"
                                v-if="hasPermission('gate-log')"
                            >查看log文件
                            </el-button>
                            <el-button
                                size="mini"
                                type="success"
                                @click="sendLog(scope.row)"
                            >发送log
                            </el-button>
                            <el-button
                                size="mini"
                                type="success"
                                @click="gwReboot(scope.row)"
                            >重启
                            </el-button>
                            <el-button
                                size="mini"
                                type="success"
                                @click="backConnect(scope.row)"
                            >反向连接
                            </el-button>
                            <!--                            <el-button-->
                            <!--                                size="mini"-->
                            <!--                                type="danger"-->
                            <!--                                @click=""-->
                            <!--                            >删除-->
                            <!--                            </el-button>-->
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

        <el-dialog title="配置网关" :visible.sync="visible.configVisible" @close="onDialogClose()">
            <el-form :model="configForm" ref="form">
                <el-form-item label="重启时间" label-width="120px">
                    <!--                    <el-input v-model="configForm.reboot_time"></el-input>-->
                    <el-time-picker
                        v-model="configForm.reboot_time"
                        clearable
                        value-format="HH:mm:ss"
                        placeholder="请选择时间">
                    </el-time-picker>
                </el-form-item>

                <el-form-item label="心跳开关" label-width="120px">
                    <el-radio-group v-model="configForm.hb_status" size="mini">
                        <el-radio-button label="1">开</el-radio-button>
                        <el-radio-button label="0">关</el-radio-button>

                    </el-radio-group>

                </el-form-item>

                <el-form-item label="数据开关" label-width="120px">
                    <el-radio-group v-model="configForm.data_status" size="mini">
                        <el-radio-button label="1">开</el-radio-button>
                        <el-radio-button label="0">关</el-radio-button>
                    </el-radio-group>

                </el-form-item>


            </el-form>
            <div slot="footer" class="dialog-footer">
                <el-button size="mini" @click="visible.configVisible = false">取 消</el-button>
                <el-button size="mini" type="primary" @click="submitConfig()">确 定</el-button>
            </div>
        </el-dialog>

        <el-dialog title="日志文件列表" :visible.sync="visible.logVisible" @opened="openDialog" @close="onDialogClose()">
            <gatelog :gw_id="gw_id" ref="gate"></gatelog>
        </el-dialog>
    </el-card>
</template>

<script>
    import {setTableStyle} from '@/common/utils'
    import {Message} from "element-ui";
    import gatelog from './gatelog.vue'

    export default {
        name: "gatelist",
        components:{gatelog},
        data() {
            return {
                searchData: {
                    gw_id: '',


                },
                timer: '',
                visible: {

                    configVisible: false,
                    logVisible: false,

                },
                configForm: {

                    id: '',
                    gw_id: '',
                    reboot_time: '',
                    hb_status: 1,
                    data_status: 1

                },
                page: {
                    currentPage: 1,
                    total: 0,
                    pageSize: 20,
                },
                tableData: [],
                tableStyle: setTableStyle(),

                gw_id: '',

            }
        },
        mounted() {

            this.initList()

            this.timer = setInterval(this.initList, 30000);
        },

        beforeDestroy() {

            clearInterval(this.timer);
        },
        methods: {

            goToLog(row) {

                this.visible.logVisible = true
                this.gw_id = row.gw_id
                // this.$router.replace('/gate/gatelog')
            },
            openDialog(){

                this.$refs.gate.getFile()

            },

            backConnect(row){

                let _that = this
                let data ={gw_id:row.gw_id}

                _that.$http.post('/SystermApi/backConnect',data).then(function (res) {

                    Message.success("已经通知网关开启连接！")

                })


            },

            sendLog(row) {

                let _that = this

                let data = {gw_id: row.gw_id}

                _that.$http.post('/SystermApi/sendLog', data).then(function (res) {

                    Message.success("已经通知网关发送日志文件！")
                })
            },

            gwReboot(row) {

                let _that = this

                let data = {gw_id: row.gw_id}

                _that.$http.post('/SystermApi/gwReboot', data).then(function (res) {

                    Message.success("已经通知网关重启！")
                })

            },
            submitConfig() {

                let _that = this

                _that.$http.post('/SystermApi/configGw', this.configForm).then(function (res) {

                    _that.visible.configVisible = false

                    Message.success("配置成功！")

                    _that.initList()

                })

            },
            onDialogClose() {

                this.gw_id = ''
            },

            hanldeConfig(row) {

                this.configForm = {
                    id: row.id,
                    gw_id: row.gw_id,
                    reboot_time: row.reboot_time,
                    hb_status: row.hb_status,
                    data_status: row.data_status

                }

                this.visible.configVisible = true
            },

            handleReset() {
                this.searchData = {
                    gw_id: '',

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

                _that.$http.post('/SystermApi/getGateList', searchData, true).then(function (res) {

                    _that.tableData = res

                })

            },
        },
    }
</script>

<style scoped>

</style>
