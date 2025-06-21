@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<style>
    .bank .layui-form-label{width:100px;}
    .bank .layui-input-block{margin-left:130px;}
     .layui-table-cell {
        height: 30px;
    }
</style>
<form class="layui-form" action="">
     <div class="layui-form-item">
        <label class="layui-form-label">币种名称</label>
        <div class="layui-input-block">
             <select name="coin" lay-filter="select-filter" lay-search lay-verify="required">
              <option value="USDT(TRC20)" @if($result->coin == 'USDT(TRC20)'){{"selected"}}@endif >USDT(TRC20)</option>
              <option value="USDT(ERC20)" @if($result->coin == 'USDT(ERC20)'){{"selected"}}@endif >USDT(ERC20)</option>
              <option value="USDC" @if($result->coin == 'USDC'){{"selected"}}@endif >USDC</option>
              <option value="ETH" @if($result->coin == 'ETH'){{"selected"}}@endif >ETH</option>
              <option value="BTC" @if($result->coin == 'BTC'){{"selected"}}@endif >BTC</option>
            </select>
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">钱包地址</label>
        <div class="layui-input-block">
            <input type="text" name="address" autocomplete="off" placeholder="" class="layui-input" value="{{$result->address}}" lay-verify="required">
        </div>
    </div>
     <div class="layui-form-item layui-form-text" style="display: none;">
        <label class="layui-form-label">钱包二维码</label>
        <div class="layui-input-block">
            <button class="layui-btn upload_btn" type="button">选择图片</button>
            <br>
            <img src="@if(!empty($result->upload_pic)){{$result->upload_pic}}@endif" class="thumbnail" style="display: @if(!empty($result->upload_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
            <input type="hidden" class="thumbnail_input" name="upload_pic" id="upload_pic" value="@if(!empty($result->upload_pic)){{$result->upload_pic}}@endif" lay-verify="required">
        </div>
    </div>
    <input type="hidden" name="id" value="{{$result->id}}">
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
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
                url: '/admin/user/edit_address',
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