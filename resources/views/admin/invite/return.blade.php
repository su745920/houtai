@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div class="layui-inline btn-group layui-btn-group">
           <button class="layui-btn layui-btn-primary cateManage" id="share">邀请分享设置</button>
           <button class="layui-btn layui-btn-primary" id="childs">会员推荐关系图</button>
    </div>
    
    
     <div class="layui-input-inline" style="margin-left:-30px;">
       <label class="layui-form-label">上级用户ID</label>
        <div class="layui-input-inline">
            <input type="text" name="id" placeholder="上级用户ID" autocomplete="off" class="layui-input" value="">
            
        </div>
        </div>
    
   <div class="layui-inline" style="dispaly:flex;margin-left:-10px;">
       <label class="layui-form-label">用户名</label>
        <div class="layui-input-inline">
            <input type="text" name="account" placeholder="上级用户手机号或邮箱" autocomplete="off" class="layui-input" value="">
            <input type="text" name="invitecode" placeholder="请输入上级邀请码" autocomplete="off" class="layui-input" value="">
        </div>
        
        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
        <div class="layui-input-inline" style="margin-left:20px;">
            <input type="text" name="parent_account" placeholder="请输入上级用户手机号或邮箱" autocomplete="off" class="layui-input" value="" style="width:220px;">
        </div>
        <div class="layui-input-inline" style="margin: 0 10px;">
            全部下级
            <input type="checkbox" class="authority" name="check_all" title="全部下级" lay-skin="primary" lay-filter="authority"/>
        </div>
        <div class="layui-inline">
            <button class="layui-btn" id="change_parent" lay-submit lay-filter="change_parent">变更上级</button>
        </div>
    </div>
    <div class="layui-form">
        <table id="accountlist" lay-filter="accountlist"></table>
        <script type="text/html" id="barDemo">
            <!-- <a class="layui-btn layui-btn-xs" lay-event="viewDetail">删除</a> -->
        </script>

@endsection

        @section('scripts')
            <script>

                window.onload = function() {
                    document.onkeydown=function(event){
                        var e = event || window.event || arguments.callee.caller.arguments[0];
                        if(e && e.keyCode==13){ // enter 键
                            $('#mobile_search').click();
                        }
                    };
                    layui.use(['element', 'form', 'layer', 'table'], function () {
                        var element = layui.element;
                        var layer = layui.layer;
                        var table = layui.table;
                        var $ = layui.$;
                        var form = layui.form;

                        $('#share').click(function(){layer_show('邀请分享设置', '/admin/invite/share');});
                        $('#childs').click(function(){layer_show('会员关系图', '/admin/invite/childs');});
                        $('#bg').click(function(){layer_show('邀请背景图管理', '/admin/invite/bgpic');});

                        form.on('submit(mobile_search)',function(obj){
                            var account =  $("input[name='account']").val();
                            var code =  $("input[name='invitecode']").val();
                            var id = $("input[name='id']").val();
                            tbRend("{{url('/admin/invite/return_list')}}?account="+account+"&code="+code+"&id="+id);
                            return false;
                        });
                        form.on('submit(change_parent)',function(obj){
                            var account =  $("input[name=account]").val();
                            var parent_account =  $("input[name=parent_account]").val();
                            if(parent_account === '' || parent_account.trim().length ===0){
                                layer.alert('请输入变更的上级用户手机号或邮箱');
                                return false;
                            }
                            var check_all =  $("input[name=check_all]:checked").val();
                            var check_list = layui.table.checkStatus('accountlist').data;
                            if(check_list.length == 0 && check_all != "on"){
                                layer.alert('请选择需要变更上级的用户或者勾选全部下级');
                                return false;
                            }
                            if((account === '' || account.trim().length ===0) && check_all == "on"){
                                layer.alert('请先输入查找的用户手机号或邮箱');
                                return false;
                            }      
                            layer.confirm('真的要变更选择用户的上级用户么？', function (index) {
                                var check_id_list = [];
                                for(var i in check_list){
                                    check_id_list.push(check_list[i]['id']);
                                }
                                //向服务端发送删除指令
                                $.ajax({
                                    url: "{{url('admin/invite/change_parant')}}",
                                    type: 'post',
                                    dataType: 'json',
                                    data: {account: account,parent_account: parent_account,check_all: check_all,check_id_list: check_id_list},
                                    success: function (res) {
                                        if (res.type == 'ok') {
                                            layer.close(index);
                                            layer.alert(res.message);
                                            tbRend("{{url('/admin/invite/return_list')}}");
                                        } else {
                                            layer.close(index);
                                            layer.alert(res.message);
                                        }
                                    }
                                });
                            });
                        });
                        function tbRend(url) {
                            table.render({
                                elem: '#accountlist'
                                , url: url
                                , page: true
                                ,limit: 20
                                , cols: [[
                                    {field: '', type: 'checkbox'}
                                    ,{field: 'id', title: 'ID', width: 60}
                                    ,{field:'account_number', title:'交易账号', width: 200}
                                    ,{field:'country_code', title:'国际区号', width: 90}
                                    ,{field:'phone', title:'手机号', width: 120}
                                    ,{field:'email', title:'邮箱', width: 150, hide: true}
                                    ,{field:'nationality', title:'国籍', width: 130, hide: true}
                                    ,{field:'card_id', title: '身份证号',width:  180, hide: true}
                                    ,{field: 'parent_name', title: '邀请人', width:  130}
                                    ,{field:'extension_code', title:'邀请码', width:80}
                                    ,{field: 'risk_name', title: '风控类型', width: 90}
                                    ,{field: 'cz_count', title: '充值次数', width:  90}
                                    // ,{field:'top_upnumber', title:'团队充值业绩', width: 130}
                                    // ,{field:'zhitui_real_number', title:'直推实名人数', width: 130}
                                    // ,{field:'real_teamnumber', title:'团队实名人数', width: 130}
                                    // ,{field:'status', title:'工作室', width: 90, templet: '#isAtelier'}
                                    // ,{field:'type', title: '积分兑换', width:  100, templet: '#allowExchange'}
                                    ,{field: 'credit_score', title: '信用分', width:  80}
                                    ,{field:'status', title:'状态', width: 120, templet: '#switchTpl'}
                                    ,{field: 'ip', title: '来源', width:  130}
                                    ,{field: 'remark', title: '备注', width: 90}
                                    ,{field:'time', title:'注册时间', width: 170} 
                                    // ,{fixed: 'right', title: '操作', width: 220, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                            tbRend("{{url('/admin/invite/return_list')}}");
                        
                        //监听工具条
                        table.on('tool(accountlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;

                            if (layEvent === 'delete') { //删除
                                layer.confirm('真的要删除吗？', function (index) {
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url: "{{url('admin/invite/del')}}",
                                        type: 'post',
                                        dataType: 'json',
                                        data: {id: data.id},
                                        success: function (res) {
                                            if (res.type == 'ok') {
                                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                                layer.close(index);
                                            } else {
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

@endsection