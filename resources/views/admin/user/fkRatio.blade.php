@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <style>
        .bank .layui-form-label{width:100px;}
        .bank .layui-input-block{margin-left:130px;}
    </style>
    <form class="layui-form" action="">
        <div class="layui-tab">

{{--            <ul class="layui-tab-title">--}}
{{--                <li class="layui-this">编辑风控比例</li>--}}
{{--            </ul>--}}
{{--            <div class="layui-tab-content">--}}
{{--                <div class="layui-tab-item layui-show">--}}


{{--                    <div class="layui-form-item">--}}
{{--                        <label class="layui-form-label">风控比例</label>--}}
{{--                        <div class="layui-input-block">--}}
{{--                            <input type="text" name="subcontrol_ratio" autocomplete="off" placeholder="" class="layui-input" value="{{$result->subcontrol_ratio}}">--}}
{{--                        </div>--}}
{{--                    </div>--}}

{{--                </div>--}}


{{--            </div>--}}

            <ul class="layui-tab-title">
                <li class="layui-this">编辑输赢</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">


                    <select name="risk" id="risk">
                        <option value="">请选择</option>

                            <option value="-1" @if($result->risk == -1) selected @endif>亏</option>
                        <option value="0" @if($result->risk == 0) selected @endif>正常</option>
                        <option value="1" @if($result->risk == 1) selected @endif>赢</option>
                        <option value="2" @if($result->risk == 2) selected @endif>涨赢</option>
                        <option value="3" @if($result->risk == 3) selected @endif>跌赢</option>

                    </select>

                </div>


            </div>
        </div>


        <input type="hidden" name="id" value="{{$result->id}}">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        layui.use(['element', 'form', 'laydate', 'upload'], function() {
            var form = layui.form,
                $ = layui.jquery,
                laydate = layui.laydate,
                upload = layui.upload,
                element = layui.element
            var index = parent.layer.getFrameIndex(window.name)
                ,uploadInst = upload.render({
                elem: '.upload_btn' //绑定元素
                ,url: '/api/upload' //上传接口
                ,done: function(res, index, upload) {
                    //上传完毕回调
                    if (res.type == "ok") {
                        var img = $(this.item).nextAll('img.thumbnail')
                        img.next(".thumbnail_input").val(res.message);
                        img.attr("src",res.message).show();
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });
            //监听提交
            form.on('submit(demo1)', function(data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/user/updateFKRatio',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function(res) {
                        if (res.type == 'error') {
                            layer.msg(res.message);
                        } else {
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection