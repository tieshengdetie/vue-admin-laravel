<template>
    <el-card class="box-card echarts">
        <div class="search-bar">
            <el-form :inline="true" :model="searchData" class="fl" size="small">

                <el-form-item label="请选择">

                    <el-cascader
                        v-model="selectData"
                        placeholder="试试搜索"
                        :options="gwAndsn"
                        :props="props"
                        ref="gwAndsn"
                        :show-all-levels="true"
                        filterable
                        style="width:300px;">

                    </el-cascader>

                </el-form-item>

                <el-form-item label="开始时间">

                    <el-date-picker
                        placeholder="开始时间"
                        v-model="searchData.start_time"
                        type="datetime"
                        value-format="timestamp"
                        style="width: 100%;">

                    </el-date-picker>


                </el-form-item>
                <el-form-item label="结束时间">


                    <el-date-picker
                        v-model="searchData.end_time"
                        type="datetime"
                        placeholder="结束时间"
                        value-format="timestamp"
                        style="width: 100%;">

                    </el-date-picker>

                </el-form-item>

                <el-form-item>
                    <el-button type="primary" icon="el-icon-search" @click="initList" size="mini">查询</el-button>
                </el-form-item>
            </el-form>

        </div>

        <div id="myChart" class="temp-echarts"></div>
    </el-card>
</template>

<script>
    export default {

        name: "datagraph",

        data() {

            return {

                searchData: {

                    gw_id: '',
                    sn_id: '',
                    start_time: '',
                    end_time: '',
                    ST:''


                },
                selectData: [],
                gwAndsn: [],


                props: {
                    value: 'name',
                    label: 'name',
                    expandTrigger: 'hover',
                    checkStrictly:true
                    // multiple:true
                },
                series: []
            }
        },

        mounted() {

            this.getGwAndsn()

            this.initList()

        },
        created() {

            let params = this.$route.query

            this.searchData.gw_id = params.gw_id !== undefined ? params.gw_id : ''
            this.searchData.sn_id = params.sn_id !== undefined ? params.sn_id : ''
        },

        methods: {

            initList() {

                let _that = this

                this.series = []

                if (this.selectData.length > 0) {

                    this.searchData.gw_id = this.selectData[0] ? this.selectData[0] : ''
                    this.searchData.ST = this.selectData[1] ? this.selectData[1] : ''
                    this.searchData.sn_id = this.selectData[2] ? this.selectData[2] : ''
                }

                _that.$http.post('/SystermApi/getDayData', this.searchData).then(function (res) {


                    let seriesData = {
                        type : "line",
                        markPoint :{
                            data : [
                                {type : 'max', name: '最大值'},
                                {type : 'min', name: '最小值'}
                            ]
                        },
                        markLine :{
                            data : [
                                {type : 'average', name: '平均值'}
                            ]
                        },
                        itemStyle: {
                            normal: {
                                lineStyle: {
                                    width:1, //调整 线条的宽度  5已经很宽啦
                                    color: {
                                        type: 'linear',
                                        x: 0,
                                        y: 0,
                                        x2: 0,
                                        y2: 1,
                                        colorStops: [{
                                            offset: 0, color: 'red' // 0% 处的颜色
                                        }, {
                                            offset: 1, color: 'blue' // 100% 处的颜色
                                        }],
                                        global: false // 缺省为 false
                                    }
                                }
                            }
                        },
                        showSymbol : false,

                    };


                    for (let [key, value] of Object.entries(res)) {

                        seriesData.name = key
                        seriesData.data = value

                        _that.series.push(seriesData)
                    }
                    _that.drawLine()

                })

            },

            getGwAndsn() {
                let _that = this

                _that.$http.post('/SystermApi/getGwAndSn').then(function (res) {

                    _that.gwAndsn = res

                })

            },
            drawLine() {
                // 基于准备好的dom，初始化echarts实例
                let myChart = this.$echarts.init(document.getElementById('myChart'))


                let xDate = [];

                let timestamp = Date.parse(new Date());
                let date = new Date(timestamp);
                //获取年份 ?
                var Y = date.getFullYear();
                //获取月份 ?
                var M = (date.getMonth() + 1 < 10 ? '0' + (date.getMonth() + 1) : date.getMonth() + 1);
                //获取当日日期?
                var D = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();

                let today = Y + '-' + M + '-' + D;

                for (var ii = 0; ii < 25; ii++) {

                    let timeShow = `${today} ${ii > 9 ? ii : "0" + ii}:00:00`;

                    xDate.push(timeShow); // x轴显示
                }

                // 绘制图表
                myChart.setOption({
                    // color: ['#409EFF'],
                    title: {
                        text: '信号模组',
                        subtext: '工作数值',
                        textStyle: {
                            color:'#409EFF',
                            fontStyle:'normal',
                            fontWeight:'lighter',
                        }
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'shadow',

                            label : {
                                backgroundColor : 'red' // 上图显示的颜色
                            }

                        }
                    },
                    legend: {
                        show:true,
                        data: ['数值']
                    },
                    toolbox: {
                        show: true,
                        feature: {
                            mark: {show: true},
                            dataView: {
                                show: true,
                                readOnly: false,
                                // optionToContent:function (opt) {
                                //     console.log(opt)
                                // }
                            },
                            magicType: {show: true, type: ['line', 'bar']},
                            restore: {show: true},
                            saveAsImage: {show: true}
                        },
                        iconStyle:{


                        },
                    },
                    calculable: true,
                    xAxis: [
                        {
                            name: '时间',
                            type: 'time',
                            splitLine: false,
                            nameTextStyle: {
                                color: 'rgba(148,192,234,1)',
                            },
                            axisLine: {
                                symbol: ['none', 'arrow'],
                                show: true
                            },
                            axisLabel: {
                                interval: 0,/*横轴信息全部显示*/
                                textStyle: {
                                    color: '#304156' ,//x轴data 的颜色
                                    fontSize:12 // 让字体变大
                                }

                            },

                            interval: 1000 * 3600, // x轴时间间隔显示为半小时
                            // data: xDate,

                            axisTick: {



                            },
                            boundaryGap : false,

                        }
                    ],
                    yAxis: [
                        {
                            name: '数值',
                            type: 'value',
                            axisLine: {
                                symbol: ['none', 'arrow'],

                            },
                            nameTextStyle: {
                                color: 'rgba(148,192,234,1)',
                            },

                        }
                    ],
                    series: this.series
                })
            }
        },
    }
</script>

<style scoped>
    .temp-echarts {

        width: 100%;
        height: 500px;
        margin-top: 50px;

    }
</style>
