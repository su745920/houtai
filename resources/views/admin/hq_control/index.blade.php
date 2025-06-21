@extends('admin._layoutNew')
@section('page-head')
     <script src="https://lib.baomitu.com/echarts/5.0.2/echarts.min.js"></script>
@endsection
@section('page-content')
    <form class="layui-form" method="POST" id="form">
        <input type="hidden" name="project_id" id="projectId">
        {{ csrf_field() }}

        <div class="layui-form-item">
            <label for="currency_id" class="layui-form-label">币种</label>
            <div class="layui-input-block">
                 <select name="symbol" lay-verify="required" id="symbol" lay-search>
                    @foreach ($currencies as $currency)
                    <option value="{{$currency->name}}">{{$currency->name}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">上涨比例</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="rate1" name="rate1" lay-verify="required" placeholder="下跌比例"
                               value="130" type="number">
                    </div>
                </div>
            </div>
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">下跌比例</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="rate2" name="rate2" lay-verify="required" placeholder="下跌比例"
                               value="60" type="number">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                    <label class="layui-form-label">插入日期</label>
                    <div class="layui-input-block">
                        <input class="layui-input itime" id="dates" name="dates" lay-verify="required"
                               placeholder="请选择时间" value=""
                               type="text">
                    </div>
                </div>
        
                    <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">插入时长</label>
                    <div class="layui-input-block">
                        <select name="insert_range" lay-verify="required">
                          <option value="">请选择</option>
                          <option value="1">1分钟</option>
                          <option value="2">2分钟</option>
                          <option value="3">3分钟</option>
                          <option value="4">4分钟</option>
                          <option value="5">5分钟</option>
                          <option value="6">6分钟</option>
                          <option value="7">7分钟</option>
                          <option value="8">8分钟</option>
                          <option value="9">9分钟</option>
                          <option value="10">10分钟</option>
                          <!--<option value="5">五分钟</option>-->
                          <!--<option value="15">15分钟</option>-->
                          <!--<option value="30">30分钟</option>-->
                          <!--<option value="60">60分钟</option>-->
                          <!--<option value="1440">1天</option>-->
                          <!--<option value="10080">1周</option>-->
                          <!--<option value="43200">1月</option>-->
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">

                <div class="layui-form-item">
                    <label class="layui-form-label">变化幅度（小数点后位数）</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="jingdu" name="jingdu" lay-verify="required" placeholder="增长精度"
                               value="4" type="text">
                    </div>
                </div>
            </div>
            
             <div class="layui-row">

            <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
                <div class="layui-form-item">
                    <label class="layui-form-label">起始价格</label>
                    <div class="layui-input-block">
                        <input class="layui-input" id="start" name="start" lay-verify="required" placeholder="起始价格"
                               value="0" type="text">
                    </div>
                </div>
            </div>
            <div class=" layui-col-md4 layui-col-xs4 layui-col-sm4">
                <div class="layui-form-item">
                    <label class="layui-form-label">根变化量%</label>
                    <div class="layui-input-block" style="display: flex;flex-direction: row;">
                        <input class="layui-input" style="flex:1" id="speed0" name="speed0" lay-verify="required" placeholder="变化幅度最小"
                               value="0.0001" type="text">

                        <input class="layui-input"  style="flex:1" id="speed1" name="speed1" lay-verify="required" placeholder="变化幅度最大"
                               value="0.0005" type="text">

                    </div>
                </div>
            </div>
            <div class=" layui-col-md4 layui-col-xs4 layui-col-sm4">
                <div class="layui-form-item">
                    <label class="layui-form-label">每分钟成交量</label>
                    <div class="layui-input-block" style="display: flex;flex-direction: row;">
                        <input class="layui-input" style="flex:1" id="vol0" name="vol0" lay-verify="required" placeholder="变化幅度最小"
                               value="600" type="text">

                        <input class="layui-input"  style="flex:1" id="vol1" name="vol1" lay-verify="required" placeholder="变化幅度最大"
                               value="3005" type="text">

                    </div>
                </div>
            </div>
        </div>
            
       
        <div class="layui-form-item">
            <div class="layui-input-block">
                 <button type="button" onclick="previewKLine()" class="layui-btn">预生成K线</button>
                <button type="button" class="layui-btn" onclick="nextKline()">保存并生成下一段k线</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
     <div style="width:100%; height: 768px;" id="echarts"></div>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{URL("/admin/js/hqControlSubmit.js?v=").time()}}"></script>
    <script>

         let layer;
        let form;
        let element;
        layui.use(['element','laydate', 'layer','form'], () => {
            var laydate = layui.laydate;
            let dropdown = layui.dropdown;
            layer = layui.layer;
            form=layui.form;
            element =layui.element;
            laydate.render({
                elem: '#dates',
                type: 'datetime',
                format:'yyyy-MM-dd HH:mm'
            });
        })

         let data = [];
        function previewKLine() {
            var upColor = '#ec0000';
            var upBorderColor = '#8A0000';
            var downColor = '#00da3c';
            var downBorderColor = '#008F28';

            var dataCount = 60;
            var myChart = echarts.init(document.getElementById('echarts'));


            $.getJSON('/admin/hqControl/previewKLine', $('#form').serialize(), res => {
                data = res;

                var option = {
                    dataset: {
                        source: data.data
                    },
                    title: {
                        text: 'Data Amount: ' + echarts.format.addCommas(dataCount)
                    },
                    tooltip: {
                        trigger: 'axis',
                        axisPointer: {
                            type: 'line'
                        }
                    },
                    toolbox: {
                        feature: {
                            dataZoom: {
                                yAxisIndex: false
                            },
                        }
                    },
                    grid: [
                        {
                            left: '10%',
                            right: '10%',
                            bottom: 200
                        },
                        {
                            left: '10%',
                            right: '10%',
                            height: 80,
                            bottom: 80
                        }
                    ],
                    xAxis: [
                        {
                            type: 'category',
                            scale: true,
                            boundaryGap: false,
                            // inverse: true,
                            axisLine: {onZero: false},
                            splitLine: {show: false},
                            splitNumber: 20,
                            min: 'dataMin',
                            max: 'dataMax'
                        },
                        {
                            type: 'category',
                            gridIndex: 1,
                            scale: true,
                            boundaryGap: false,
                            axisLine: {onZero: false},
                            axisTick: {show: false},
                            splitLine: {show: false},
                            axisLabel: {show: false},
                            splitNumber: 20,
                            min: 'dataMin',
                            max: 'dataMax'
                        }
                    ],
                    yAxis: [
                        {
                            scale: true,
                            splitArea: {
                                show: true
                            }
                        },
                        {
                            scale: true,
                            gridIndex: 1,
                            splitNumber: 2,
                            axisLabel: {show: false},
                            axisLine: {show: false},
                            axisTick: {show: false},
                            splitLine: {show: false}
                        }
                    ],
                    dataZoom: [
                        {
                            type: 'inside',
                            xAxisIndex: [0, 1],
                            start: 10,
                            end: 100
                        },
                        {
                            show: true,
                            xAxisIndex: [0, 1],
                            type: 'slider',
                            bottom: 10,
                            start: 10,
                            end: 100
                        }
                    ],
                    visualMap: {
                        show: false,
                        seriesIndex: 1,
                        dimension: 6,
                        pieces: [{
                            value: 1,
                            color: upColor
                        }, {
                            value: -1,
                            color: downColor
                        }]
                    },
                    series: [
                        {
                            type: 'candlestick',
                            itemStyle: {
                                color: upColor,
                                color0: downColor,
                                borderColor: upBorderColor,
                                borderColor0: downBorderColor
                            },
                            encode: {
                                x: 0,
                                y: [1, 4, 3, 2]
                            }
                        },
                        {
                            name: 'Volumn',
                            type: 'bar',
                            xAxisIndex: 1,
                            yAxisIndex: 1,
                            itemStyle: {
                                color: '#7fbe9e'
                            },
                            large: true,
                            encode: {
                                x: 0,
                                y: 5
                            }
                        }
                    ]
                };

                myChart.setOption(option);
            })
        }
        function nextKline() {
            if ($('#dates').val() === '') {
                layer.msg('请选择日期');
                return;
            }
            if (data.length === 0) {
                layer.msg('请先生成k线');
                return;
            }
            let con = JSON.stringify(data.data);
            layer.load(2);
            $.post('/admin/hqControl/saveAjax', {
                kline: con,
                k5min: JSON.stringify(data['k5min']),
                k15min: JSON.stringify(data['k15min']),
                k30min: JSON.stringify(data['k30min']),
                k60min: JSON.stringify(data['k60min']),
                k1day: JSON.stringify(data['k1day']),
                k1week: JSON.stringify(data['k1week']),
                k1mon: JSON.stringify(data['k1mon']),
                date: ($('#dates').val() + ' ' + $('#times').val() + ":00:00"),
                symbol:$('#symbol').val()
            }, res => {
                console.log(res);
                layer.closeAll('loading');
                $('#start').val(data.data[data.data.length - 1][4]);
                if (parseInt($('#times').val()) < 23) {
                    // $('#times').val(parseInt($('#times').val()) + 1);
                } else {
                    $('#times').val(0);
                    $('#dates').val(data.next)
                }
                previewKLine();
            });
        }


    </script>
@endsection
