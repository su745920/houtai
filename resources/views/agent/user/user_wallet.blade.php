@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<div class="layui-form">
    <div class="layui-inline"  style="display: none;">
        <button class="layui-btn" id="wallet_add" title="生成钱包">
            <i class="layui-icon layui-icon-add-1"></i>
            <span>生成钱包</span>
        </button>
    </div>
    <div class="layui-inline">
        <div class="layui-input-inline">
            <select name="currency_id" lay-verify="required" lay-filter="currency_id">
                <option value="0">所有</option>
                @foreach ($currencies as $currency)
                <option value="{{$currency->id}}">{{$currency->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>    
<table id="userlist" lay-filter="userlist"></table>
<input type="hidden" name="user_id" value="{{$user_id}}">
@endsection

@section('scripts')
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-xs" lay-event="conf">调节账户</a>
   {{--    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>--}}
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
</script>
<script type="text/html" id="switchTpl">
    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.status == 1 ? 'checked' : '' }} >
</script>
<script>
    window.onload = function() {
        document.onkeydown = function(event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if(e && e.keyCode==13) { // enter 键
                $('#mobile_search').click();
            }
        };
        layui.use(['element', 'form', 'layer', 'table'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var table = layui.table;
            var $ = layui.$;
            var form = layui.form;
            var user_id = $("input[name='user_id']").val()
            var data_table = table.render({
                elem: '#userlist'
                ,url: '/agent/user/walletList'
                ,page: true
                ,limit: 20
                ,toolbar: true
                ,height: 'full-100'
                ,cols: [
                    [

                        {field: 'id', title: 'id', width: 70, rowspan: 2}
                        ,{field: 'currency_name', title: '币种', width: 100, totalRowText: '小计', rowspan: 2}

                       ,{field: 'cz_amount', title: '总充值额度', width: 150, rowspan: 2}
                       
                       ,{title: '账户资产', width: 380, colspan: 3, rowspan: 1, align: "center"}
                        ,{title: '币币资产', width: 380, colspan: 3, rowspan: 1, align: "center"}
                        ,{title: '合约资产', width: 380, colspan: 3, rowspan: 1, align: "center"}
                        ,{title: '秒合约资产', width: 380, colspan: 3, rowspan: 1, align: "center"}
                        ,{title: '理财资产', width: 380, colspan: 3, rowspan: 1, align: "center"}
                        

                        ,{field: 'gl_time_str', title: '归拢时间', width: 170, hide: true, rowspan: 2}

                        ,{fixed: 'right', title: '操作', width: 260, align: 'center', toolbar: '#barDemo', rowspan: 2}

                    ], [
                        
                        {field:'legal_balance',title:'余额', width:120}
                        ,{field:'lock_legal_balance',title:'冻结', width:120}
                        ,{field:'total_micro_balance',title:'折合usdt(总)', width:120}
                        
                        ,{field:'change_balance',title:'余额', width:120}
                        ,{field:'lock_change_balance',title:'冻结', width:120}
                        ,{field:'total_change_balance',title:'折合usdt(总)', width:120}

                        

                        ,{field:'lever_balance',title:'余额', width:120}
                        ,{field:'lock_lever_balance',title:'冻结', width:120}
                        ,{field:'total_lever_balance',title:'折合usdt(总)', width:120}
                        
                        ,{field:'micro_balance',title:'余额', width:120}
                        ,{field:'lock_micro_balance',title:'冻结', width:120}
                        ,{field:'total_micro_balance',title:'折合usdt(总)', width:120}
                        
                        ,{field:'earn_balance',title:'余额', width:120}
                        ,{field:'lock_earn_balance',title:'冻结', width:120}
                        ,{field:'total_earn_balance',title:'折合usdt(总)', width:120}


                        


                    ]
                ]
                ,where: {user_id: user_id}
            });
            
            //监听锁定操作
            form.on('switch(sexDemo)', function (obj) {
                var id = this.value;
                $.ajax({
                    url: '/agent/user/wallet_lock',
                    type: 'post',
                    dataType: 'json',
                    data: {id:id},
                    success: function (res) {
                        layer.msg(res.message);
                    }
                });
            });
            form.on('select(currency_id)', function (data) {
                data_table.reload({
                    where: {user_id: user_id, currency_id: data.value}
                })
            });
            $('#wallet_add').click(function () {
                layer.confirm('确认为用户生成钱包?', function (index) {
                    layer.close(index);
                    $.ajax({
                        url: '/agent/wallet/make'
                        ,type: 'get'
                        ,data: {user_id: user_id}
                        ,success: function (res) {
                            layer.msg(res.message, {
                                end: function () {
                                    data_table.reload();
                                }
                            })
                        }
                        ,error: function (res) {
                            layer.msg('网络错误')
                        }
                    });
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
                            url: '/agent/user/delw',
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.code == 0) {
                                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                    layer.close(index);
                                } else {
                                    layer.close(index);
                                    layer.alert(res.msg);
                                }
                            }
                        });
                    });
                } else if (layEvent === 'conf') { 
                    var index = layer.open({
                        title: '调节账户'
                        ,type: 2
                        ,content: '/agent/user/conf?id=' + data.id
                        ,maxmin: true
                    });
                    layer.full(index);
                } else if (layEvent === 'edit') { //编辑
                    var index = layer.open({
                        title: '管理充币地址'
                        ,type: 2
                        ,content: '/agent/user/address?currency_name=' + data.currency_name+'&id='+data.id
                        ,maxmin: true
                    });
                    layer.full(index);
                }else if (layEvent === 'address') { //提币
                    var index = layer.open({
                        title: '管理提币'
                        ,type: 2
                        ,content: '/agent/user/address?currency_name=' + data.currency_name+'&id='+data.id
                        ,maxmin: true
                    });
                    layer.full(index);
                }
            });
        });
    }
</script>

@endsection