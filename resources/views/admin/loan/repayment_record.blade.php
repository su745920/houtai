@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <form class="layui-form layui-form-pane layui-inline" action="">
            <div class="layui-inline" style="margin-left: -10px;">
                <label class="layui-form-label">用户ID</label>
                <div class="layui-input-inline">
                    <input type="text" name="user_id"  placeholder="请输入用户ID" autocomplete="off" class="layui-input">
                </div>
            </div>
            <!--<div class="layui-inline" style="margin-left: -10px;">-->
            <!--    <label class="layui-form-label">用户名</label>-->
            <!--    <div class="layui-input-inline">-->
            <!--        <input type="text" name="account_number" autocomplete="off" class="layui-input">-->
            <!--    </div>-->
            <!--</div>-->
            <div class="layui-inline" style="margin-left: -10px;">
                <label class="layui-form-label">还款订单号</label>
                <div class="layui-input-inline">
                    <input type="text" name="order_no"  placeholder="请输入还款订单号" autocomplete="off" class="layui-input">
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

    <table id="repaymentRecordlist" lay-filter="test"></table>
    <script type="text/html" id="operate">
           @{{# if (d.status == 0) { }}
            <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="pass">还款通过</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="reject">还款拒绝</a>
        @{{# } }}
   
    </script>

@endsection

@section('scripts')
    <script>
        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            
            //第一个实例
            var data_table = table.render({
                elem: '#repaymentRecordlist'
                ,url: '{{url('admin/repayment_record_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,toolbar: true
                ,height: 'full-60'
                ,cols: [[ //表头
                    {field: 'order_no', title: '还款订单号', width: 150, sort: true}
                    ,{field: 'user_id', title: '用户ID', width: 120}
                    ,{field: 'account_number', title: '账号', width: 180}
                    ,{field: 'payable_amount', title: '应还金额(USDT)', width: 150}
                    ,{field: 'residue_amount', title: '剩余待还(USDT)', width: 150}
                    ,{field: 'repayment_amount', title: '还款金额(USDT)', width: 150}
                    ,{title: '还款数量', width: 200,
                        templet: function(d) {
                            return (d.repayment_number).toFixed(6) + d.name
                        }
                    }
                    ,{field: 'repayment_voucher', title: '还款凭证', align: 'center',width: 200,
                        templet: '<div style=cursor: pointer;><img src=@{{d.repayment_voucher}}  onclick=window.open("@{{d.repayment_voucher}}")></img></div>'
                    }
                    ,{ title: '状态',templet:function(d){
                        if(d.status==0){
                            return '<span style="color:#2A64FB">待审核</span>';
                        }else if(d.status==1){
                            return '<span style="color:#00c087">还款通过</span>';
                        }else{
                            return '<span style="color:#f00">还款拒绝</span>';
                        }
                    },
                    width: 120
                    }
                    ,{field: 'created_at', title: '创建时间', width: 170}
                    ,{field: 'updated_at', title: '更新时间', width: 170}
                    ,{fixed: 'right',title: '操作', width: 200, align: 'center', templet: '#operate'}
                ]]
            });

           table.on('tool(test)', function(obj) {
                var data = obj.data;
                if(obj.event === 'pass') {
                    layer.confirm('确定还款通过吗?', function (index) {
                        $.ajax({
                            url:'/admin/repayment_record_pass',
                            type:'post',
                            dataType:'json',
                            data: {id: data.id},
                            success:function (res) {
                                layer.msg(res.message);
                                if(res.type == 'ok'){
                                    layer.close(index);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 800);
                                }
                            }
                        });
                    });
                } else if(obj.event === 'reject') {
                   layer.confirm('确定还款拒绝吗?', function (index) {
                    $.ajax({
                        url:'/admin/repayment_record_reject',
                        type:'post',
                        dataType:'json',
                        data: {id: data.id},
                        success:function (res) {
                            layer.msg(res.message);
                            if(res.type == 'ok'){
                                layer.close(index);
                                setTimeout(() => {
                                    window.location.reload();
                                }, 800);
                            }
                        }
                    });
                });
              }else if(obj.event === 'del') {
                    layer.confirm('确定要删除吗?', function(index) {
                        $.ajax({
                            url:'/admin/repayment_record_del',
                            type:'post',
                            dataType:'json',
                            data: {id: data.id},
                            success:function (res) {
                               layer.msg(res.message);
                                if(res.type == 'ok'){
                                    layer.close(index);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 800);
                                }
                            }
                        });
                    });
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data) {
                var account_number = data.field.account_number;
                table.reload('mobileSearch', {
                    where: data.field,
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection