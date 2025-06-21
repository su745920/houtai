@extends('admin._layoutNew')

@section('page-head')
<style>
    .reset-color {
        background: #ffbe76;
    }
</style>
@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 0px;">
        <div class="layui-tab">
            <ul class="layui-tab-title">
                <li><a href="/admin/account/recharge_v2">充币待审核</a></li>
                <li  class="layui-this">充币通过记录</li>
                <li><a href="/admin/account/recharge_v2_fail">充币失败记录</a></li>

            </ul>
            <div class="layui-form-item" style="margin-top: 3px;">
                <label class="layui-form-label">充币总额</label>
                <div class="layui-input-block" style="width:50%">
                    <blockquote class="layui-elem-quote layui-quote-nm" id="sum">0</blockquote>
                </div>
            </div>
            <form class="layui-form layui-form-pane layui-inline" action="">
                <div class="layui-input-inline" >
                    <input type="text" name="user_id" placeholder="请输入用户ID" autocomplete="off" class="layui-input" value="" style="width:100px">
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">币种&nbsp;&nbsp;</label>
                    <div class="layui-input-inline" style="width:130px;">
                        <select name="currency" id="type_type" lay-search>
                            <option value="-1" class="ww">全部</option>
                            @foreach ($currencies as $currency)
                                <option value="{{$currency->id}}" class="ww">{{$currency->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">用户账号&nbsp;&nbsp;</label>
                    <div class="layui-input-inline" style="width:130px;">
                        <input type="text" name="account_number" autocomplete="off" class="layui-input">
                    </div>
                </div>

                <div class="layui-inline">
                    <label class="layui-form-label">审核状态&nbsp;&nbsp;</label>
                    <div class="layui-input-inline" style="width:130px;">
                        <select name="status" id="status" lay-search>
                            <option value="" class="ww">全部</option>

                            <option value="-0" class="0">待审核</option>
                            <option value="1" class="1">审核通过</option>
                            <option value="2" class="2">审核被拒绝</option>

                        </select>
                    </div>
                </div>

                <div class="layui-inline">
                    <label class="layui-form-label" style="width:130px;">申请开始日期：</label>
                    <div class="layui-input-inline" style="width:120px;">
                        <input type="text" class="layui-input" id="start_time" value="" name="start_time">
                    </div>
                </div>

                <div class="layui-inline">
                    <label class="layui-form-label" style="width:130px;">申请结束日期：</label>
                    <div class="layui-input-inline" style="width:120px;">
                        <input type="text" class="layui-input" id="end_time" value="" name="end_time">
                    </div>
                </div>

                <div class="layui-inline">
                    <div class="layui-input-inline">
                        <button class="layui-btn" lay-submit="" lay-filter="search"><i class="layui-icon">&#xe615;</i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <table id="data_table" lay-filter="data_table"></table>
   
@endsection
@section('scripts')
    <script type="text/html" id="barDemo">
        @if(strpos($authorityList,"2952")>0)
             @{{d.status==1 ? '<a class="layui-btn layui-btn layui-btn-xs reset-color" lay-event="reset">重置订单</a>' : '' }}
        @endif

    </script>
    <script>
        
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(context, args);
                }, wait);
            };
        }
    
        layui.use(['table','form','laydate'], function(){
            var table = layui.table
                ,$ = layui.jquery
                ,form = layui.form
                ,laydate = layui.laydate
                ,layer = layui.layer
            //第一个实例
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });
            
            
            
            
            
            //第一个实例
            var data_table = table.render({
                elem: '#data_table'
                ,url: '/admin/account/recharge_v2_success/page'
                ,page: true
                ,cols: [[
                    {field: 'id', title: 'id', width: 50}
                     ,{field: 'user_id', title: '用户ID', width:100}
                    ,{field: 'order_no',title: '订单编码', width:200}
                   ,{field: 'email',title: '邮箱', width:220}
                    ,{field: 'account_number', title: '账号', width: 200}
                    ,{field: 'currency_name', title: '币种', width: 120}

                    ,{field: 'money', title: '充值金额', width: 160}
                    , {field: 'voucher', title: '凭证', align: 'center',width: 300,
                        templet: '<div><img src=@{{d.voucher}}  onclick=window.open("@{{d.voucher}}")></img></div>'
                    }
                    ,{field: 'channel',title: '渠道', width:200}

                    ,{field: 'created_at', title: '充值时间', width: 180}
                    ,{field: 'remark', title: '备注', width: 180}
                    ,{field: 'statusZH', title: '状态', width: 180}
                    ,{field: 'notes', title: '反馈信息', width: 180}
                    ,{fixed: 'right', title: '操作', width: 220, align: 'center', toolbar: '#barDemo'}

                ]]  , done: function(res){
                    $("#sum").text(res.extra_data);
                }
            })



            //监听提交
            form.on('submit(search)', function(data) {
                table.reload('data_table', {
                    where: data.field,
                    page: {curr: 1}//重新从第一页开始
                });
                return false;
            });
            table.on('tool(data_table)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'reset') { //通过
                    layer.confirm('确定要重置订单吗？', debounce(function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "{{url('admin/account/v2_reset')}}",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                layer.msg(res.message);
                                if(res.type == 'ok'){
                                    layer.close(index);
                                    setTimeout(()=> {
                                        location.reload();
                                    },800)
                                }
                            }
                        });
                    },800));
                } else if (layEvent === 'jj') {
                    layer.confirm('真的要拒绝充币申请吗？', function (index) {

                        $.ajax({
                            url: "{{url('admin/account/v2_jj')}}",
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