
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>UZX交易所后台管理系统</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="../layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="../layuiadmin/style/admin.css" media="all">
    <script src="/js/jquery.js"></script>
    <script>
        @if($authorityList==null)
            window.location.href="/admin";
        @endif
    </script>
    <script src="https://cdn.bootcss.com/sockjs-client/1.4.0/sockjs.min.js"></script>
    <script src="https://cdn.bootcss.com/stomp.js/2.3.2/stomp.min.js"></script>

</head>

<body class="layui-layout-body">

<div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
        <div class="layui-header">
            <!-- 头部区域 -->
            <ul class="layui-nav layui-layout-left">
                <li class="layui-nav-item layadmin-flexible" lay-unselect>
                    <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                        <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible" style="color: #000;"></i>
                    </a>
                </li>
               <li class="layui-nav-item layui-hide-xs" lay-unselect>

                  <a href="javascript:void(0);" onclick="czFn();" style="color: #ffffff;background: #2A64FB;line-height: 30px;height: 30px;margin-top: 10px;width: 80px;">
                      <span style="margin-left: 8px;">充值</span>
                      <span class="layui-badge" style="background: red;"  id="czTipCount">0</span>
                  </a>

                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:void(0);" onclick="withdrawFn()" style="color: #ffffff;background: #2A64FB;line-height: 30px;height: 30px;margin-top: 10px;width: 80px;">
                        <span style="margin-left: 8px;">提现</span>
                        <span class="layui-badge" style="background: #01eedd;"  id="withdrawTipCount">0</span>
                    </a>
                </li>

                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:void(0);" onclick="userListFn()" style="color: #ffffff;background: #2A64FB;line-height: 30px;height: 30px;margin-top: 10px;width: 120px;">
                        <span style="margin-left: 8px;">在线人数</span>
                        <span class="layui-badge" style="background: #01eedd;"  id="onlineUserCnt">0</span>
                    </a>
                </li>



            </ul>
            <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">



                <li class="layui-nav-item layui-hide-xs" lay-unselect>
                    <a href="javascript:;" layadmin-event="fullscreen">
                        <i class="layui-icon layui-icon-screen-full"></i>
                    </a>
                </li>
                <li class="layui-nav-item" lay-unselect>
                    <a href="javascript:;">
                        <img src="/man.png" style="width: 30px;height: 30px;">
                        <cite>{{ $admin_username }}</cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a lay-href="set/user/info.html">基本资料</a></dd>
                        <dd><a lay-href="/admin/set/update_pwd">修改密码</a></dd>
                        <hr>

                        <dd><a href="javascript:void(0);" onclick="logoutFn();">退出</a></dd>
                    </dl>
                </li>

                <li class="layui-nav-item layui-hide-xs" lay-unselect style="width: 50px;">
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </li>

            </ul>
        </div>

        <!-- 侧边菜单 -->
        <div class="layui-side layui-side-menu">
            <div class="layui-side-scroll">
                <div class="layui-logo" lay-href="/home/console.html" style="font-weight:700;">
                    <img   src="/logo.6c11393.png"
                           style="height:24px;margin-top:-7px;margin-left:5px;">
                           交易所后台管理系统


                </div>

                <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">




                    <li data-name="app" class="layui-nav-item">
                        <a lay-href="/admin/bigdata_v2024" lay-tips="交易管理" lay-direction="2">
                            <img src="/v2/bigdata.png" alt=""   style="height: 20px;width: 20px;margin-left: -10px;">
                            <cite>大数据</cite>
                        </a>
                    </li>


                    <li data-name="app" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="交易管理" lay-direction="2">
                            <img src="/v2/jiaoyi.png" alt=""   style="height: 20px;width: 20px;margin-left: -10px;">
                            <cite>交易管理</cite>
                        </a>
                        <dl class="layui-nav-child">

                            @if(strpos($authorityList,"2100")>0)
                                <dd>
                                    <a lay-href="/admin/trade" data-icon="&#xe628;" data-title="币币交易" kit-target data-id='32'><span> 币币交易</span></a>
                                </dd>
                            @endif







                            <dd><a lay-href="/admin/Leverdeals/Leverdeals_show"><span> 合约订单</span></a></dd>
                            <!--
                            <dd><a lay-href="/admin/currency/match_index"><span> 交易对</span></a></dd>
                            -->


                            @if(strpos($authorityList,"2400")>0)
                                <dd><a lay-href="/admin/hazard/total"><span>用户风险率汇总</span></a></dd>
                            @endif

                            @if(strpos($authorityList,"2500")>0)
                                <dd><a lay-href="/admin/account/account_index"><span>资金流水</span></a></dd>
                            @endif

                            @if(strpos($authorityList,"2600")>0)
                                <dd><a lay-href="/admin/micro_order"><span>秒合约交易</span></a></dd>
                            @endif



                            <dd><a lay-href="/admin/micro_config"><span>秒合约设置</span></a></dd>
                                <dd><a lay-href="/admin/micro_amount"><span>秒合约下单数量</span></a></dd>





                        </dl>
                    </li>










                    <li data-name="user" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="客户管理" lay-direction="2">
                            <img src="/v2/user.png" alt=""  style="height: 22px;width: 22px;margin-left: -10px;">
                            <cite>客户管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            @if(strpos($authorityList,"9101")>0)
                                <dd>
                                    <a lay-href="/admin/user/user_index">客户列表</a>
                                </dd>
                            @endif


                            @if(strpos($authorityList,"9001")>0)
                                <dd>
                                    <a lay-href="/admin/user/real_index">实名认证管理</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"2700")>0)
                                <dd><a  lay-href="/admin/feedback/index">投诉建议</a></dd>
                            @endif


                        </dl>
                    </li>
                    <li data-name="set" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="资金管理" lay-direction="2">
                            <img src="/v2/money.png" alt=""  style="height: 22px;width: 22px;margin-left: -10px;">
                            <cite>资金管理</cite>
                        </a>
                        <dl class="layui-nav-child">
                            <dd class="layui-nav-itemed">
                                <a lay-href="/admin/rebate_rules_index">返佣文案配置</a>

                            </dd>
                            <dd class="layui-nav-itemed">
                                <a lay-href="/admin/invite/reward_conf_page">返佣规则配置</a>

                            </dd>
                            <dd class="layui-nav-itemed">
                                <a lay-href="/admin/invite/account_return">邀请返佣</a>

                            </dd>

                            @if(strpos($authorityList,"8477")>0)
                                <dd class="layui-nav-itemed">
                                    <a lay-href="/admin/czRewardGroup/index">充值组团返佣列表</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"8477")>0)
                                <dd class="layui-nav-itemed">
                                    <a lay-href="/admin/tranFeeReward/index">交易手续费佣列表</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"8479")>0)
                                <dd class="layui-nav-itemed">
                                    <a lay-href="/admin/miningRewardGroup/index">挖矿组团返佣</a>
                                </dd>
                            @endif



                            @if(strpos($authorityList,"2900")>0)
                                <dd class="layui-nav-itemed">
                                    <a lay-href="/admin/cashb">提币申请</a>

                                </dd>
                            @endif

                            @if(strpos($authorityList,"2950")>0)
                                <dd class="layui-nav-itemed">
                                    <a lay-href="/admin/account/recharge_v2">充币申请</a>
                                </dd>
                            @endif


                            <dd>
                                <a lay-href="/admin/account/account_index">日志信息</a>
                            </dd>

                        </dl>
                    </li>

                    <li data-name="senior" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="理财管理" lay-direction="2">
                            <img src="/v2/licai.png" alt=""  style="height: 23px;width: 23px;margin-left: -10px;">
                            <cite>理财管理</cite>
                        </a>
                        <dl class="layui-nav-child">

                            @if(strpos($authorityList,"3001")>0)
                                <dd>
                                    <a lay-href="/admin/lockmining">锁仓挖矿产品</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"3100")>0)
                                <dd>
                                    <a lay-href="/admin/lockminingorder">锁仓挖矿订单列表</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"3200")>0)
                                <dd>
                                    <a lay-href="/admin/currency/project/view">申购产品</a>
                                </dd>
                            @endif





                        </dl>
                    </li>
                    
                    <li data-name="senior" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="贷款管理" lay-direction="2">
                            <img src="/v2/gd.png" alt=""  style="height: 23px;width: 23px;margin-left: -10px;">
                            <cite>贷款管理</cite>
                        </a>
                        <dl class="layui-nav-child">

                            @if(strpos($authorityList,"3001")>0)
                                <dd>
                                    <a lay-href="/admin/loan_order_index">贷款列表</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"3100")>0)
                                <dd>
                                    <a lay-href="/admin/repayment_record_index">还款记录</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"3200")>0)
                                <dd>
                                    <a lay-href="/admin/loan_setting_index">贷款设置</a>
                                </dd>
                            @endif
                        </dl>
                    </li>

                    <li data-name="home" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="平台设置">
                            <img src="/v2/set.png" alt="平台设置"  style="height: 22px;width: 22px;margin-left: -10px;">
                            <cite>平台设置</cite>
                        </a>
                        <dl class="layui-nav-child">
                            @if(strpos($authorityList,"9990")>0)
                                <dd data-name="console">
                                    <a lay-href="/admin/setting/index">基础设置</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"9999")>0)
                                <dd data-name="console">
                                    <a lay-href="/admin/setting/walletconfig_v2">钱包设置</a>
                                </dd>
                                 <dd data-name="console">
                                    <a lay-href="/admin/wallet_address/index">充值地址管理</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"999901")>0 || strpos($authorityList,"999802")>0)
                                <!--<dd data-name="console">-->
                                <!--    <a lay-href="/admin/setting/walletconfig_en">欧美电汇设置</a>-->
                                <!--</dd>-->
                            @endif


                                    <!--<dd data-name="console">-->
                                    <!--    <a lay-href="/admin/prize_index">大转盘设置</a>-->
                                    <!--</dd>-->

                                <dd data-name="console">
                                    <a lay-href="/admin/user_level">用户等级设置</a>
                                </dd>


                            @if(strpos($authorityList,"1100")>0)
                                <dd data-name="console">
                                    <a lay-href="/admin/manager/manager_roles">角色管理</a>
                                </dd>
                            @endif

                            @if(strpos($authorityList,"8100")>0)
                                <dd data-name="console">
                                    <a lay-href="/admin/manager/manager_index">后台管理员</a>
                                </dd>
                            @endif
                            
                            
                             @if(strpos($authorityList,"8200")>0)
                                <dd data-name="console">
                                    <a lay-href="/admin/agent/index">代理管理</a>
                                </dd>
                            @endif



                            @if(strpos($authorityList,"1200")>0)
                                <dd>
                                    <a lay-href="/admin/currency">币种管理</a>
                                </dd>
                            @endif



                            @if(strpos($authorityList,"1600")>0)
                                <dd>
                                    <a lay-href="/admin/robot/auto_robot">机器人列表</a>
                                </dd>
                            @endif


                        </dl>
                    </li>



                    @if(strpos($authorityList,"1500")>0)
                        <li data-name="component" class="layui-nav-item">
                            <a href="javascript:;" lay-tips="资讯管理" lay-direction="2">
                                <img src="/v2/news.png" alt=""  style="height: 22px;width: 22px;margin-left: -10px;">
                                <cite>资讯管理</cite>
                            </a>
                            <dl class="layui-nav-child">
                                <dd data-name="button">
                                    <a lay-href="/admin/news_index">资讯列表</a>
                                </dd>

                            </dl>
                        </li>
                    @endif

                    <!--<li data-name="user" class="layui-nav-item">-->
                    <!--    <a href="javascript:;" lay-tips="跟单管理" lay-direction="2">-->
                    <!--        <img src="/v2/gd.png" alt=""  style="height: 22px;width: 22px;margin-left: -10px;">-->
                    <!--        <cite>跟单管理</cite>-->
                    <!--    </a>-->
                    <!--    <dl class="layui-nav-child">-->
                    <!--        @if(strpos($authorityList,"9101")>0)-->
                    <!--            <dd>-->
                    <!--                <a lay-href="/admin/copyTrade/page">交易员列表</a>-->
                    <!--            </dd>-->
                    <!--        @endif-->

                    <!--        <dd>-->
                    <!--            <a lay-href="/admin/copy_trade_order/page">交易员订单列表</a>-->
                    <!--        </dd>-->

                    <!--    </dl>-->
                    <!--</li>-->

                    <li data-name="app" class="layui-nav-item">
                        <a href="javascript:;" lay-tips="插针管理" lay-direction="2">
                            <img src="/zhen.png" alt=""   style="height: 22px;width: 22px;margin-left: -10px;">
                            <cite>插针管理</cite>
                        </a>
                        <dl class="layui-nav-child">

                            <dd>
                                <a lay-href="/admin/hqControl/index" data-icon="&#xe628;" data-title="插针设置" kit-target data-id='33'><span>插针设置</span></a>
                            </dd>
                            <dd>
                                <a lay-href="/admin/hqControl/page" data-icon="&#xe628;" data-title="历史插针" kit-target data-id='34'><span>历史插针</span></a>
                            </dd>
                            <dd>
                                <a lay-href="/admin/hqControl/price" data-icon="&#xe628;" data-title="价格设置" kit-target data-id='33'><span>价格设置</span></a>
                            </dd>

                        </dl>
                    </li>

                    <!--<li data-name="app" class="layui-nav-item">-->
                    <!--    <a href="javascript:;" lay-tips="C2C管理" lay-direction="2">-->
                    <!--        <img src="/c2c.png" alt=""   style="height: 22px;width: 22px;margin-left: -10px;">-->
                    <!--        <cite>C2C管理</cite>-->
                    <!--    </a>-->
                    <!--    <dl class="layui-nav-child">-->
                    <!--        @if(strpos($authorityList,"C9101")>0|| strpos($authorityList,"C9103")>0)-->
                    <!--        <dd>-->
                    <!--            <a lay-href="/admin/c2c_merchant/merchant_page" data-icon="&#xe628;" data-title="商户申请" kit-target data-id='33'><span>商户管理</span></a>-->
                    <!--        </dd>-->
                    <!--        @endif-->

                    <!--            @if(strpos($authorityList,"C9001")>0||strpos($authorityList,"C9003")>0 || strpos($authorityList,"C9004")>0)-->
                    <!--        <dd>-->
                    <!--            <a lay-href="/admin/c2c/all" data-icon="&#xe628;" data-title="C2C交易" kit-target data-id='34'><span> C2C挂单需求</span></a>-->
                    <!--        </dd>-->
                    <!--            @endif-->

                    <!--            @if(strpos($authorityList,"C2700")>0)-->
                    <!--        <dd>-->
                    <!--            <a lay-href="/admin/c2c/appeal" data-icon="&#xe628;" data-title="C2C申诉" kit-target data-id='34'><span> C2C申诉</span></a>-->
                    <!--        </dd>-->
                    <!--            @endif-->

                    <!--    </dl>-->
                    <!--</li>-->
                    <!--
                    <li data-name="get" class="layui-nav-item">
                        <a href="javascript:;" lay-href="//www.layui.com/admin/#get" lay-tips="授权" lay-direction="2">
                            <img src="./image/setting_icon.png" alt="">
                            <cite>授权</cite>
                        </a>
                    </li>
                    -->
                </ul>
            </div>
        </div>

        <!-- 页面标签 -->
        <div class="layadmin-pagetabs" id="LAY_app_tabs" style="display: none;">
            <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-down">
                <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;"></a>
                        <dl class="layui-nav-child layui-anim-fadein">
                            <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                            <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                            <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
            <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                <ul class="layui-tab-title" id="LAY_app_tabsheader">
                    <li lay-id="/home/console.html" lay-attr="home/console.html" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
                </ul>
            </div>
        </div>


        <!-- 主体内容 -->
        <div class="layui-body" id="LAY_app_body"  style="margin-top: -40px;">
            <div class="layadmin-tabsbody-item layui-show">
              
                <iframe src="/admin/bigdata_v2024" frameborder="0" id="iframeID"  class="layadmin-iframe"></iframe>
            </div>
        </div>

        <!-- 辅助元素，一般用于移动设备下遮罩 -->
        <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
</div>

<script src="../layuiadmin/layui/layui.js"></script>
<script>
    layui.config({
        base: '../layuiadmin/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use('index',function() {
        var $ = layui.$,
            admin = layui.admin,
            form = layui.form,
            table = layui.table

        var active = {
            // 点击菜单缓存当前页面
            menuEvent() {
                // 刷新
                $('#LAY-system-side-menu').on("click", 'a[lay-href]', function() {
                    
                   setTimeout(() => {
                        $('#LAY_app_body .layui-show .layadmin-iframe').attr('src', $('#LAY_app_body .layui-show .layadmin-iframe').attr('src'));
                   },100)
                    
                    var tabNum = $(this).index();
                    var tabUrl = $(this).attr('lay-href');
                    var homeTabMenu = {};
                    homeTabMenu.tabNum = tabNum;
                    homeTabMenu.tabUrl = tabUrl;
                    sessionStorage.setItem("homeTabMenu", JSON.stringify(homeTabMenu));
                })
            },
            // 刷新保持页面
            stopPage() {
                // 刷新保持在当前页面
                var homeTabMenu = sessionStorage.getItem("homeTabMenu");
                homeTabMenu = JSON.parse(homeTabMenu);
                // console.log(homeTabMenu)
                // console.log(homeTabMenu!='' && homeTabMenu.hasOwnProperty('tabUrl'))
                // 将之前的URL设置为iframe的src
                if (homeTabMenu != null && homeTabMenu.hasOwnProperty('tabUrl')) {
                    var a = homeTabMenu.tabUrl;
                    $(`#LAY-system-side-menu a[lay-href="${a}"]`).click();
                    $(`#LAY-system-side-menu a[lay-href="${a}"]`).parents('li').addClass('layui-nav-itemed');
                }
            },
            init: function() {
                this.menuEvent();
                this.stopPage();
            }
        }
        active.init();
    });
</script>


<script>
    function czFn(){
        document.getElementById("iframeID").src="/admin/account/recharge_v2";
        navNext()
    }

    function withdrawFn(){
        document.getElementById("iframeID").src="/admin/cashb";
        navNext()
    }
    function userListFn(){
        document.getElementById("iframeID").src="/admin/user/user_index";
        navNext()
    }
    function navNext() {
        var a = '/admin/bigdata_v2024';
        $(`#LAY-system-side-menu a[lay-href="${a}"]`).click();
        $(`#LAY-system-side-menu a[lay-href="${a}"]`).parents('li').addClass('layui-nav-itemed');
        $('#LAY_app_body > :first-child').addClass('layui-show');
        $('#LAY_app_body > :first-child').siblings('.layadmin-tabsbody-item').removeClass('layui-show')
    }
    
    function uuid() {
        var s = [];
        var hexDigits = "0123456789abcdef";
        for (var i = 0; i < 36; i++) {
            s[i] = hexDigits.substr(Math.floor(Math.random() * 0x10), 1);
        }
        s[14] = "4";
        s[19] = hexDigits.substr((s[19] & 0x3) | 0x8, 1); // bits 6-7 of the clock_seq_hi_and_reserved to 01
        s[8] = s[13] = s[18] = s[23] = "-";

        var uuid = s.join("");
        return uuid;
    }

    function logoutFn(){
        $.ajax({
            url:'/logout',
            type:'post',
            dataType:'json',
            data:{
            },
            success:function(data) {
                if(data.type === 'ok') {

                    window.location="/admin/login.html";
                } else{
                    window.location="/admin/login.html";
                }
            }
        })
    }
    $(function (){
        $.ajax({
            url:'/admin/isLogin',
            type:'post',
            dataType:'json',
            data:{
            },
            success:function(data) {
                if(data.type == 'ok') {

                    //window.location="/admin";
                } else{
                    window.location="/admin/login";
                }
            }
        })
        //
       var timeId = setInterval(()=> {
            $.ajax({
            url:'/admin/czTipFn',
            type:'post',
            dataType:'json',
            data:{
            },
            success:function(data) {
                //最新统计提醒=={"type":"ok","message":{"czTipCount":11,"withdrawTipCount":62}}
                console.log("最新统计提醒=="+JSON.stringify(data));
                var czTipCount=data.message.czTipCount;
                $("#czTipCount").html(czTipCount);
                var withdrawTipCount=data.message.withdrawTipCount;
                $("#withdrawTipCount").html(withdrawTipCount);

                var onlineUserCnt=data.message.onlineUserCnt;
                $("#onlineUserCnt").html(""+onlineUserCnt);
            }
        })
       },2000)

        ///
        var socket = new SockJS('https://api0912.myshop0816.shop/market/market-ws');
        let stompClient = Stomp.over(socket);
        stompClient.connect({}, function (frame) {
            stompClient.subscribe('/topic/binance/notification', function (response) {
                let msg = JSON.parse(response.body);
                console.log("推送返回==>"+JSON.stringify(msg));//推送返回==>{"cmd":"msgNotification"}
                var cmd=msg.cmd;
                if(cmd=="msgNotification"){
                    $.ajax({
                        url:'/admin/czTipFn',
                        type:'post',
                        dataType:'json',
                        data:{
                        },
                        success:function(data) {
                            //最新统计提醒=={"type":"ok","message":{"czTipCount":11,"withdrawTipCount":62}}
                            console.log("最新统计提醒=="+JSON.stringify(data));
                            var czTipCount=data.message.czTipCount;
                            $("#czTipCount").html(czTipCount);
                            var withdrawTipCount=data.message.withdrawTipCount;
                            $("#withdrawTipCount").html(withdrawTipCount);

                            var onlineUserCnt=data.message.onlineUserCnt;
                            $("#onlineUserCnt").html(""+onlineUserCnt);
                        }
                    })
                }
            });
        });
        ///





    })
</script>
</body>

</html>