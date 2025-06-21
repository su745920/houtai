@extends('admin._layoutNew')
@section('page_head')
@stop

@section('page-content')
     <div class="layui-fluid">
        <div class="layui-card">
            <div style="height: auto;" class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">ID</label>
                        <div class="layui-input-block">
                            <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">代理商用户名</label>
                        <div class="layui-input-block">
                            <input type="text" name="username" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">是否锁定</label>
                        <div class="layui-input-block">
                            <select name="is_lock">
                                <option value="2">不限</option>
                                <option value="1">锁定</option>
                                <option value="0">未锁定</option>
                            </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">是否拉新</label>
                        <div class="layui-input-block">
                            <select name="is_addson">
                                <option value="2">不限</option>
                                <option value="1">允许拉新</option>
                                <option value="0">禁止拉新</option>
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
                 @if(strpos($authorityList,"8111")>0)
                <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_agent" style="border-radius: 4px;background:#2A64FB;">添加代理</button>
                @endif
            
                <table class="layui-hide" id="agentUsers" lay-filter="agentList"></table>
            
                <script type="text/html" id="lockTpl">
                    @{{# if (d.is_lock == 1) { }}
                        <span style="color:#2A64FB;">已锁定</span>
                    @{{# } else { }}
                        <span style="color:#999999;">未锁定</span>
                    @{{# } }}
                </script>
                <script type="text/html" id="addsonTpl">
                    @{{# if (d.is_addson == 1) { }}
                        <span style="color:#2A64FB;">允许拉新</span>
                    @{{# } else { }}
                        <span style="color:#999999;">禁止拉新</span>
                    @{{# } }}
                </script>
                
            
                <script type="text/html" id="barDemo">
                    @if(strpos($authorityList,"8112")>0)
                    <a class="layui-btn layui-btn-xs" lay-event="edit"  style="background:#2A64FB;">修改</a>
                    @endif
                    
                     @{{# if (d.google_secret) { }}
                       <a class="layui-btn layui-btn-xs" lay-event="unbind_google" style="background:#e67e22;">解绑google谷歌验证码</a>
                    @{{# } else { }}
                       <a class="layui-btn layui-btn-xs" lay-event="google" style="background:#2A64FB;">绑定google谷歌验证码</a>
                    @{{# } }}
                    
                    @if(strpos($authorityList,"8113")>0)
                    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del"  style="background: #F56C6C;">删除</a>
                    @endif
                </script>
             </div>
        </div>
    </div>
    
@stop
@section('scripts')
    <script type="text/javascript">
        window.onload = function () {
            layui.use(['layer', 'table'], function () { //独立版的layer无需执行这一句
                var $ = layui.jquery;
                var layer = layui.layer; //独立版的layer无需执行这一句
                var table = layui.table;
                var form = layui.form;
                $('#add_agent').click(function(){layer_show('添加代理', '/admin/agent/add');});

               var data_table = table.render({
                    elem: '#agentUsers',
                    url: '/admin/agent/users',
                    page: false,
                    cols: [[
                        {field: 'id', title: 'ID', minWidth: 100, sort: true},
                        {field: 'username', title: '用户名', minWidth: 150},
                        {field: 'parent_agent_name', title: '上级用户名', minWidth: 150},
                        {field: 'reg_time', title: '加入时间', minWidth: 150,sort: true},
                        {field: 'is_lock', title: '是否锁定', minWidth: 150,templet: '#lockTpl'},
                        {field: 'is_addson', title: '是否拉新', minWidth: 150,templet: '#addsonTpl'},
                        {field: 'pro_loss', title: '头寸比例(%)', minWidth: 150},
                        {field: 'pro_ser', title: '手续费比例(%)', minWidth: 150},
                        {field: 'lock_time', title: '锁定时间', minWidth: 150,sort: true},
                        {fixed: 'right', title: '操作', minWidth: 300, align: 'center', toolbar: '#barDemo'}
                    ]]
                });
                
                 //监听搜索
                form.on('submit(LAY-user-front-search)', function(data){
                    var field = data.field;
                    //执行重载
                    table.reload('agentUsers', {
                        where: field
                        ,page: {
                            curr: 1 //重新从第 1 页开始
                        }
                        ,done: function(res){ //这里要说明一下：done 是只有 response 的 code 正常才会执行。而 succese 则是只要 http 为 200 就会执行
    
                            if (res.code === 1){
                                layer.msg(res.msg ,{icon : 5});
                            }
                        }
                    });
                });
                // 添加
                // $('#add_agent').click(function(){
                //     layer.prompt({title: '请输入下级代理商帐号', formType: 0, btn :['查询该用户' , '取消']}, function(value, index){
                //         layer.close(index);
                //         if (value.length == 0) {
                //             layer.msg('用户名不能位空', {icon: 5 });
                //         }else{
                //              $.ajax({
                //                 url:'/admin/agent/searchuser',
                //                 type:'post',
                //                 dataType:'json',
                //                 data : {username : value},
                //                 success:function(res){
                //                     if(res.type=='ok'){
                //                         let {max_pro_loss,max_pro_ser,son_level,user_id,username} = res.message
                //                       layer_show('添加代理商', `/admin/agent/salesmen/add?max_pro_loss=${max_pro_loss}&max_pro_ser=${max_pro_ser}&son_level=${son_level}&user_id=${user_id}&username=${username}`);
                //                     }else{
                //                         layer.alert(res.message);
                //                     }
                //                 }
                //             });
                //         }
                //     });
                
                // });
                //监听工具条
                table.on('tool(agentList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data; //获得当前行数据
                    var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                    var tr = obj.tr; //获得当前行 tr 的DOM对象
                    
                    if(layEvent === 'del'){ //删除
                        layer.confirm('真的要删除吗？', function(index){
                            //向服务端发送删除指令
                            $.ajax({
                                url:'/admin/agent/delete',
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
                            layer_show('修改代理', '/admin/agent/salesmen/edit?id=' + data.id);
                    }else if(layEvent === 'google'){ //编辑
                        //do something
                        layer_show('Google', '/admin/a_google_auth/getBindQrcode?id=' + data.id);
                    }else if(layEvent === 'unbind_google') {
                        layer.confirm('确定解绑吗？', function(index){
                            //向服务端发送删除指令
                            $.ajax({
                                url:'/admin/manager/delAgentGoogle',
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