@extends('admin._layoutNew')

@section('page-head')
<style>
    .hide {
        display: none;
    }
    .icon-tips-help {
        font-weight: bolder;
        border: 1px solid #b7b7b7;
        font-size: 12px;
        border-radius: 50%;
        padding: 1px;
        color: #f9c83f;
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-tab-content">
            <div class="layui-form-item">
                <label class="layui-form-label">ID：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$id}}</label>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$account_number}}</label>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">交易对：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$symbol}}</label>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">风险时间：</label>
                <div class="layui-input-inline" style="width:500px">
                    <input id="caution_time" type="text" class="layui-input layui-date" name="caution_time" placeholder="风险时间" value="{{$caution_time}}">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">风险价格：</label>
                <div class="layui-input-inline" style="width:500px">
                    <input type="number" step=0.01 name="caution_price" min=0 style="padding-left:15px;" autocomplete="off" placeholder="风险价格" class="layui-input" value="{{$caution_price}}">
                </div>
            </div>
        </div>
        <input id="id" type="hidden" name="id" value="{{$id}}">
        <div class="layui-form-item">
            <div class="layui-input-block" style="margin-left:0px;text-align:center;">
                <button class="layui-btn" type="button" lay-submit="" lay-filter="*">确定</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
<script>
    layui.use([ 'form','layer','laydate'], function () {
        // layui模块
        var upload = layui.upload 
            ,form = layui.form
            ,layer = layui.layer
            ,laydate = layui.laydate
            ,$ = layui.$
        var coin_amount = Number($('#coin_amount').val())
        $('input.layui-date').each(function () {
            laydate.render({
                elem: this
                ,type: 'datetime'
            });
        });
        
        // 监听提交
        form.on('submit(*)', function(data) {
            var data = data.field;

            layer.confirm('真的要确定风险设置吗？', function (index) {
                $.ajax({
                    url: '/admin/Leverdeals/caution_confirm'
                    ,type: 'post'
                    ,dataType: 'json'
                    ,data : data
                    ,success: function(res) {
                        layer.msg(res.message, {
                            time: 2000
                            ,end: function () {
                                if (res.type == 'ok') {
                                    var parent_index = parent.layer.getFrameIndex(window.name); // 先得到当前iframe层的索引
                                    parent.layer.close(parent_index); // 再执行关闭
                                    parent.window.layui.table.reload('data_table', {});
                                }
                            }
                        })
                    }
                });
                return false;
            });
        });
    });
</script>
@endsection