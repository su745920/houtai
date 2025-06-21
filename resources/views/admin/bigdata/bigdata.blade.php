<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>工作台</title>
    <link rel="stylesheet" href="{{URL('/admin/plugins/layui/css/layui.css')}}" media="all">


    <link rel="stylesheet" href="{{URL('/admin/plugins/layui/css/admin.css')}}" media="all">

    <style>
        /** 应用快捷块样式 */
        .console-app-group {
            padding: 16px;
            border-radius: 4px;
            text-align: center;
            background-color: #fff;
            cursor: pointer;
            display: block;
        }

        .console-app-group .console-app-icon {
            width: 32px;
            height: 32px;
            line-height: 32px;
            margin-bottom: 6px;
            display: inline-block;
            -webkit-box-sizing: border-box;
            -moz-box-sizing: border-box;
            box-sizing: border-box;
            font-size: 32px;
            color: #69c0ff;
        }

        .console-app-group:hover {
            box-shadow: 0 0 15px rgba(0, 0, 0, .08);
        }

        /** //应用快捷块样式 */

        /** 小组成员 */
        .console-user-group {
            position: relative;
            padding: 10px 0 10px 60px;
        }

        .console-user-group .console-user-group-head {
            width: 32px;
            height: 32px;
            position: absolute;
            top: 50%;
            left: 12px;
            margin-top: -16px;
            border-radius: 50%;
        }

        .console-user-group .layui-badge {
            position: absolute;
            top: 50%;
            right: 8px;
            margin-top: -10px;
        }

        .console-user-group .console-user-group-name {
            line-height: 1.2;
        }

        .console-user-group .console-user-group-desc {
            color: #8c8c8c;
            line-height: 1;
            font-size: 12px;
            margin-top: 5px;
        }

        /** 卡片轮播图样式 */
        .admin-carousel .layui-carousel-ind {
            position: absolute;
            top: -41px;
            text-align: right;
        }

        .admin-carousel .layui-carousel-ind ul {
            background: 0 0;
        }

        .admin-carousel .layui-carousel-ind li {
            background-color: #e2e2e2;
        }

        .admin-carousel .layui-carousel-ind li.layui-this {
            background-color: #999;
        }

        /** 广告位轮播图 */
        .admin-news .layui-carousel-ind {
            height: 45px;
        }

        .admin-news a {
            display: block;
            line-height: 70px;
            text-align: center;
        }

        /** 最新动态时间线 */
        .layui-timeline-dynamic .layui-timeline-item {
            padding-bottom: 0;
        }

        .layui-timeline-dynamic .layui-timeline-item:before {
            top: 16px;
        }

        .layui-timeline-dynamic .layui-timeline-axis {
            width: 9px;
            height: 9px;
            left: 1px;
            top: 7px;
            background-color: #cbd0db;
        }

        .layui-timeline-dynamic .layui-timeline-axis.active {
            background-color: #0c64eb;
            box-shadow: 0 0 0 2px rgba(12, 100, 235, .3);
        }

        .dynamic-card-body {
            box-sizing: border-box;
            overflow: hidden;
        }

        .dynamic-card-body:hover {
            overflow-y: auto;
            padding-right: 9px;
        }

        /** 优先级徽章 */
        .layui-badge-priority {
            border-radius: 50%;
            width: 20px;
            height: 20px;
            padding: 0;
            line-height: 18px;
            border-width: 2px;
            font-weight: 600;
        }
    </style>
</head>
<body>
<!-- 正文开始 -->
<div class="layui-fluid ew-console-wrapper">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    今日充值(USDT)<span class="layui-badge layui-badge-green pull-right">日</span>
                </div>
                <div class="layui-card-body">
                    <p class="lay-big-font"  id="todayUSDT"></p>
                    <p>总充值(USDT)<span class="pull-right" id="allUSDT"></span></p>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    今日秒合约订单<span class="layui-badge layui-badge-blue pull-right">日</span>
                </div>
                <div class="layui-card-body">
                    <p class="lay-big-font" id="todayMicroOrderCnt"></p>
                    <p>总订单<span class="pull-right"  id="allMicroOrderCnt"></span></p>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    合约订单<span class="layui-badge layui-badge-red pull-right">日</span>
                </div>
                <div class="layui-card-body">
                    <p class="lay-big-font"  id="todaySwapOrderCount"></p>
                    <p>总订单<span class="pull-right"  id="swapOrderCount"></span></p>
                </div>
            </div>
        </div>
        <div class="layui-col-xs12 layui-col-sm6 layui-col-md3">
            <div class="layui-card">
                <div class="layui-card-header">
                    今日新增用户
                    <span class="icon-text pull-right" lay-tips="今日新增用户" lay-direction="4" lay-offset="5px,5px">
                        <i class="layui-icon layui-icon-tips"></i>
                    </span>
                </div>
                <div class="layui-card-body">
                    <p class="lay-big-font"  id="todayRegCount"><span style="font-size: 24px;line-height: 1;">位</span></p>
                    <p>总用户<span class="pull-right"  id="allUserCount">人</span></p>
                </div>
            </div>
        </div>
    </div>
    <!-- 快捷方式 -->
    <div class="layui-row layui-col-space15">
        <div class="layui-col-sm6" style="padding-bottom: 0;">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-xs6 layui-col-sm3">
                    <div class="console-app-group" ew-href="/admin/index" ew-title="今日提币">
                        <i class="console-app-icon layui-icon layui-icon-dollar"
                           style="font-size: 26px;padding-top: 3px;margin-right: 6px;"></i>
                        <div class="console-app-name">今日提币:<span style="font-weight: 700;"  id="todayWithdrawUSDTAmount"></span></div>
                    </div>
                </div>
                <!--<div class="layui-col-xs6 layui-col-sm3">-->
                <!--    <div class="console-app-group" ew-href="/role/index" ew-title="c2c商家待审核">-->
                <!--        <i class="console-app-icon layui-icon layui-icon-group" style="color: #95de64;"></i>-->
                <!--        <div class="console-app-name"  id="c2c_audit_cnt">c2c商家待审核:0</div>-->
                <!--    </div>-->
                <!--</div>-->
                <!--<div class="layui-col-xs6 layui-col-sm3">-->
                <!--    <div class="console-app-group" ew-href="/dept/index" ew-title="今日抽奖">-->
                <!--        <i class="console-app-icon layui-icon layui-icon-component" style="color: #ff9c6e;"></i>-->
                <!--        <div class="console-app-name"  id="cj_count">今日抽奖:0次</div>-->
                <!--    </div>-->
                <!--</div>-->
                <!--<div class="layui-col-xs6 layui-col-sm3">-->
                <!--    <div class="console-app-group" ew-href="/menu/index" ew-title="带单员">-->
                <!--        <i class="console-app-icon layui-icon layui-icon-friends"-->
                <!--           style="color: #b37feb;font-size: 30px;"></i>-->
                <!--        <div class="console-app-name">带单员:4人</div>-->
                <!--    </div>-->
                <!--</div>-->
                
                <div class="layui-col-xs6 layui-col-sm3">
                    <div class="console-app-group" ew-href="/actionlog/index" ew-title="今日访问量">
                        <i class="console-app-icon layui-icon layui-icon-layer"
                           style="color: #ffd666;font-size: 34px;"></i>
                        <div class="console-app-name"  id="todayLoginCnt">今日访问量:0</div>
                    </div>
                </div>
                <div class="layui-col-xs6 layui-col-sm3">
                    <div class="console-app-group" ew-href="/link/index" ew-title="今日锁仓订单数量">
                        <i class="console-app-icon layui-icon layui-icon-list"
                           style="color: #5cdbd3;font-size: 36px;"></i>
                        <div class="console-app-name">今日锁仓订单:0</div>
                    </div>
                </div>
                <div class="layui-col-xs6 layui-col-sm3">
                    <div class="console-app-group" ew-href="/city/index" ew-title="今日申购订单">
                        <i class="console-app-icon layui-icon layui-icon-note"
                           style="color: #ff85c0;font-size: 28px;"></i>
                        <div class="console-app-name"  id="todayIEOCnt">今日申购订单:0</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-sm6" style="padding-bottom: 0;">
            <div class="layui-row layui-col-space15">
                
                <div class="layui-col-xs6 layui-col-sm3">
                    <div class="console-app-group" ew-href="/configweb/index" ew-title="运营团队">
                        <i class="console-app-icon layui-icon layui-icon-slider" style="color: #ffc069;"></i>
                        <div class="console-app-name">运营团队:1</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="layui-row layui-col-space15">
        <div class="layui-col-md8 layui-col-sm6">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">客户资产分布</div>
                        <div class="layui-card-body dynamic-card-body mini-bar" style="height: 265px;" id="statistics1">
                        </div>
                    </div>
                </div>
                <div class="layui-col-md6">
                    <div class="layui-card">
                        <div class="layui-card-header">用户秒合约盈亏统计</div>
                        <div class="layui-card-body dynamic-card-body mini-bar" style="height: 265px;" id="statistics4">
                        </div>
                    </div>
                </div>
                <div class="layui-col-md12">
                    <div class="layui-card">
                        <div class="layui-card-header">秒合约统计</div>
                        <div class="layui-card-body" style="height: 300px;" id="statistics2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="layui-col-md4 layui-col-sm6">
            <div class="layui-card">
                <div class="layui-card-header">客户充值排行榜</div>
                <div class="layui-card-body dynamic-card-body mini-bar" style="height: 265px;">
                    <table class="layui-table layui-text">
                        <colgroup>
                            <col width="90">
                            <col>
                        </colgroup>
                        <tbody>
                        <script type="text/html" ew-tpl>
                            <tr>
                                <td>当前版本</td>
                                <td>v3.4.0 &emsp;
                                </td>
                            </tr>
                            <tr>
                                <td>基于框架</td>
                                <td>Laravel8.x、Layui、MySQL</td>
                            </tr>
                        </script>
                        <tr>
                            <td>名次</td>
                            <td>用户名</td>
                            <td>折合USDT</td>
                        </tr>
                        @foreach($rankList as $rankPO)
                        <tr>
                            <td>1</td>
                            <td>
                              {{$rankPO->account_number}}
                            </td>
                            <td>{{$rankPO->cz_amount}}</td>
                        </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="layui-card">
                <div class="layui-card-header">其他待定</div>
                <div class="layui-card-body" style="height: 300px;" id="statistics3">
                </div>
            </div>
            <div class="layui-card" style="display: none">
                <div class="layui-card-header">友情链接</div>
                <div class="layui-card-body">
                    <div class="layui-carousel admin-carousel admin-news" id="workplaceNewsCarousel">
                        <div carousel-item>
                            <div>
                                <a href="javascript:void(0);" target="_blank"
                                   style="color:#fff;background-color: #009fde;background-image: linear-gradient(to right,#009fde,#00beff);">
                                    数据1</a>
                            </div>
                            <div>
                                <a href="javascript:void(0);" target="_blank"
                                   style="color:#fff;background-color: #34363f;background-image: linear-gradient(to right,#34363f,#676c7c);">
                                    数据2</a>
                            </div>
                            <div>
                                <a href="javascript:void(0);" target="_blank"
                                   style="color:#fff;background-color: #009688;background-image: linear-gradient(to right,#009688,#5fb878);">
                                    数据3</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- js部分 -->
<script src="{{URL('/admin/plugins/layui/layui.js')}}"></script>
<script type="text/javascript" src="/static/admin/assets/js/common.js?v=318"></script>
<script type="text/javascript" src="/static/admin/assets/libs/echarts/echarts.min.js"></script>
<script type="text/javascript" src="/static/admin/assets/libs/echarts/theme/macarons.js"></script>
<script type="text/javascript" src="/static/admin/module/think_main.js?v=318"></script>
<script>
    layui.use(['layer', 'carousel', 'element'], function () {
        var $ = layui.jquery;
        var layer = layui.layer;
        var carousel = layui.carousel;
        var device = layui.device();

        // 渲染轮播
        carousel.render({
            elem: '#workplaceNewsCarousel',
            width: '100%',
            height: '70px',
            arrow: 'none',
            autoplay: true,
            trigger: device.ios || device.android ? 'click' : 'hover',
            anim: 'fade'
        });
        //
        var url="/admin/homeBigData";
        $.post(
            url,
            function(data){
                var message=data.message;
                //allUserCount":379,"todayRegCount":0,"swapOrderCount":124}}
                console.log(JSON.stringify(data));
                let allUSDT=message.allUSDT;
                $("#allUSDT").html(allUSDT);

                let todayUSDT=message.todayUSDT;
                todayUSDT=Number(todayUSDT).toFixed(2);
                $("#todayUSDT").html(todayUSDT);

                let todayMicroOrderCnt=message.todayMicroOrderCnt;//今日秒合约订单
                $("#todayMicroOrderCnt").html(todayMicroOrderCnt);

                let allMicroOrderCnt=message.allMicroOrderCnt;//秒合约总订单
                $("#allMicroOrderCnt").html(allMicroOrderCnt);

                var allUserCount=message.allUserCount;
                $("#allUserCount").html(allUserCount+"人");
                var todayRegCount=message.todayRegCount;

                var swapOrderCount=message.swapOrderCount;
                $("#swapOrderCount").html(swapOrderCount);

                let todaySwapOrderCount=message.todaySwapOrderCount;
                $("#todaySwapOrderCount").html(todaySwapOrderCount);

                let todayWithdrawUSDTAmount=message.todayWithdrawUSDTAmount;
                $("#todayWithdrawUSDTAmount").html(todayWithdrawUSDTAmount);


                $("#todayRegCount").html(todayRegCount+"<span style='font-size: 24px;line-height: 1;'>位</span>");


                let cj_count=message.cj_count;
                $("#cj_count").html("今日抽奖:"+cj_count+"次");

                let c2c_audit_cnt=message.c2c_audit_cnt;
                $("#c2c_audit_cnt").html("c2c商家待审核:"+c2c_audit_cnt);

                let todayLoginCnt=message.todayLoginCnt;

                $("#todayLoginCnt").html("今日访问量:"+todayLoginCnt);
                //
                let todayIEOCnt=message.todayIEOCnt;
                $("#todayIEOCnt").html("今日申购订单:"+todayIEOCnt);




            });
        //

        // $.get(
        //     "/admin/czRankFn",
        //     function(data) {
        //         var data = data.message;
        //
        //     });


    });
</script>
</body>
</html>
