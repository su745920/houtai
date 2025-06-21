
@extends('agent.layadmin')

@section('title', '充币列表')

@section('page-head')

@endsection

@section('page-content')

<div class="layui-fluid">
    <div class="layui-card">
        <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
            <div class="layui-form-item">
                
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
                    <label class="layui-form-label">用户名</label>
                    <div class="layui-input-block">
                        <input type="text" name="account_number" placeholder="请输入" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">所属代理</label>
                    <div class="layui-input-block" style="width:130px;">
                        <select name="belong_agent" >
                            <option value="" >全部</option>
                            @foreach ($son_agents as $son)
                                <option value="{{$son->username}}">{{$son->username}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="layui-inline">
                    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="LAY-user-front-search">
                        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="layui-card-body">
            <table id="LAY-user-manage" lay-filter="LAY-user-manage"></table>
            
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/html" id="barDemo">
    @{{d.status==0 ? '<a class="layui-btn layui-btn layui-btn-xs" lay-event="tg">审核通过</a>' : '' }}
    @{{d.status==0 ? '<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="jj">拒绝</a>' : '' }}
</script>
<script>
    layui.use(['index','table' , 'layer'], function() {
        var $ = layui.$
            ,admin = layui.admin
            ,view = layui.view
            ,table = layui.table
            ,form = layui.form
            
       
     
        
        //充币管理
        table.render({
            elem: '#LAY-user-manage'
            ,method : 'get'
            ,url: '/agent/capital/recharge'
            ,toolbar: true
            ,totalRow: true
            ,cols: [[
                {type: 'checkbox', fixed: 'left'}
                ,{field: 'user_id', title: '用户ID', width:100}
                ,{field: 'currency_name', title: '币种', width: 90}
                ,{field: 'account_number', title: '用户名', width: 120, totalRowText: '小计'}
                ,{field: 'belong_agent_name', title: '所属代理', width: 120}
                ,{field: 'order_no',title: '订单编码', width:200}
                ,{field: 'email',title: '邮箱', width:220}
                ,{field: 'money', title: '充值金额', width: 160}
                , {field: 'voucher', title: '凭证', align: 'center',width: 300,
                    templet: '<div><img src=@{{d.voucher}}  onclick=window.open("@{{d.voucher}}")></img></div>'
                }
                ,{field: 'channel',title: '渠道', width:200}
                ,{field: 'created_at', title: '充值时间', width: 180}
                 ,{field: 'remark', title: '备注', width: 180}
                ,{field: 'statusZH', title: '状态', width: 180}
                ,{ title: '操作', width: 220, align: 'center', toolbar: '#barDemo'}
            ]]
            ,page: true
            ,limit: 30
            ,height: 'full-240'
            ,text: '对不起，加载出现异常！'
            // ,headers: {
            //     //通过 request 头传递
            //     access_token: layui.data('layuiAdmin').access_token
            // }
            // ,where: {
            //     //通过参数传递
            //     access_token: layui.data('layuiAdmin').access_token
            // }
            ,done: function(res) { //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
                if (res !== 0 ){
                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }
                }
            }
        });

        form.render(null, 'layadmin-userfront-formlist');

        //监听搜索
        form.on('submit(LAY-user-front-search)', function(data){
            var field = data.field;

            //执行重载
            table.reload('LAY-user-manage', {
                where: field
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
                ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行

                    if (res.code === 1001) {
                        //清空本地记录的 token，并跳转到登入页
                        admin.exit();
                    }

                    if (res.code === 1){
                        layer.msg(res.msg ,{icon : 5});
                    }
                }
            });
        });
        
         let isClick = false
            table.on('tool(LAY-user-manage)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                if (layEvent === 'tg') { //通过
                layer.prompt({
                    title: '真的要通过充币申请吗?',
                    formType: 2,
                    yes: function(index,layero) {
                        let text = layero.find(".layui-layer-input").val();
                        //向服务端发送删除指令
                        if(isClick) {
                            layer.msg("请不要频繁点击");
                            return
                        }
                        isClick = true
                        $.ajax({
                                url: "{{url('agent/recharge/v2_tj')}}",
                                type: 'post',
                                dataType: 'json',
                                data: {id: data.id,notes: text},
                                success: function (res) {
                                    layer.msg(res.msg);
                                    layer.close(index);
                                    setTimeout(()=> {
                                        location.reload();
                                    },800)
    
                                }
                            });
                        setTimeout(()=> {
                            isClick = false
                        },8000)
                    }
                });
                } else if (layEvent === 'jj') {
                    layer.prompt({
                    title: '真的要拒绝充币申请吗?',
                    formType: 2,
                    yes: function(index,layero) {
                        let text = layero.find(".layui-layer-input").val();
                        //向服务端发送删除指令
                        if(isClick) {
                            layer.msg("请不要频繁点击");
                            return
                        }
                        isClick = true
                        $.ajax({
                            url: "{{url('agent/recharge/v2_jj')}}",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id,notes: text},
                            success: function (res) {
                                layer.msg(res.msg);
                                setTimeout(()=> {
                                    location.reload();
                                },800)
                            }
                        });
                        setTimeout(()=> {
                            isClick = false
                        },8000)
                    }
                });
                }
            });

    });
</script>
@endsection
