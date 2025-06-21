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
                <label class="layui-form-label">贷款订单号</label>
                <div class="layui-input-inline">
                    <input type="text" name="order_no"  placeholder="请输入贷款订单号" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
            
        </form>
        <button class="layui-btn layui-btn-primary" id="loanSettingAdd">添加贷款设置</button>
    </div>
    
    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <table id="loanSettingList" lay-filter="test"></table>
    <script type="text/html" id="operate">
       <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit">编辑</a>
       <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>

@endsection

@section('scripts')
    <script>
        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            
            $('#loanSettingAdd').click(function() {
            var index = layer.open({
                    title:'添加贷款设置'
                    ,type:2
                    ,content: '/admin/loan_setting_add'
                    ,area: ['529px', '382px']
                });
            });
            
            //第一个实例
            var data_table = table.render({
                elem: '#loanSettingList'
                ,url: '{{url('admin/loan_setting_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,toolbar: true
                ,height: 'full-60'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width: 150, sort: true}
                    ,{field: 'loan_institution', title: '放款机构', width: 120}
                    ,{field: 'loan_days', title: '贷款天数', width: 180}
                    ,{field: 'loan_rate', title: '贷款利率%', width: 150}
                    ,{field: 'sorts', title: '显示顺序', width: 150}
                    ,{field:'status', title:'是否显示', width: 120, templet: '#switchTpl'}
                    ,{field: 'created_at', title: '创建时间', width: 170}
                    ,{field: 'updated_at', title: '更新时间', width: 170}
                    ,{fixed: 'right',title: '操作', width: 150, align: 'center', templet: '#operate'}
                ]]
            });

            table.on('tool(test)', function(obj) {
                var data = obj.data;
                if(obj.event === 'edit') {
                    layer.open({
                        title: '编辑贷款设置'
                        ,type: 2
                        ,content: '/admin/loan_setting_edit/' + data.id
                        ,area: ['529px', '382px']
                        ,maxmin: true
                    });
                }else if(obj.event === 'del') {
                    layer.confirm('确定要删除吗?', function(index) {
                        $.ajax({
                            url:'/admin/loan_setting_del/' + data.id,
                            type:'get',
                            dataType:'json',
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
            
            //监听锁定操作
            form.on('switch(status)', function(obj){
                var id = this.value;
                $.ajax({
                    url:"{{url('admin/loan_setting_status')}}",
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        layer.msg(res.message);
                        
                    }
                });
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