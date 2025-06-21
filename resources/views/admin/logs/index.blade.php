@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
   <div class="layui-inline layui-form">
        <!-- <label class="layui-form-label">管理员账号</label> -->
        <div class="layui-input-inline" >
            <input type="datetime" name="admin" placeholder="请输入管理员名称或ID" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline" >
            <input type="text" name="uri" placeholder="请输入请求uri" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline" style="margin-left: 10px;">
            <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-input-inline" style="margin-left: 10px;">
            <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off" class="layui-input" value="">
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <label>请求方法&nbsp;&nbsp;</label>
            <div class="layui-input-inline" style="width: 90px;">
                <select name="method" id="method" class="layui-input">
                    <option value=""></option>
                    <option value="get">GET</option>
                    <option value="post">POST</option>
                </select>
            </div>
        </div>
        <button class="layui-btn btn-search" id="admin_search" lay-submit lay-filter="admin_search"> <i class="layui-icon">&#xe615;</i> </button>
    </div>
    <table id="logs" lay-filter="logs"></table>

@endsection

@section('scripts')
<script>
    window.onload = function() {
        document.onkeydown = function(event) {
            var e = event || window.event || arguments.callee.caller.arguments[0];
            if(e && e.keyCode == 13) { // enter 键
                $('#admin_search').click();
            }
        };
        layui.use(['element', 'form', 'layer', 'table','laydate'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var table = layui.table;
            var $ = layui.$;
            var form = layui.form;
            var laydate = layui.laydate;
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });
            var data_table = table.render({
                elem: '#logs'
                ,url: '/admin/logs/list'
                ,page: true
                ,limit: 20
                ,toolbar: true
                ,height: 'full-80'
                ,cols: [[
                    {field: 'id', title: 'ID', width: 100}
                    ,{field: 'admin_id', title: '管理员ID',width: 100}
                    ,{field: 'admin_name', title: '管理员名称',width: 100}
                    ,{field: 'uri', title:'URI', width:280}
                    ,{field: 'method', title:'方法', width:60}
                    ,{field: 'params', title:'参数', width:280}
                    ,{field: 'ip', title: 'IP', width: 150}
                    ,{field: 'created_at', title: '时间', width:170}
                ]]
            });

            form.on('submit(admin_search)', function (obj) {
                console.log(obj)
                data_table.reload({
                    where: obj.field
                    ,page: {curr: 1}
                })
            });
            //监听工具条
            table.on('tool(logs)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;

                // if (layEvent === 'viewDetail') { //编辑
                //     var index = layer.open({
                //         title: '查看详情'
                //         , type: 2
                //         , content: "{{url('admin/account/viewDetail')}}?id=" + data.id
                //         , maxmin: true
                //     });
                //     layer.full(index);
                // }
            });
        });
    }
</script>    
@endsection