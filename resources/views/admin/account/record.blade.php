@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <style>
        .layui-table-select{
            background-color: #e2e2e2;
        }
    </style>
    <div style="margin-top: 10px;width: 100%;margin-left: 0px;">
        <div class="layui-form-item">
        <form class="layui-form layui-form-pane layui-inline" action="">
            <div class="layui-inline">
                <label class="layui-form-label">用户账号&nbsp;&nbsp;</label>
                <div class="layui-input-inline" style="width:130px;">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">创建时间：</label>
                <div class="layui-input-inline" style="width:120px;">
                    <input type="text" class="layui-input" id="created_at" value="" name="created_at">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">更新时间：</label>
                <div class="layui-input-inline" style="width:120px;">
                    <input type="text" class="layui-input" id="updated_at" value="" name="updated_at">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px;">
                <label class="layui-form-label" style="width: 80px;">状态</label>
                <div class="layui-input-inline" style="width:100px;">
                    <select name="status" id="status">
                        <option value="0">所有</option>
                        <option value="1">待审核</option>
                        <option value="2">已通过</option>
                        <option value="3">已驳回</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
        </form>
    </div>
    <script type="text/html" id="statustml">
        @{{d.state==1 ? '<span class="layui-badge layui-bg-green">'+'待审核'+'</span>' : '' }}
        @{{d.state==2 ? '<span class="layui-badge layui-bg-red">'+'已通过'+'</span>' : '' }}
        @{{d.state==3 ? '<span class="layui-badge layui-bg-black">'+'未通过'+'</span>' : '' }}

    </script>
    
    
    <table id="data_table" lay-filter="data_table" type="text/html">
    </table>
    @endsection
    @section('scripts')
    <script type="text/html" id="operateBar">
            @{{# if (d.state == 1) { }}
                <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="adopt">通过</a>
                <a class="layui-btn layui-btn-xs layui-btn" lay-event="refuse">拒绝</a>
            @{{# } }}
        </script>
    <script>
                
        function showVoucher(imgSrc){
            layer.open({
                type:1
                ,title:false
                ,closeBtn:0
                ,skin:'layui-layer-nobg'
                ,shadeClose:true
                ,content:'<img style="width:100%;height:100%;" class="layui-upload-img" src="'+ imgSrc +'"/>'
                ,scrollbar:false
            })
        }

    layui.use(['table','form','laydate'], function(){
        var table = layui.table
            ,$ = layui.jquery
            ,form = layui.form
            ,laydate = layui.laydate
            ,layer = layui.layer
        //第一个实例
        laydate.render({
            elem: '#created_at'
        });
        laydate.render({
            elem: '#updated_at'
        });
        //第一个实例
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/account/record/lists'
            ,page: true
            ,id:'mobileSearch'
            ,cols: [[
                {field: 'id', title: 'id', width: 90}
                ,{field: 'phone', title: '邮箱', width: 180}
                ,{field: 'currency', title: '币种', width: 120}
                ,{field: 'address', title: '充币地址', width: 300}
                ,{field: 'money', title: '充币数量', width: 120}
                ,{field: 'twd', title: '总金额', width: 120}
                ,{field: 'state',title: '状态', width:90,templet: '#statustml'}
                ,{field: 'voucher', title: '凭证', width: 160, templet: '<div><img style="cursor:pointer" onclick="showVoucher(\'@{{d.voucher}}\')" src=@{{d.voucher}}></img></div>'}
                ,{field: 'created_at',title: '创建时间', width:160}
                ,{field: 'updated_at',title: '更新时间', width:200}
                ,{fixed: 'right', title: '操作', width: 150, align: 'center', toolbar: '#operateBar'}
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

        //触发行单击事件
        table.on('row(data_table)', function(obj){
            console.log(obj.tr) //得到当前行元素对象
            $(obj.tr).addClass('layui-table-select').siblings().removeClass('layui-table-select');
            
        });

        table.on('tool(data_table)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
        console.log(123);
                    var data = obj.data
                        ,layEvent = obj.event
                        ,tr = obj.tr;
                    if (layEvent === 'adopt') { //通过
                        layer.confirm('真的要通过充币申请吗？', function (index) {
                                $.ajax({
                                url: "/admin/account/change_state",
                                type: 'post',
                                dataType: 'json',
                                data: {id: data.id,state: 2},
                                success: function (res) {
                                    layer.msg(res.message);
                                    if (res.type == 'ok') {
                                        data_table.reload();
                                    } else {
                                        layer.close(index);
                                    }
                                }
                            });
                        });
                    }else if(layEvent === 'refuse'){
                        layer.confirm('真的要拒绝充币申请吗？', function (index) {

                            $.ajax({
                                url: "/admin/account/change_state",
                                type: 'post',
                                dataType: 'json',
                                data: {id: data.id,state: 3},
                                success: function (res) {
                                    layer.msg(res.message);
                                    if (res.type == 'ok') {
                                        data_table.reload();
                                    } else {
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