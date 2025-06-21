@extends('admin._layoutNew')
@section('page-head')
    <link rel="stylesheet" type="text/css" href="{{URL("layui/css/layui.css")}}" media="all">
    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/bootstrap/css/bootstrap.css")}}" media="all">
    <link rel="stylesheet" type="text/css" href="{{URL("admin/common/global.css")}}" media="all">
    <link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">
@endsection
@section('page-content')
    <div class="layui-tab">
        <ul class="layui-tab-title">
            <li><a href="/admin/prize_index">奖品设置</a></li>
            <li  class="layui-this">大转盘设置</li>
            <li><a href="/admin/dzp_config_count">抽奖次数配置</a></li>

        </ul>
    </div>

    <form class="layui-form" method="POST">
        <input type="hidden" name="id" value="@if (isset($news['id'])){{ $news['id'] }}@endif">
        {{ csrf_field() }}
        <div class="layui-form-item">
            <label class="layui-form-label">参与规则(中)</label>
            <div class="layui-input-block">
                <script id="news_content" name="content_zh" type="text/plain" style="width:100%; height:200px;">@if (isset($news['content_zh'])){!! $news['content_zh'] !!}@endif</script>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">参与规则(英)</label>
            <div class="layui-input-block">
                <script id="news_content1" name="content_en" type="text/plain" style="width:100%; height:200px;">@if (isset($news['content_en'])){!! $news['content_en'] !!}@endif</script>
            </div>
        </div>








        <div class="layui-form-item">
            <label class="layui-form-label">参与规则(越南语)</label>
            <div class="layui-input-block">
                <script id="news_content2" name="content_vi" type="text/plain" style="width:100%; height:200px;">@if (isset($news['content_vi'])){!! $news['content_vi'] !!}@endif</script>
            </div>
        </div>





        <div class="layui-form-item">
            <label class="layui-form-label">参与规则(泰语)</label>
            <div class="layui-input-block">
                <script id="news_content3" name="content_th" type="text/plain" style="width:100%; height:200px;">@if (isset($news['content_th'])){!! $news['content_th'] !!}@endif</script>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">参与规则(印尼)</label>
            <div class="layui-input-block">
                <script id="news_content4" name="content_id" type="text/plain" style="width:100%; height:200px;">@if (isset($news['content_id'])){!! $news['content_id'] !!}@endif</script>
            </div>
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
    <script type="text/javascript" src="{{URL("/admin/js/dzpFormSubmit.js?v=").time()}}"></script>
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

            //执行实例
            var uploadInst1 = upload.render({
                elem: '#img_cover_btn' //绑定元素
                ,url: '{{URL("api/upload")}}' //上传接口
                ,done: function(res) {
                    console.log(res);
                    //上传完毕回调
                    if (res.type == "ok"){
                        $("#cover").val(res.message)
                        $("#img_cover").show()
                        $("#img_cover").attr("src",res.message)
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
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