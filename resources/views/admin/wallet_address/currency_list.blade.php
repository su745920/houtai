@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="text-align: right;">
        <button class="layui-btn" onclick="onAdd()">新增</button>
    </div>
    <table id="walletTable" lay-filter="test"></table>
    <script type="text/html" id="switchVoucherTpl">
        <input type="checkbox" name="is_voucher" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="voucherDemo" @{{ d.is_voucher == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="switchShowTpl">
        <input type="checkbox" name="is_show" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="showDemo" @{{ d.is_show == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="barWallet">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
@endsection

@section('scripts')
    <script>
        layui.use(['element','table','form'],function () {
            var table = layui.table
                ,form = layui.form
                ,$ = layui.jquery
                ,element = layui.element
                ,index = parent.layer.getFrameIndex(window.name);
                
                table.render({
                    elem: '#walletTable'
                    ,url: '{{url('admin/wallet_address_list/lists')}}?wallet_address_id=' + "{{$wallet_address_id}}" //数据接口
                    ,page: true //开启分页
                    ,id:'mobileSearch'
                    ,cols: [[ //表头
                        {field: 'id', title: 'ID', width:60, sort: true}
                        ,{field: 'name', title: '币种名称', width:130}
                        ,{field: 'address', title: '地址', minWidth:250}
                        ,{field: 'min_limit', title: '最小额度', minWidth:50}
                        ,{field: 'max_limit', title: '最大额度', minWidth:50}
                        ,{field:'is_voucher', title:'凭证开关', width:90, templet: '#switchVoucherTpl', unresize: true}
                        ,{field:'is_show', title:'是否显示', width:90, templet: '#switchShowTpl', unresize: true}
                        ,{field: 'sort', title: '排序', width:60}
                        ,{title:'操作',toolbar: '#barWallet',minWidth:120}
    
                    ]]
                });
            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行吗？', function(index){
                        $.ajax({
                            url:'{{url('admin/wallet_address_list/del')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });
                    });
                } else if(obj.event === 'edit'){
                    layer_show('编辑地址','{{url('admin/wallet_address_list/add')}}?id='+data.id + '&wallet_address_id=' + data.wallet_address_id,800);
                }
            });
                //监听凭证开关操作
            form.on('switch(voucherDemo)', function(obj){
                var id = this.value;
                  $.ajax({
                    url:'{{url('admin/wallet_address_list/setVoucher')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                            window.location.reload();
                        }
                    }
                });
            });
            //监听是否显示操作
            form.on('switch(showDemo)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'{{url('admin/wallet_address_list/setShow')}}',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                            window.location.reload();
                        }
                    }
                });
            });
        });
         function onAdd() {
            layer_show('添加币种','{{url('admin/wallet_address_list/add')}}?wallet_address_id='+"{{$wallet_address_id}}",800)
        }
    </script>

@endsection