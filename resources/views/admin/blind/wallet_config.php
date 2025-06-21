@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        
          <div class="layui-form-item">
            <div class="layui-form-item">
                <label class="layui-form-label">产生币种</label>
                <div class="layui-input-block">
                    <select name="currency_id" lay-verify="required">
                        
                        @foreach ($currencies as $currency)
				        <option value="@if (isset($currency['id'])){{ $currency['id'] }}@endif">@if (isset($currency['name'])){{ $currency['name'] }}@endif</option>
						@endforeach
                    </select>
                </div>
            </div>
            <label class="layui-form-label">名称</label>
            <div class="layui-input-block">
                <div class="layui-input-block">
                        <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
                    </div>
            </div>
            <label class="layui-form-label">权重</label>
            <div class="layui-input-block">
                <div class="layui-input-block">
                        <input type="text" name="chance" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
                    </div>
            </div>
            </div>
             <div class="layui-form-item">
            <label class="layui-form-label">数量</label>
            <div class="layui-input-block">
                <div class="layui-input-block">
                        <input type="text" name="num" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
                    </div>
            </div>
            </div>
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
        layui.use('upload', function(){
            var upload = layui.upload;

            //执行实例
            var uploadInst = upload.render({
                elem: '#upload_test' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res){
                    //上传完毕回调
                    if (res.type == "ok"){
                        $("#thumbnail").val(res.message)
                        $("#img_thumbnail").show()
                        $("#img_thumbnail").attr("src",res.message)
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            });
        });

     
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/blind_config_doadd')}}'
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