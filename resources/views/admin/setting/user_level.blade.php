@extends('admin._layoutNew')
@section('page-head')
    <link rel="stylesheet" type="text/css" href="{{URL("layui/css/layui.css")}}" media="all">
    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/bootstrap/css/bootstrap.css")}}" media="all">
    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/global.css")}}" media="all">
    <link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">
@endsection
@section('page-content')
{{--    <div class="layui-tab">--}}
{{--        <ul class="layui-tab-title">--}}
{{--            <li>汇率设置</li>--}}
{{--            <li>平台设置</li>--}}
{{--            <li style="display:none">上传设置</li>--}}
{{--            <li>交易设置</li>--}}
{{--            <li>代理商设置</li>--}}
{{--            <li>注册设置</li>--}}
{{--            <li  class="layui-this"><a href="/admin/user_level">用户等级设置</a></li>--}}

{{--        </ul>--}}
{{--    </div>--}}
    <form class="layui-form" method="POST">
        <input type="hidden" name="id" value="@if (isset($news['id'])){{ $news['id'] }}@endif">
        {{ csrf_field() }}
        <div class="layui-input-inline">
        <div class="layui-inline">
            <label class="layui-form-label" style="width: 300px;font-weight: 700;">请输入每一级别需要充值的最低金额
            </label>
        </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">VIP0</label>
            <div class="layui-input-inline">
                <input type="text" name="lv0" style="width:190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv0'])){{$po['lv0']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP1</label>
            <div class="layui-input-inline">
                <input type="text" name="lv1" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv1'])){{$po['lv1']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">VIP2</label>
            <div class="layui-input-inline">
                <input type="text" name="lv2" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv2'])){{$po['lv2']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP3</label>
            <div class="layui-input-inline">
                <input type="text" name="lv3" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv3'])){{$po['lv3']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP4</label>
            <div class="layui-input-inline">
                <input type="text" name="lv4" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv4'])){{$po['lv4']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP5</label>
            <div class="layui-input-inline">
                <input type="text" name="lv5" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv5'])){{$po['lv5']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP6</label>
            <div class="layui-input-inline">
                <input type="text" name="lv6" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv6'])){{$po['lv6']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP7</label>
            <div class="layui-input-inline">
                <input type="text" name="lv7" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv7'])){{$po['lv7']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">VIP8</label>
            <div class="layui-input-inline">
                <input type="text" name="lv8" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv8'])){{$po['lv8']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">VIP9</label>
            <div class="layui-input-inline">
                <input type="text" name="lv9" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv9'])){{$po['lv9']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">VIP10</label>
            <div class="layui-input-inline">
                <input type="text" name="lv10" style="width: 190px" autocomplete="off" class="layui-input"
                       value="@if(isset($po['lv10'])){{$po['lv10']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">USDT</div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/ueditor.config.js') }}"></script>
    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/ueditor.all.js') }}"> </script>
    <script type="text/javascript" src="{{ URL('vendor/ueditor/1.4.3/lang/zh-cn/zh-cn.js') }}"></script>

    <script>
        layui.use(['element', 'form', 'layer', 'jquery', 'layedit', 'laydate'], function() {
            var element = layui.element
                , $ = layui.$
                , form = layui.form
                , layer = layui.layer
                , laydate = layui.laydate
                , layedit = layui.layedit;

            //表单提交事件
            form.on('submit(submit)', function(dataObj){
                var serData = $(dataObj.form).serialize();
                $.ajax({
                    type: 'POST'
                    ,url: window.location.href
                    ,data: serData
                    ,success: function(data) {
                        if(data.type == 'ok') {
                            layer.msg(data.message, {
                                icon: 1,
                                time: 1000,
                                end: function() {
                                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                    parent.layer.close(index);
                                    parent.window.location.reload();
                                }
                            });
                        } else {
                            layer.msg(data.message, {icon:2});
                        }
                    }
                    ,error: function(data) {
                        console.log(data);
                        //重新遍历获取JSON的KEY
                        var str = '服务器验证失败,错误信息:' + '<br>';
                        for(var o in data.responseJSON.errors) {
                            str += data.responseJSON.errors[o] + '<br>';
                        }
                        layer.msg(str, {icon:2});
                    }
                });
                parent.layui.layer.close();
                return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
            });

        });
    </script>
    <style>
        .layui-textarea{
            /*height: 5px!important;*/
            min-height: 50px!important;
        }
    </style>
@endsection