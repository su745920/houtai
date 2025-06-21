@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')


    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">开始日期</label>
                        <div class="layui-input-block">
                            <input type="text" name="start" id="datestart" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">结束日期</label>
                        <div class="layui-input-block">
                            <input type="text" name="end" id="dateend" placeholder="yyyy-MM-dd" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="account_number" placeholder="请输入" autocomplete="off"
                                   class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-block" style="width:130px;">
                            <select name="currency_id" >
                                <option value="-1" class="ww">全部</option>
                                @foreach ($legal_currencies as $currency)
                                    <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                                @endforeach
                            </select>
                        </div>
                   </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="san-user-search">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                        <!-- <button class="layui-btn layuiadmin-btn-useradmin"  onclick="javascript:window.location.href='/order/users_excel'">导出Excel</button> -->
                        <button class="layui-btn layui-btn-normal dao" lay-event="excel">导出表格</button>
                    </div>
                </div>
            </div>
            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <ul class="layui-row layui-col-space10 layui-this">
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('总用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总用户数：</h3>
                                <p><cite style="color:#fff" id="_num">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>代理商用户数</h3>
                                <p><cite style="color:#fff" id="_daili">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总入金</h3>
                                <p><cite style="color:#fff" id="_ru">0</cite></p>
                            </a>
                        </li>
                        <li class="layui-col-xs3">
                            <a href="javascript:;" onclick="layer.tips('代理商用户数', this, {tips: 3});" class="layadmin-backlog-body" style="color: #fff;background-color: #01AAED;">
                                <h3>总出金</h3>
                                <p><cite style="color:#fff" id="_chu">0</cite></p>
                            </a>
                        </li>
                        
                    </ul>
                </div>
            </div>


            <div class="layui-card-body">
                <div class="layui-carousel layadmin-backlog" style="background-color: #fff">
                    <table id="san-user-manage" lay-filter="san-user-manage"></table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/html" id="table-useradmin-webuser">
        <a class="layui-btn layui-btn-xs" lay-event="users_wallet">钱包管理</a>
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="editFKRatio">秒合约控盘</a>
        <a class="layui-btn layui-btn-xs" lay-event="recharge_usdt">折合USDT总充值</a>
        <a class="layui-btn layui-btn-xs" lay-event="withdraw_usdt">折合USDT总提现</a>
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="wallet_info">查看资金</a>
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="order">查看订单</a>
        <a class="layui-btn layui-btn-xs" lay-event="send_mail">站内信发送</a>
    </script>


<script>
    layui.use(['index','laydate','form','table'], function () {
            var $ = layui.$
                ,admin = layui.admin
            , table = layui.table
            , layer = layui.layer
            , laydate = layui.laydate
            , form = layui.form;


        //日期
        laydate.render({
            elem: '#datestart'
        });
        laydate.render({
            elem: '#dateend'
        });

        var parent_id = {{ $parent_id }};
        console.log(parent_id);

        admin.req( {
            type : "POST",
            url : '/agent/get_user_num',
            dataType : "json",
            data : {all : 1 , parent_id : parent_id},
            done : function(result) { //返回数据根据结果进行相应的处理
                $("#_num").html(result.data._num);
                $("#_daili").html(result.data._daili);
                $("#_ru").html(result.data._ru);
                $("#_chu").html(result.data._chu);
               
            }
        });

        load(parent_id);

        function load(parent_id) {
            parent_id = parent_id || 0;

            table.render({
                elem: '#san-user-manage'
                , url: '/agent/user/lists?parent_id=' + parent_id //模拟接口
                , cols: [[
                     {fixed: 'left',field: '', type: 'checkbox'}
                    ,{fixed: 'left',field: 'id', title: '用户ID', width: 100}
                    , {field: 'account_number', title: '用户名', minWidth: 150}
                    , {field: 'my_agent_level', title: '用户身份' , width : 120}
                    , {field: 'card_id', title: '身份证号' , width : 180}
                    , {field: 'parent_name', title: '上级代理商' , width : 120}
                    ,{field: 'remark', title: '备注', width: 90}
                    , {field: 'phone', title: '手机号', minWidth: 150}
                    , {field: 'email', title: '邮箱', minWidth: 150}
                    , {field: 'extension_code', title: '邀请码', minWidth: 150}
                    , {field: 'create_date', title: '加入时间', sort: true, width: 170}
                    , {title: '操作', width: 700, align: 'center', toolbar: '#table-useradmin-webuser'}
                ]]
                , page: true
                , limit: 30
                , height: 'full-320'
                , text: '对不起，加载出现异常！'
                // , headers: { //通过 request 头传递
                //     access_token: layui.data('layuiAdmin').access_token
                // }
                // , where: { //通过参数传递
                //     access_token: layui.data('layuiAdmin').access_token
                //     ,parent_id : parent_id
                // }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                    if (res !== 0) {
                        if (res.code === 1001) {
                            //清空本地记录的 token，并跳转到登入页
                            admin.exit();
                        }
                    }
                }
            });
        }


        table.on('tool(san-user-manage)', function (obj) {
            var event = obj.event;
            var data = obj.data;

            if (event == 'order') {
                //查看订单
                
                // layer.open({
                //         title: '查看杠杆订单'
                //         , type: 2
                //         , content: '{{url('/agent/user/lever_order')}}?id=' + data.id
                //         // , maxmin: true
                //         ,area: ['1000px', '600px']
                //     });
                
                 layer.open({
                        title: '查看秒合约订单'
                        , type: 2
                        , content: '{{url('/agent/user/micro_order')}}?id=' + data.id
                        // , maxmin: true
                        ,area: ['1000px', '600px']
                    });
            }
            if (event == 'wallet_info') {
                //查看资金
                
                layer.open({
                        title: '查看资金'
                        , type: 2
                        , content: '{{url('/agent/user/users_wallet')}}?id=' + data.id
                        // , maxmin: true
                        ,area: ['800px', '600px']
                    });
                
               
            }
             if (event === 'users_wallet') {
                var index = layer.open({
                    title: '钱包管理'
                    , type: 2
                    , content: "{{url('/agent/users_wallet')}}?id=" + data.id
                    , maxmin: true
                });
                layer.full(index);
            }
            if (event === 'edit'){ //编辑
             layer.open({
                    title: '编辑会员'
                    , type: 2
                    , content: "{{url('agent/user/edit')}}?id="+data.id
                    , maxmin: true
                    ,area: ['1000px', '600px']
                });
            }
            if (event === 'editFKRatio'){ //编辑
            layer.open({
                    title: '编辑风控比例'
                    , type: 2
                    , content: "{{url('agent/user/editFKRatio')}}?id="+data.id
                    , maxmin: true
                    ,area: ['1000px', '600px']
                });
            }
            
            if (event === 'recharge_usdt') {
                  // 弹出层显示“加载中”
                  var index = layer.load(1, {
                    shade: [0.1,'#fff'] //0.1透明度的白色背景
                  });
                    $.ajax({
                        url: "/agent/user/recharge_usdt",
                        data: {
                            id: data.id,
                        },
                        success: function (res) {
                            if(res.code == 0) {
                                layer.alert('折合USDT总充值: ' + res.data);
                            }
                            // 关闭加载层
                            layer.close(index);
                        },
                        error: function () {
                             // 关闭加载层
                            layer.close(index);
                        }
                    })
                }
                if (event === 'withdraw_usdt') {
                  // 弹出层显示“加载中”
                  var index = layer.load(1, {
                    shade: [0.1,'#fff'] //0.1透明度的白色背景
                  });
                    $.ajax({
                        url: "/agent/user/withdraw_usdt",
                        data: {
                            id: data.id,
                        },
                        success: function (res) {
                            if(res.code == 0) {
                                layer.alert('折合USDT总提现: ' + res.data);
                            }
                            // 关闭加载层
                            layer.close(index);
                        },
                        error: function () {
                             // 关闭加载层
                            layer.close(index);
                        }
                    })
                }
            
            if (event === 'send_mail'){ //发送站内信
                layer.open({
                    title: '发送站内信'
                    , type: 2
                    , content: "{{url('agent/user/send_mail_index')}}?id="+data.id
                    , maxmin: true
                    ,area: ['1000px', '600px']
                });
            }
           
            if (event == 'son') {
                load(data.id);
            }
        });


        form.render(null, 'layadmin-userfront-formlist');

        //监听搜索
        form.on('submit(san-user-search)', function (data) {
            var field = data.field;


            admin.req( {
                type : "POST",
                url : '/agent/get_user_num',
                dataType : "json",
                data : field,
                done : function(result) { //返回数据根据结果进行相应的处理
                    $("#_num").html(result.data._num);
                    $("#_daili").html(result.data._daili);
                    $("#_ru").html(result.data._ru);
                    $("#_chu").html(result.data._chu);
                }
            });

            //执行重载
            table.reload('san-user-manage', {
                where: field
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , done: function (res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }
                    if (res.code === 1) {
                        layer.msg(res.msg, {icon: 5});
                    }
                }
            });
        });

        //导出表格
        $('.dao').click(function () {
            var id = $('input[name="id"]').val();
            var account_number = $('input[name="account_number"]').val();
            var start = $('input[name="start"]').val();
            var end = $('input[name="end"]').val();

            var url='/agent/users_excel?id='+id+'&account_number='+account_number+'&start='+start+'&end='+end;
            window.open(url);

        })

    });
</script>

@endsection