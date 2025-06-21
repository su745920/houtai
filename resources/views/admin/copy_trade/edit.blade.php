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
        <ul class="layui-tab-title">
            <li class="layui-this">交易员资料</li>
            <li>交易员订单</li>

           
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <div class="layui-form-item">
                    <label class="layui-form-label">账号</label>
                    <div class="layui-input-block">
                        <input type="text" name="account_number" autocomplete="off" placeholder="" class="layui-input" value="{{$result->account_number}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">手机号</label>
                    <div class="layui-input-block">
                        <input type="text" name="phone" autocomplete="off" placeholder="" class="layui-input" value="{{$result->phone}}" disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" name="email" autocomplete="off" placeholder="" class="layui-input" value="{{$result->email}}" disabled>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="password" autocomplete="off" placeholder="" class="layui-input" value="">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">交易密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="pay_password" autocomplete="off" placeholder="" class="layui-input" value="">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">信用分</label>
                    <div class="layui-input-block">
                        <input type="text" name="credit_score" autocomplete="off" placeholder="" class="layui-input" value="{{$result->credit_score}}">
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">风控比例</label>
                    <div class="layui-input-block">
                        <input type="text" name="subcontrol_ratio" autocomplete="off" placeholder="" class="layui-input" value="{{$result->subcontrol_ratio}}">
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <input type="text" name="remark" autocomplete="off" placeholder="" class="layui-input" value="{{$result->remark}}">
                    </div>
                </div>
            </div>
            <!--
            <div class="layui-tab-item">
                <div class="layui-form-item">
                    <label class="layui-form-label">真实姓名</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name ?? ''}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证号</label>
                    <div class="layui-input-block">
                        <input type="text" name="card_id" autocomplete="off" placeholder="" class="layui-input" value="{{$result->card_id ?? ''}}" @if(empty($result->card_id)) disabled @endif>
                    </div>
                </div>
            </div>
            -->

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
                url: '/admin/user/edit',
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