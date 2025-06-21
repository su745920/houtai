@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <style>
        .layui-form-label{width:120px;}
        .layui-input-block {
            margin-left: 150px;
        }
    </style>
    <form class="layui-form" action="">
        
       <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">中文标题</label>
                <div class="layui-input-inline">
                    <input class="layui-input "  name="title" type="text"  id="title"  value="{{$result->title}}">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">英文标题</label>
                <div class="layui-input-inline">
                    <input class="layui-input "  name="entitle" type="text"  id="entitle"  value="{{$result->entitle}}">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">繁体中文标题</label>
                <div class="layui-input-inline">
                    <input class="layui-input "  name="hktitle" type="text"  id="hktitle"   value="{{$result->hktitle}}">
                </div>
            </div>
        </div>
        
        
        <div class="layui-form-item">
            <label class="layui-form-label">锁定币种</label>
            <div class="layui-input-block">
                <select name="from_name" lay-verify="required">
                    
                    @foreach ($currencies as $currency)
                    <option value="@if (isset($currency['id'])){{ $currency['id'] }}@endif" @if(isset($result) && $result['from_name'] == $currency['name']) selected @endif>@if (isset($currency['name'])){{ $currency['name'] }}@endif</option>
                    @endforeach
                </select>
            </div>
        </div>  
        <div class="layui-form-item">
            <label class="layui-form-label">产出币种</label>
            <div class="layui-input-block">
                <select name="to_name" lay-verify="required">
                    
                    @foreach ($currencies as $currency)
                    <option value="@if (isset($currency['id'])){{ $currency['id'] }}@endif" @if(isset($result) && $result['to_name'] == $currency['name']) selected @endif>@if (isset($currency['name'])){{ $currency['name'] }}@endif</option>
                    @endforeach
                </select>
            </div>
        </div>  
        <div class="layui-form-item">
            <label class="layui-form-label">天数</label>
            <div class="layui-input-block">
                <input type="text" name="day" autocomplete="off" placeholder="" class="layui-input" value="{{$result->day}}" lay-verify="required">
            </div>
        </div> 
        <div class="layui-form-item">
            <label class="layui-form-label">最小收益率(%)</label>
            <div class="layui-input-block">
                <input type="text" name="rate" autocomplete="off" placeholder="" class="layui-input" value="{{$result->rate}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大收益率(%)</label>
            <div class="layui-input-block">
                <input type="text" name="rate_max" autocomplete="off" placeholder="" class="layui-input" value="{{$result->rate_max}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最小锁仓数量</label>
            <div class="layui-input-block">
                <input type="text" name="min_money" autocomplete="off" placeholder="" class="layui-input" value="{{$result->min_money}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
        <label class="layui-form-label">最大锁仓数量</label>
            <div class="layui-input-block">
                <input type="text" name="max_money" autocomplete="off" placeholder="" class="layui-input" value="{{$result->max_money}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
        <label class="layui-form-label">提前赎回手续费率</label>
            <div class="layui-input-block">
                <input type="text" name="adance_redeem_falsify" autocomplete="off" placeholder="" class="layui-input" value="{{$result->adance_redeem_falsify}}" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">提前赎回信用分</label>
            <div class="layui-input-block">
                <input type="text" name="adance_redeem_credit" autocomplete="off" placeholder="" class="layui-input" value="{{$result->adance_redeem_credit}}" lay-verify="required">
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
                layer.confirm('确定编辑锁仓挖矿吗？', function (index) {                  
                    $.ajax({
                        url:'{{url("admin/lock_config_doedit")}}'
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
                });
                return false;
            });
        });
    </script>

@endsection