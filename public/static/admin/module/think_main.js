layui.define(['jquery'], function (exports) {
    "use strict";
    var $ = layui.$;

    var url = "/admin/getAssetsDistribution";
    $.get(
        url,
        function (data) {
            let message = data.message;
            console.log("客户资产分布图==>" + JSON.stringify(message));
            ///////
            let usdt = message.usdt;
            usdt = Number(usdt).toFixed(2);
            let btc = message.btc;
            btc = Number(btc).toFixed(2);

            let eth = message.eth;
            eth = Number(eth).toFixed(2);

            let vnd = message.vnd;
            vnd = Number(vnd).toFixed(2);

            let cny=message.cny;
            cny = Number(cny).toFixed(2);

            let idr=message.idr;
            idr = Number(idr).toFixed(2);

            let thb=message.thb;
            thb = Number(thb).toFixed(2);


            ////////////=>
            var themeName = "macarons";//"macarons";
            var data = genData(8);
            var myChart = echarts.init(document.getElementById('statistics1'), themeName),
                option = {
                    title: {
                        text: '客户资产分布',
                        subtext: '',
                        left: 'center'
                    },
                    tooltip: {
                        trigger: 'item',
                        formatter: '{a} <br/>{b} : {c} ({d}%)'
                    },
                    legend: {
                        type: 'scroll',
                        orient: 'vertical',
                        right: 10,
                        top: 20,
                        bottom: 20,
                        data: data.legendData,

                        selected: data.selected
                    },
                    series: [
                        {
                            name: '资产数量',
                            type: 'pie',
                            radius: '55%',
                            center: ['40%', '50%'],
                            data: data.seriesData,
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }
                    ]
                };
            myChart.setOption(option);

            function genData(count) {
                var nameList = [
                    'USDT', 'BTC', 'ETH', 'VND', 'CNY', 'USD', 'THB', 'IDR'
                ];
                var legendData = [];
                var seriesData = [];
                var selected = {};
                for (var i = 0; i < count; i++) {
                    name = nameList[i];
                    legendData.push(name);
                    if (name == "USDT") {
                        seriesData.push({
                            name: name+":"+usdt,
                            value: usdt
                        });
                    } else if (name == "BTC") {
                        seriesData.push({
                            name: name+":"+btc,
                            value: btc
                        });
                    } else if (name == "ETH") {
                        seriesData.push({
                            name: name+":"+eth,
                            value: eth
                        });
                    }else if (name == "CNY") {
                        seriesData.push({
                            name: name+":"+cny,
                            value: cny
                        });
                    }else if (name == "VND") {
                        seriesData.push({
                            name: name+":"+vnd,
                            value: vnd
                        });
                    }else if (name == "THB") {
                        seriesData.push({
                            name: name+":"+thb,
                            value: thb
                        });
                    }

                    if (name == "IDR") {
                        seriesData.push({
                            name: name+":"+idr,
                            value: idr
                        });
                    }

                    selected[name] = i < 8;
                }

                return {
                    legendData: legendData,
                    seriesData: seriesData,
                    selected: selected
                };
            }

            window.onresize = function () {
                myChart.resize();
            };


            //////////////====>


        });


    statistics2();

    //statistics3();

    /////
    var url = "/admin/microyingkuiTj";
    $.get(
        url,
        function (data) {
            let message = data.message;
            console.log("客户资产分布图==>" + JSON.stringify(message));

            let ying = message.ying;
            ying = Number(ying).toFixed(2);

            let kui = message.kui;
            kui = Number(kui).toFixed(2);




            var themeName = "macarons";//"macarons";
            var data = genData(2);
            var myChart = echarts.init(document.getElementById('statistics4'), themeName),
                option = {
                    title: {
                        text: '',
                        subtext: '',
                        left: 'center'
                    },
                    tooltip: {
                        trigger: 'item',
                        formatter: '{a} <br/>{b} : {c} ({d}%)'
                    },
                    legend: {
                        type: 'scroll',
                        orient: 'vertical',
                        right: 10,
                        top: 20,
                        bottom: 20,
                        data: data.legendData,

                        selected: data.selected
                    },
                    series: [
                        {
                            name: '盈亏分析',
                            type: 'pie',
                            radius: '55%',
                            center: ['40%', '50%'],
                            data: data.seriesData,
                            emphasis: {
                                itemStyle: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }
                    ]
                };
            myChart.setOption(option);

            function genData(count) {
                var nameList = [
                    '盈利', '亏损'
                ];
                var legendData = [];
                var seriesData = [];
                var selected = {};
                for (var i = 0; i < count; i++) {
                    name = nameList[i];
                    legendData.push(name);
                    if (name == "盈利") {
                        seriesData.push({
                            name: name,
                            value: ying
                        });
                    } else if (name == "亏损") {
                        seriesData.push({
                            name: name,
                            value: kui
                        });
                    }

                    selected[name] = i < 3;
                }

                return {
                    legendData: legendData,
                    seriesData: seriesData,
                    selected: selected
                };
            }

            window.onresize = function () {
                myChart.resize();
            };


            //////////////====>


        });

});

/**
 * 客户资产分布
 */
function statistics1() {
    var themeName = "macarons";//"macarons";
    var data = genData(7);
    var myChart = echarts.init(document.getElementById('statistics1'), themeName),
        option = {
            title: {
                text: '客户资产分布',
                subtext: '',
                left: 'center'
            },
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b} : {c} ({d}%)'
            },
            legend: {
                type: 'scroll',
                orient: 'vertical',
                right: 10,
                top: 20,
                bottom: 20,
                data: data.legendData,

                selected: data.selected
            },
            series: [
                {
                    name: '资产数量',
                    type: 'pie',
                    radius: '55%',
                    center: ['40%', '50%'],
                    data: data.seriesData,
                    emphasis: {
                        itemStyle: {
                            shadowBlur: 10,
                            shadowOffsetX: 0,
                            shadowColor: 'rgba(0, 0, 0, 0.5)'
                        }
                    }
                }
            ]
        };
    myChart.setOption(option);

    function genData(count) {
        var url = "/admin/getAssetsDistribution";
        $.get(
            url,
            function (data) {
                let message = data.message;
                console.log("客户资产分布图==>" + JSON.stringify(message));


            });
    }

    window.onresize = function () {
        myChart.resize();
    };
}


/**
 * 秒合约订单统计
 */
function statistics2() {
    var themeName = "macarons";//"macarons";
    var myChart = echarts.init(document.getElementById('statistics2'), themeName),
        option = {
            title: {
                text: '秒合约订单统计',
                subtext: 'USDT'
            },
            tooltip: {
                trigger: 'axis'
            },
            legend: {
                data: ['亏损', '盈利']
            },
            toolbox: {
                show: true,
                feature: {
                    dataView: {show: true, readOnly: false},
                    magicType: {show: true, type: ['line', 'bar']},
                    restore: {show: true},
                    saveAsImage: {show: true}
                }
            },
            calculable: true,
            xAxis: [
                {
                    type: 'category',
                    data: ['2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月', '1月']
                }
            ],
            yAxis: [
                {
                    type: 'value'
                }
            ],
            series: [
                {
                    name: '盈利',
                    type: 'bar',
                    data: [2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3],
                    markPoint: {
                        data: [
                            {type: 'max', name: '最大值'},
                            {type: 'min', name: '最小值'}
                        ]
                    },
                    markLine: {
                        data: [
                            {type: 'average', name: '平均值'}
                        ]
                    }
                },
                {
                    name: '亏损',
                    type: 'bar',
                    data: [2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3],
                    markPoint: {
                        data: [
                            {name: '年最高', value: 182.2, xAxis: 7, yAxis: 183},
                            {name: '年最低', value: 2.3, xAxis: 11, yAxis: 3}
                        ]
                    },
                    markLine: {
                        data: [
                            {type: 'average', name: '平均值'}
                        ]
                    }
                }
            ]
        };

    myChart.setOption(option);

    window.onresize = function () {
        myChart.resize();
    };
}

/**
 * 数据统计
 */
function statistics3() {
    var themeName = "macarons";//"macarons";
    var myChart = echarts.init(document.getElementById('statistics3'), themeName),
        option = {
            title: {
                text: '',
                subtext: '',
                left: 'left',
                top: 'bottom'
            },
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b} : {c}%'
            },
            toolbox: {
                orient: 'vertical',
                top: 'center',
                feature: {
                    dataView: {readOnly: false},
                    restore: {},
                    saveAsImage: {}
                }
            },
            legend: {
                orient: 'vertical',
                left: 'left',
                data: ['展现', '点击', '访问', '咨询', '订单']
            },

            series: [
                {
                    name: '漏斗图',
                    type: 'funnel',
                    width: '40%',
                    height: '45%',
                    left: '5%',
                    top: '50%',
                    data: [
                        {value: 60, name: '访问'},
                        {value: 30, name: '咨询'},
                        {value: 10, name: '订单'},
                        {value: 80, name: '点击'},
                        {value: 100, name: '展现'}
                    ]
                },
                {
                    name: '金字塔',
                    type: 'funnel',
                    width: '40%',
                    height: '45%',
                    left: '5%',
                    top: '5%',
                    sort: 'ascending',
                    data: [
                        {value: 60, name: '访问'},
                        {value: 30, name: '咨询'},
                        {value: 10, name: '订单'},
                        {value: 80, name: '点击'},
                        {value: 100, name: '展现'}
                    ]
                },
                {
                    name: '漏斗图',
                    type: 'funnel',
                    width: '40%',
                    height: '45%',
                    left: '55%',
                    top: '5%',
                    label: {
                        position: 'left'
                    },
                    data: [
                        {value: 60, name: '访问'},
                        {value: 30, name: '咨询'},
                        {value: 10, name: '订单'},
                        {value: 80, name: '点击'},
                        {value: 100, name: '展现'}
                    ]
                },
                {
                    name: '金字塔',
                    type: 'funnel',
                    width: '40%',
                    height: '45%',
                    left: '55%',
                    top: '50%',
                    sort: 'ascending',
                    label: {
                        position: 'left'
                    },
                    data: [
                        {value: 60, name: '访问'},
                        {value: 30, name: '咨询'},
                        {value: 10, name: '订单'},
                        {value: 80, name: '点击'},
                        {value: 100, name: '展现'}
                    ]
                }
            ]
        };

    myChart.setOption(option);

    window.onresize = function () {
        myChart.resize();
    };
}


/**
function statistics4() {
    var themeName = "";//"macarons";
    var myChart = echarts.init(document.getElementById('statistics4'), themeName),
        option = {
            tooltip: {
                formatter: '{a} <br/>{b} : {c}%'
            },
            toolbox: {
                feature: {
                    restore: {},
                    saveAsImage: {}
                }
            },
            series: [
                {
                    name: '业务指标',
                    type: 'gauge',
                    detail: {formatter: '{value}%'},
                    data: [{value: 50, name: '完成率'}]
                }
            ]
        };

    myChart.setOption(option);

    setInterval(function () {
        option.series[0].data[0].value = (Math.random() * 100).toFixed(2) - 0;
        myChart.setOption(option, true);
    }, 2000);

    window.onresize = function () {
        myChart.resize();
    };
}

 **/
