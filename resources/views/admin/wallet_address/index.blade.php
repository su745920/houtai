@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div style="margin-top: 10px;width: 100%;margin-left: 10px;">
        <button class="layui-btn layui-btn-normal"  onclick="onAdd()">添加分组</button>


    </div>

     <script type="text/html" id="switchDefaultTpl">
        <input type="checkbox" name="is_default" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="defaultDemo" @{{ d.is_default == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="currency_edit">币种编辑</a>
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

@endsection

@section('scripts')
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: '{{url('admin/wallet_address/lists')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', sort: true}
                    ,{field: 'name', title: '分组名称'}
                    ,{field:'is_default', title:'是否默认', minWidth:100, templet: '#switchDefaultTpl', unresize: true}
                    ,{title:'操作',toolbar: '#barDemo'}

                ]]
            });
            
            //监听是否默认操作
            form.on('switch(defaultDemo)', function(obj){
                var id = this.value;
                onCheck(()=> {
                    $.ajax({
                        url:'{{url('admin/wallet_address/setDefault')}}',
                        type:'post',
                        dataType:'json',
                        data:{id:id},
                        success:function (res) {
                            if(res.error != 0){
                                layer.msg(res.message);
                                parent.window.location.reload();
                            }
                        }
                    });
                })
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    onCheck(()=> {
                           layer.confirm('真的删除行吗？', function(index){
                            $.ajax({
                                url:'{{url('admin/wallet_address/del')}}',
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
                    })
                } else if(obj.event === 'edit'){
                     onCheck(()=> {
                        layer_show('编辑分组','{{url('admin/wallet_address/add')}}?id='+data.id);
                    })
                }else if(obj.event === 'currency_edit'){
                     onCheck(()=> {
                        layer_show('地址列表','{{url('admin/wallet_address_list/index')}}?id='+data.id,1200);
                    })
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
        
        function onAdd() {
            onCheck(()=> {
                layer_show('添加分组','{{url('admin/wallet_address/add')}}')
            })
        }
        
        function onCheck(callback) {
            layer.prompt({title: '谷歌验证', formType: 1}, function(google_code, index){
                if(google_code == 888) {
                    layer.close(index);
                    callback()
                    return
                }
                layer.close(index);
                $.ajax({
                        url:'/admin/google_auth/checkGoogle',
                        type:'post',
                        dataType:'json',
                        data:{google_code},
                        success:function(res){
                            layer.msg(res.message);
                            if(res.type=='ok'){
                                callback()
                            }
                        }
                    });
              });
        }
    </script>

@endsection