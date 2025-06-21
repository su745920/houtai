@extends('admin._layoutNew')


@section('page-head')

@endsection

@section('page-content')
    <div class="layui-form">
        <div class="layui-item">



{{--            <div class="layui-input-inline" style="margin-left: 10px;">--}}
{{--                <input type="text" name="start_time" id="start_time" placeholder="开始时间" autocomplete="off" class="layui-input" value="">--}}
{{--            </div>--}}
{{--            <div class="layui-input-inline" style="margin-left: 10px;">--}}
{{--                <input type="text" name="end_time" id="end_time" placeholder="结束时间" autocomplete="off" class="layui-input" value="">--}}
{{--            </div>--}}






            <div class="layui-inline" style="margin-left: 10px;">
                <label>币种</label>
                <div class="layui-input-inline" style="width: 90px">
                    <!--<select name="symbol" lay-verify="required" id="symbol">-->
                    <!--    <option value="btc">BTC</option>-->
                    <!--    <option value="aoq">AOQ</option>-->
                    <!--    <option value="lpq">LPQ</option>-->
                    <!--    <option value="bicn">BICN</option>-->
                    <!--</select>-->
                      <select name="symbol" lay-verify="required" id="symbol" lay-search>
                        @foreach ($currencies as $currency)
                        <option value="{{$currency->name}}">{{$currency->name}}</option>
                        @endforeach
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
       

       
        @if(strpos($authorityList,"9103")>0)
           <!-- <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a> -->
        @endif

        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
    </script>
    <script type="text/html" id="switchTpl">

        @if(strpos($authorityList,"9103")>0)
            <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="启用|冻结" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
            @@else
            <input type="checkbox" name="status" value="@{{d.id}}"  disabled lay-skin="switch" lay-text="启用|冻结" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
        @endif



    </script>
    <script type="text/html" id="isAtelier">
        <input type="checkbox" name="is_atelier" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_atelier" @{{ d.is_atelier == 1 ? 'checked' : '' }} disabled>
    </script>
    <script type="text/html" id="allowExchange">
        <input type="checkbox" name="type" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="allowExchange" @{{ d.type == 1 ? 'checked' : '' }} >
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
                    var symbol=$("#symbol").val();
                     var urlStr="/admin/hqControl/historyListAjax?symbol="+symbol+"&fromTime=1&toTime=2693422180000&period=1m"
                    
                    tbRend(urlStr);
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
                            ,{field: 'date', title: '时间', width: 160}
                            ,{field:'open', title:'开盘价格', width: 200}
                            ,{field:'close', title:'收盘价格', width: 200}

                           ,{field:'symbol', title:'币种', width: 90}
                          
                            ,{field: 'remark', title: '备注', width: 90}
                          
                            ,{fixed: 'right', title: '操作', width: 220, align: 'center', toolbar: '#barDemo'}
                        ]]
                    });
                }
                var symbol=$("#symbol").val();
                tbRend("/admin/hqControl/historyListAjax?symbol="+symbol+"&fromTime=1&toTime=2693422180000&period=1m");

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
                                url: "{{url('admin/hqControl/historyDelete')}}",
                                type: 'post',
                                dataType: 'json',
                                data: {id: data.id},
                                success: function (res) {
                                    if (res.type == 'ok') {
                                        obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                        layer.msg(res.message);
                                        layer.close(index);
                                    } else {
                                        layer.close(index);
                                        layer.alert(res.message);
                                    }
                                }
                            });
                        });
                    } else if (layEvent === 'edit'){ //编辑
                        layer_show('编辑会员',"{{url('admin/user/edit')}}?id="+data.id);
                    } else if (layEvent === 'editFKRatio'){ //编辑
                        layer_show('编辑风控比例',"{{url('admin/user/editFKRatio')}}?id="+data.id);
                    }
                });
            });
        }
    </script>
@endsection