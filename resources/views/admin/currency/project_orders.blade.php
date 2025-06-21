@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')

    <!--<div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <!--<div class="layui-inline">-->
                    <!--    <label class="layui-form-label">ID</label>-->
                    <!--    <div class="layui-input-block">-->
                    <!--        <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">-->
                    <!--    </div>-->
                    <!--</div>-->
                    <!--<div class="layui-inline">
                        <label class="layui-form-label">用户</label>
                        <div class="layui-input-block">
                            <input type="text" name="serach" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">类型</label>
                        <div class="layui-input-inline">
                           <select name="type"  class="layui-input">
                               <option value="">请选择</option>
                               <option value="1">认购</option>
                               <option value="2">抽签</option>
                           </select>
                        </div>
                    </div>
                     <div class="layui-inline">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-inline">
                           <select name="status"  class="layui-input">
                               <option value="">请选择</option>
                               <option value="1">已申请</option>
                               <option value="2">已扣费</option>
                               <option value="3">已完成</option>
                           </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="searchtj">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="layui-card-body">

                <table id="boss" lay-filter="boss"></table>
            </div>
        </div>
    </div>-->
    <div style="margin-top: 10px;width: 100%;margin-left: 0px;">
        <div class="layui-form-item">
        <form class="layui-form layui-form-pane layui-inline" action="">
            <div class="layui-inline" style="margin-right: 0px;">
                <label class="layui-form-label">用户账号</label>
                <div class="layui-input-inline" style="width:110px;">
                    <input type="text" name="account_number" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-right: 0px;">
                <label class="layui-form-label">创建时间</label>
                <div class="layui-input-inline" style="width:110px;">
                    <input type="text" class="layui-input" id="created_at" value="" name="created_at">
                </div>
            </div>
            <div class="layui-inline" style="margin-right: 0px;">
                <label class="layui-form-label">到期时间</label>
                <div class="layui-input-inline" style="width:110px;">
                    <input type="text" class="layui-input" id="end_at" value="" name="end_at">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="width: 80px;">类型</label>
                <div class="layui-input-inline" style="width:100px;">
                <select name="type"  class="layui-input">
                        <option value="">请选择</option>
                        <option value="1">认购</option>
                        <option value="2">抽签</option>
                    </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label" style="width: 80px;">状态</label>
                <div class="layui-input-inline" style="width:100px;">
                    <select name="status"  class="layui-input">
                        <option value="">请选择</option>
                        <option value="1">已申请</option>
                        <option value="2">已扣费</option>
                        <option value="3">已放币</option>
                        <option value="4">已解冻</option>
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
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'已申请'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge layui-bg-red">'+'已扣费'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge layui-bg-blue">'+'已放币'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge layui-bg-black">'+'已解冻'+'</span>' : '' }}
    </script>
    <table id="boss" lay-filter="boss" type="text/html">
    </table>
    @endsection
    @section('scripts')
    <script type="text/html" id="barDemo">
        @{{# if (d.status == 1 || d.status == 2) { }}
        @if(strpos($authorityList,"3302")>0)
            <a class="layui-btn layui-btn-xs layui-btn-warm" lay-event="adopt">通过</a>
            <a class="layui-btn layui-btn-xs layui-btn" lay-event="refuse">拒绝</a>
        @endif

        @{{# } }}
    </script>

    <script>
     let id='';
    let url=window.location.href;
    if(url.indexOf("=")!=-1){
        id=url.substr(url.indexOf("=")+1);
    }
        layui.use(['table','form','laydate'], function(){
            // console.log(layui.setter.base)
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate
            laydate.render({
                elem: '#created_at'
            });
            laydate.render({
                elem: '#end_at'
            });
             //第一个实例
            table.render({
                elem: '#boss'
                ,url: '/admin/currency/project/order/list?project_id='+id //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'account_number', title: '用户', width:180}
                    ,{field: 'name', title: '币种', width:120}
                    ,{field: 'coin_amount', title: '申购数量', width:120}
                    ,{field: 'passed_amount', title: '通过数量', width:120}
                    ,{field: 'price', title: '价格', width:120}
                    ,{field: 'total_price',title: '扣除余额', width:130}
                    ,{field: 'type_text', title: '类型', width:60}
                    ,{field: 'status_text', title: '状态', width:80, align: 'center', templet: '#statustml'}
                    ,{field: 'created_at', title: '创建时间',width:150}
                    ,{field: 'end_at', title: '到期时间',width:150}
                    ,{fixed: 'right', title: '操作', width: 122, align: 'center', toolbar: '#barDemo'}
                ]]
            });

            //监听提交
            form.on('submit(mobile_search)', function(data) {

                table.reload('mobileSearch',{
                    where: data.field,
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
            table.on('tool(boss)', function(obj){
                var data = obj.data;
                if(obj.event === 'adopt'){
                    var index = layer.open({
                        title:'填写配售信息'
                        ,type:2
                        ,content: '/admin/currency/project/sell/view?id='+data.id+"&name="+data.name+"&coin_amount="+data.coin_amount+"&passed_amount="+data.passed_amount+"&account_number="+data.account_number
                        ,area: ['700px', '400px']
                        ,maxmin: true
                        ,anim: 3
                    });
                }
                else if(obj.event === 'refuse'){
                    layer.confirm('真的要拒绝申购订单吗？', function (index) {
                        $.ajax({
                            url: "/admin/currency/project/sell/confirm",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id,passed_amount: 0},
                            success: function (res) {
                                layer.msg(res.message);
                                if (res.type == 'ok') {
                                    table.reload('mobileSearch',{
                                        where: data.field,
                                        page: {curr: 1}         //重新从第一页开始
                                    });
                                } else {
                                    layer.close(index);
                                }
                            }
                        });
                    });
                }
            });
        });
    </script>

@endsection
