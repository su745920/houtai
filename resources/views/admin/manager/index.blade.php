@extends('admin._layoutNew')
@section('page_head')

@stop

@section('page-content')

   @if(strpos($authorityList,"8111")>0)
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_admin" style="border-radius: 4px;background:#2A64FB;">添加管理员</button>
    @endif

    <table class="layui-hide" id="adminUsers" lay-filter="adminList"></table>

    <script type="text/html" id="google_secret">
        @{{# if (d.google_secret) { }}
            <span style="color:#2A64FB;">已绑定</span>
        @{{# } else { }}
            <span style="color:#999999;">未绑定</span>
        @{{# } }}
    </script>

    <script type="text/html" id="barDemo">
        @if(strpos($authorityList,"8112")>0)
        <a class="layui-btn layui-btn-xs" lay-event="edit"  style="background:#2A64FB;">修改</a>
        @endif
        
        @if(strpos($authorityList,"8112")>0)
         @{{# if (d.google_secret) { }}
           <a class="layui-btn layui-btn-xs" lay-event="unbind_google" style="background:#e67e22;">解绑google谷歌验证码</a>
        @{{# } else { }}
           <a class="layui-btn layui-btn-xs" lay-event="google" style="background:#2A64FB;">绑定google谷歌验证码</a>
        @{{# } }}
            
        @endif

        @if(strpos($authorityList,"8113")>0)
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"  style="background: #F56C6C;">删除</a>
        @endif
    </script>
    
@stop
@section('scripts')
    <script type="text/javascript">
        window.onload = function () {
            layui.use(['layer', 'table'], function () { //独立版的layer无需执行这一句
                var $ = layui.jquery;
                var layer = layui.layer; //独立版的layer无需执行这一句
                var table = layui.table;
                var form = layui.form;
                $('#add_admin').click(function(){layer_show('添加管理员', '/admin/manager/add');});
               var data_table = table.render({
                    elem: '#adminUsers',
                    url: '/admin/manager/users',
                    page: false,
                    cols: [[
                        {field: 'id', title: 'ID', minWidth: 100, sort: true},
                        {field: 'username', title: '用户名', minWidth: 150},
                        {field: 'role_name', title: '角色', minWidth: 150},
                        {field: 'google_secret', title: '是否绑定google', minWidth: 150,templet: '#google_secret',},
                        {fixed: 'right', title: '操作', minWidth: 300, align: 'center', toolbar: '#barDemo'}
                    ]]
                });



                //监听工具条
                table.on('tool(adminList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data; //获得当前行数据
                    var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                    var tr = obj.tr; //获得当前行 tr 的DOM对象

                    if(layEvent === 'del'){ //删除
                        layer.confirm('真的要删除吗？', function(index){
                            //向服务端发送删除指令
                            $.ajax({
                                url:'/admin/manager/delete',
                                type:'post',
                                dataType:'json',
                                data:{id:data.id},
                                success:function(res){
                                    if(res.type=='ok'){
                                        obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                        layer.msg(res.message);
                                        layer.close(index);
                                    }else{
                                        layer.close(index);
                                        layer.alert(res.message);
                                    }
                                }
                            });
                        });
                    } else if(layEvent === 'edit'){ //编辑
                        //do something
                            layer_show('修改管理员', '/admin/manager/add?id=' + data.id);

                    }else if(layEvent === 'google'){ //编辑
                        //do something
                        layer_show('Google', '/admin/google_auth/getBindQrcode?id=' + data.id);

                    }else if(layEvent === 'unbind_google') {
                        layer.confirm('确定解绑吗？', function(index){
                            //向服务端发送删除指令
                            $.ajax({
                                url:'/admin/manager/delGoogle',
                                type:'post',
                                dataType:'json',
                                data:{id:data.id},
                                success:function(res){
                                    if(res.type=='ok'){
                                        layer.msg(res.message);
                                        layer.close(index);
                                        data_table.reload();
                                    }else{
                                        layer.close(index);
                                        layer.alert(res.message);
                                    }
                                }
                            });
                        });
                    }
                });
            });
        }
    </script>

@stop