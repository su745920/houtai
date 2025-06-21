@extends('admin._layoutNew')
@section('page-head')
@endsection

@section('page-content')
<div class="layui-form">
    <div class="layui-item">
        <div class="layui-input-inline" >
            <input type="text" name="id" placeholder="请输入ID" autocomplete="off" class="layui-input" value="" style="width:100px">
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>交易员账号</label>
            <div class="layui-input-inline">
                <input type="text" name="account" placeholder="请输入手机号或邮箱" autocomplete="off" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>真实姓名</label>
            <div class="layui-input-inline">
                <input type="text" name="name" placeholder="真实姓名" autocomplete="off" class="layui-input" value="">
            </div>
        </div>


        <div class="layui-input-inline" style="margin-left: 10px;">
            <input type="text" name="start_time" id="start_time" placeholder="注册开始时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline" style="margin-left: 10px;">
            <input type="text" name="end_time" id="end_time" placeholder="注册结束时间" autocomplete="off" class="layui-input" value="">
        </div>






        <div class="layui-inline" style="margin-left: 10px;">
            <label>风控类型</label>
            <div class="layui-input-inline" style="width: 90px">
                <select name="risk" lay-verify="required" id="risk">
                    <option value="-2">全部</option>
                    <option value="0">无</option>
                    <option value="-1">亏损</option>
                    <option value="1">盈利</option>
                </select>
            </div>
            <button class="layui-btn layui-btn-primary" id="btn-set" type="button" style="padding:0px; margin-left: -4px; width: 30px;">
                <i class="layui-icon layui-icon-set-fill"></i>
            </button>
        </div>
        <div class="layui-btn-group">
            <!--<button class="layui-btn layui-btn-primary" onclick="javascrtpt:window.location.href='{{url('/admin/user/csv')}}'"> <i class="layui-icon  layui-icon-export"></i></button>-->
            <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon layui-icon-search"></i> </button>
        </div>
    </div>
</div>
<table id="userlist" lay-filter="userlist"></table>
@endsection

@section('scripts')
<script type="text/html" id="barDemo">
{{--    @if(strpos($authorityList,"9104")>0)--}}
{{--    <a class="layui-btn layui-btn-xs" lay-event="users_wallet">钱包管理</a>--}}
{{--    @endif--}}

    <!-- <a class="layui-btn layui-btn-xs layui-btn-normal" lay-event="candy_change">通证调节</a> -->
    @if(strpos($authorityList,"9103")>0)
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
    @endif

{{--    @if(strpos($authorityList,"9103")>0)--}}
{{--    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="editFKRatio">风控比例</a>--}}
{{--    @endif--}}

    <!--
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
    -->
</script>
<script type="text/html" id="switchTpl">

    @if(strpos($authorityList,"9103")>0)
    <input type="checkbox" name="status" value="@{{  d.id }}" lay-skin="switch" lay-text="启用|冻结" lay-filter="status"  @{{ d.status == 1 ? 'checked' : '' }} >

    @endif



</script>
<script type="text/html" id="isAtelier">
    <input type="checkbox" name="is_atelier" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_atelier" @{{ d.is_atelier == 1 ? 'checked' : '' }} disabled>
</script>
<script type="text/html" id="allowExchange">
    <input type="checkbox" name="type" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="allowExchange" @{{ d.type == 1 ? 'checked' : '' }} >
</script>
<script id="img">

    <div>
        <img style="height:28px;width:28px;border-radius:50%;line-height:28px!important;" src="@{{ d.head_portrait }}"></img>
    </div>

</script>

<script>
    window.onload = function() {
        document.onkeydown=function(event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if(e && e.keyCode==13) { // enter 键
                $('#mobile_search').click();
            }
        };
        layui.use(['element', 'form', 'layer', 'table', 'laydate'], function () {
            var element = layui.element
                ,layer = layui.layer
                ,table = layui.table
                ,$ = layui.$
                ,form = layui.form

            var laydate = layui.laydate;
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });


            //会员关系查看
            $('#user_relation').click(function () {
                parent.layer.open({
                    type: 2
                    ,title: '会员关系查看'
                    ,content: '/admin/invite/childs'
                    ,area: ['600px', '800px']
                    ,maxmin: true
                    ,shade: 0.4
                    ,zIndex: parent.layer.zIndex
                });
            });
            /*$('#add_user').click(function(){layer_show('添加会员', '/admin/user/add');});*/

            form.on('submit(mobile_search)',function(obj){
                var id =  $("input[name='id']").val();
                var account =  $("input[name='account']").val();
                var url="{{url('/admin/copyTrade/tradePageData')}}?a=a";
                if(id!=null&&id!=0&&id.length>0){
                    url=url+"&id="+id;
                }
                if(account!=null&&account.length>0){
                    url=url+"&account="+account;
                }
                var  start_time=$("#start_time").val();
                if(start_time!=null&&start_time.length>0){
                    url=url+"&start_time="+start_time;
                }
                var  end_time=$("#end_time").val();
                if(end_time!=null&&end_time.length>0){
                    url=url+"&end_time="+end_time;
                }

                var risk = $('#risk').val();
                if(risk!=null){
                    url=url+"&risk="+risk;
                }
                tbRend(url);
                return false;
            });
            function tbRend(url) {
                table.render({
                    elem: '#userlist'
                    ,toolbar: true
                    ,url: url
                    ,page: true
                    ,limit: 12
                    ,height: 'full-60'
                    ,cols: [[
                        {field: '', type: 'checkbox'}
                        ,{field: 'id', title: 'ID', width: 60}
                        ,{field:'account_number', title:'交易员账号', width: 200}
                        ,{field:'avatar', title: '头像', width:  100, templet: '#img'}
                       ,{field:'nickname', title:'昵称', width: 90}
                        ,{field:'incomerate', title:'平均收益率', width: 150}
                        ,{field:'allincome', title:'累计带单收益', width: 150,}
                        ,{field:'allmoney', title:'资金管理规模', width: 150,}

                        ,{field: 'winrate', title: '胜率', width:  150}
                        ,{field:'transactioncount', title:'交易笔数', width:150}
                        ,{field: 'follow_num', title: '累计跟单人数', width: 150}

                        // ,{field:'top_upnumber', title:'团队充值业绩', width: 130}
                        // ,{field:'zhitui_real_number', title:'直推实名人数', width: 130}
                        // ,{field:'real_teamnumber', title:'团队实名人数', width: 130}
                        // ,{field:'status', title:'工作室', width: 90, templet: '#isAtelier'}
                        // ,{field:'type', title: '积分兑换', width:  100, templet: '#allowExchange'}
                        ,{field: 'credit_score', title: '信用分', width:  150}
                        ,{field:'status', title:'状态', width: 120, templet: '#switchTpl'}

                        ,{field: 'remark', title: '备注', width: 150}
                        ,{field:'time', title:'注册时间', width: 170}
                        ,{fixed: 'right', title: '操作', width: 90, align: 'center', toolbar: '#barDemo'}
                    ]]
                });
            }
            tbRend("{{url('/admin/copyTrade/tradePageData')}}");

            //监听锁定操作
            form.on('switch(status)', function(obj){
                var id = this.value;

                $.ajax({
                    url:"{{url('admin/user/lock')}}",
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        layer.msg(res.message);

                    }
                });
            });
            $('#btn-set').click(function () {
                var checkStatus = table.checkStatus('userlist');
                var risk = $('#risk').val();
                var ids = [];
                try {
                    if (checkStatus.data.length <= 0) {
                        throw '请先选择用户';
                    }
                    if (risk <= -2) {
                        throw '请选择风控类型';
                    }
                    checkStatus.data.forEach(function (item, index, arr) {
                        ids.push(item.id);
                    });
                    $.ajax({
                        url: '/admin/user/batch_risk'
                        ,type: 'POST'
                        ,data: {risk: risk, ids: ids}
                        ,success: function (res) {
                            layer.msg(res.message, {
                                time: 2000,
                                end: function () {
                                    if (res.type == 'ok') {
                                        table.reload('userlist');
                                    }
                                }
                            });
                        }
                        ,error: function (res) {
                            layer.msg('网络错误');
                        }
                    })

                } catch (error) {
                    layer.msg(error);
                }
            });
            form.on('switch(allowExchange)', function (obj) {
                console.log(obj);
                var id = this.value;
                $.ajax({
                    url: '/admin/user/allow_exchange',
                    type:'post',
                    dataType:'json',
                    data:{id: id},
                    success:function (res) {
                        layer.msg(res.message);
                    }
                });
            });

            //监听工具条
            table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'delete') { //删除
                    layer.confirm('真的要删除吗？', function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "{{url('admin/user/del')}}",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'ok') {
                                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                    layer.close(index);
                                } else {
                                    layer.close(index);
                                    layer.alert(res.message);
                                }
                            }
                        });
                    });
                } else if (layEvent === 'edit'){ //编辑
                    layer_show('编辑交易员资料',"{{url('admin/copyTrade/edit')}}?id="+data.id);
                } else if (layEvent === 'editFKRatio'){ //编辑
                    layer_show('编辑风控比例',"{{url('admin/user/editFKRatio')}}?id="+data.id);
                }



                else if (layEvent === 'users_wallet') {
                    var index = layer.open({
                        title: '钱包管理'
                        , type: 2
                        , content: "{{url('/admin/user/users_wallet')}}?id=" + data.id
                        , maxmin: true
                    });
                    layer.full(index);
                }else if (layEvent === 'address') {
                    var index = layer.open({
                        title: '提币管理'
                        , type: 2
                        , content: "{{url('/admin/user/address')}}"
                        , maxmin: true
                    });
                    layer.full(index);
                }else if (layEvent == 'candy_change') {
                    var index = layer.open({
                        title: '通证调节'
                        , type: 2
                        , content: '/admin/user/candy_conf/' + data.id
                        , maxmin: true
                    });
                    layer.full(index);
                }
            });
        });
    }
</script>
@endsection