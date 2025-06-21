@extends('admin._layoutNew')

@section('page-head')

@endsection



@section('page-content')
    <div class="layui-tab">
        <ul class="layui-tab-title">
            <li><a href="/admin/c2c_merchant/merchant_page">商户列表</a></li>
            <li class="layui-this">商户申请</li>

        </ul>
    </div>

    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">


        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline" style="margin-left: 50px;">
                <label >用户交易账号&nbsp;&nbsp;</label>
                <div class="layui-input-inline">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">开始日期：</label>
                <div class="layui-input-inline" style="width:120px;">
                    <input type="text" class="layui-input" id="start_time" value="">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">结束日期：</label>
                <div class="layui-input-inline" style="width:120px;">
                    <input type="text" class="layui-input" id="end_time" value="">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>



        </form>
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>

    <!-- <script type="text/html" id="barDemo">

        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script> -->

    <script type="text/html" id="type">
        @{{d.type=="buy" ? '<span class="layui-badge layui-bg-green">'+'卖出'+'</span>' : '' }}
        @{{d.type=="sell" ? '<span class="layui-badge layui-bg-red">'+'买入'+'</span>' : '' }}

    </script>
    <script type="text/html" id="is_sure">
        @{{d.is_sure==0 ? '<span class="layui-badge layui-bg-red">'+'未确认'+'</span>' : '' }}
        @{{d.is_sure==1 ? '<span class="layui-badge layui-bg-blue "  >'+'已确认'+'</span>' : '' }}
        @{{d.is_sure==2 ? '<span class="layui-badge layui-bg-orange">'+'已取消'+'</span>' : '' }}
        @{{d.is_sure==3 ? '<span class="layui-badge layui-bg-green">'+'已付款'+'</span>' : '' }}

    </script>

    <script type="text/html" id="barDemo">
        @if(strpos($authorityList,"2952")>0)
            @{{d.audit_status==0 ? '<a class="layui-btn layui-btn layui-btn-xs" lay-event="tg">审核通过</a>' : '' }}
            @{{d.audit_status==0 ? '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="jj">拒绝</a>' : '' }}
        @endif

    </script>

@endsection

@section('scripts')
    <script>

        layui.use(['table','form','laydate'], function(){
            var table = layui.table
                ,$ = layui.jquery
                ,form = layui.form
                ,laydate = layui.laydate
                ,layer = layui.layer
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('admin/c2c_merchant/applyPageAjax')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'user_id', title: '用户id', width:150}


                    ,{field: 'account', title: '登录账号', width:150}

                    ,{field: 'name', title: '商户名称', width:120}
                    ,{field: 'bank_account', title: '银行账号', width:120}

                    ,{field: 'mobile', title: '手机号', width:100}


                    ,{field: 'link_email', title: '邮箱', width:100}

                    ,{field: 'create_time', title: '申请时间', width:180}


                    ,{fixed: 'right', title: '操作', width: 220, align: 'center', toolbar: '#barDemo'}

                ]],done: function(res){
                    $("#sum").text(res.extra_data);
                }
            });



            //监听提交
            form.on('submit(mobile_search)', function(data){
                var seller_number = data.field.seller_number
                    ,type = $('#type_type').val()
                    ,is_sure = $('#is_sure_type').val()
                    // ,currency_id = $('#currency_id').val()
                    ,account_number = data.field.account_number
                    ,end_time=$('#end_time').val()
                    ,start_time = $('#start_time').val()


                table.reload('mobileSearch',{
                    where:{
                        account_number:account_number,
                        seller_number:seller_number,
                        start_time:start_time,
                        type:type,
                        end_time:end_time,
                        is_sure:is_sure,
                        // currency_id:currency_id,

                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });


            table.on('tool(test)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'tg') { //通过
                    layer.confirm('真的要通过C2C商家申请吗？', function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "{{url('admin/c2c_merchant/merchant_success')}}",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {

                                layer.msg(res.message);
                                location.reload();

                            }
                        });
                    });
                } else if (layEvent === 'jj') {
                    layer.confirm('真的要拒绝C2C商家申请吗？', function (index) {

                        $.ajax({
                            url: "{{url('admin/c2c_merchant/merchant_reject')}}",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {

                                layer.msg(res.message);
                                location.reload();
                            }
                        });
                    });
                }
            });

        });
    </script>

@endsection