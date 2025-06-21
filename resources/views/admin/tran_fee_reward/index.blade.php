@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<div style="margin-top: 10px;width: 100%;margin-left: 10px;">

    <form class="layui-form layui-form-pane layui-inline" action="">
        <div class="layui-inline" style="margin-left: 10px;">
            <label class="layui-form-label" style="width: 80px;">状态</label>
            <div class="layui-input-inline" style="width:100px;">
                <select name="status" id="status">
                    <option value="">所有</option>
                    <option value="0">待审核</option>
                    <option value="1">已通过</option>
                    <option value="2">已驳回</option>
                </select>
            </div>
        </div>
{{--        <div class="layui-inline" style="margin-left: 10px;">--}}
{{--            <label class="layui-form-label" style="width: 80px;">币种</label>--}}
{{--            <div class="layui-input-inline" style="width:100px;">--}}
{{--                <select name="currency" id="currencies">--}}
{{--                    <option value="-1">所有</option>--}}
{{--                    @foreach ($currencies as $key => $currency)--}}
{{--                    <option value="{{$currency->id}}">{{$currency->name}}</option>--}}
{{--                    @endforeach--}}
{{--                </select>--}}
{{--            </div>--}}
{{--        </div>--}}
        <div class="layui-inline">
            <label class="layui-form-label">上级用户</label>
            <div class="layui-input-inline">
                <input type="text" name="account_number"  placeholder="享受奖励的用户名"
                       autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">开始日期：</label>
            <div class="layui-input-inline" style="width:120px;">
                <input type="text" name="start_time" class="layui-input" id="start_time" value="">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">结束日期：</label>
            <div class="layui-input-inline" style="width:120px;">
                <input type="text" name="end_time" class="layui-input" id="end_time" value="">
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
<script type="text/html" id="barDemo">
    @{{d.status==0 ? '<a class="layui-btn layui-btn-xs" lay-event="auditSuccessFn">审核通过</a>' : '' }}
    @{{d.status==0 ? '<a class="layui-btn layui-btn-xs layui-bg-red" lay-event="auditFailFn">拒绝</a>' : '' }}


</script>
<script type="text/html" id="statustml">
    @{{d.status==0 ? '<span class="layui-badge layui-bg-green">'+'待审核'+'</span>' : '' }}
    @{{d.status==1 ? '<span class="layui-badge layui-bg-red">'+'审核通过'+'</span>' : '' }}
    @{{d.status==2 ? '<span class="layui-badge layui-bg-black">'+'审核失败'+'</span>' : '' }}

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
        //第一个实例
        laydate.render({
            elem: '#start_time'
        });
        laydate.render({
            elem: '#end_time'
        });
        //第一个实例
        table.render({
            elem: '#demo'
            ,url: "{{url('admin/tranFeeReward/page')}}" //数据接口
            ,page: true //开启分页
            ,id:'mobileSearch'
            ,cols: [[ //表头
                {field: 'id', title: 'ID', width:80, sort: true}
                ,{field: 'value', title: '奖励数量', width:180}
                ,{field: 'reward_username', title: '奖励用户', width:220}
                ,{field: 'create_date', title: '奖励时间', width: 180}

                ,{field: 'tx_amount', title: '下级交易金额', width:180}
                ,{field: 'en_info', title: '下级用户', width:280}
                ,{field: 'create_date', title: '下级交易时间', width:280}



                // ,{field: 'status', title: '状态', width: 120, templet: '#statustml'}
                // ,{field: 'audit_date', title: '审核时间', width:120}
                // ,{field: 'audit_username', title: '审核人', width:120}
                // ,{title:'操作', width:180, toolbar: '#barDemo'}

            ]] , done: function(res){
                $("#sum").text(res.extra_data);
            }
        });

        table.on('tool(test)', function(obj){
            var data = obj.data;
            if(obj.event === 'auditSuccessFn'){
                layer.confirm('您确认要审核通过吗', function(index){
                    $.ajax({
                        url:'{{url('admin/czRewardGroup/tg')}}',
                        type:'post',
                        dataType:'json',
                        data:{id:data.id},
                    success:function (res) {
                        if(res.type == 'error'){
                            layer.msg(res.message);
                            location.reload();
                        }else{
                            location.reload();
                            obj.del();
                            layer.close(index);

                        }
                    }
                });
                });
            }
            else if(obj.event === 'auditFailFn'){
                layer.confirm('您确认不通过审核吗', function(index){
                    $.ajax({
                        url:'{{url('admin/czRewardGroup/reject')}}',
                        type:'post',
                        dataType:'json',
                        data:{id:data.id},
                        success:function (res) {
                            if(res.type == 'error'){
                                layer.msg(res.message);
                                location.reload();
                            }else{
                                obj.del();
                                layer.close(index);
                            }
                        }
                    });
                });
            }
        });

        //监听提交
        form.on('submit(mobile_search)', function(data) {

            table.reload('mobileSearch',{
                where: data.field,
                page: {curr: 1}         //重新从第一页开始
            });
            return false;
        });

    });
</script>

@endsection