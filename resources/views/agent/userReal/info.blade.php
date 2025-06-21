@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">用户手机号或邮箱</label>
            <div class="layui-input-block">
                <input type="text" name="account" autocomplete="off" placeholder="" class="layui-input" value="{{$result->account}}" disabled>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">真实姓名</label>
            <div class="layui-input-block">
                <input type="text" name="email" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name}}" disabled>
            </div>
        </div>
       

        <div class="layui-form-item">
            <label class="layui-form-label">身份证号码</label>
            <div class="layui-input-block">
                <input type="text" name="card_id" autocomplete="off" placeholder="" class="layui-input" value="{{$result->card_id}}">
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">正面照片</label>
            <div class="layui-input-block">
               
                <img src="@if(!empty($result->front_pic)){{$result->front_pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->front_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                
            </div>
        </div>
         <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">反面照片</label>
            <div class="layui-input-block">
               
                <img src="@if(!empty($result->reverse_pic)){{$result->reverse_pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->reverse_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">手持身份证照片</label>
            <div class="layui-input-block">
               
                <img src="@if(!empty($result->hand_pic)){{$result->hand_pic}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->hand_pic)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                
            </div>
        </div>
        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">审核备注</label>
            <div class="layui-input-block">
               <textarea name="remark" id="remark" cols="90" rows="10">{{$result->remark}}</textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
            <input type="hidden" name='id' value='{{$result->id}}'>
            @if($result->review_status == 1)
            <button class="layui-btn" lay-submit="" lay-filter="demo1" name='method' value="done">审核通过</button>
            <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="demo2">审核拒绝</button>
            @endif
            </div>
        </div>
    </form>

@endsection
@section('scripts')
    <script>
       

        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('agent/user/pass_auth')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
            //监听提交
            form.on('submit(demo2)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('agent/user/refuse_auth')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
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
