@extends('admin._layoutNew')
@section('page-head')
<link rel="stylesheet" type="text/css" href="{{URL("layui/css/layui.css")}}" media="all">
<link rel="stylesheet" type="text/css" href="{{URL("admin/common/bootstrap/css/bootstrap.css")}}" media="all">
<link rel="stylesheet" type="text/css" href="{{URL("admin/common/global.css")}}" media="all">
<link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">
@endsection
@section('page-content')
	<form class="layui-form" method="POST">
		<input type="hidden" name="id" value="@if (isset($news['id'])){{ $news['id'] }}@endif">
		{{ csrf_field() }}
		<div class="layui-form-item">
			<label class="layui-form-label">奖品名称(中)</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="title" lay-verify="required" placeholder="请输入中文奖品名称" type="text"
					   value="@if (isset($news['title'])){{$news['title']}}@endif">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">奖品名称(越)</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="vi_title" lay-verify="required" placeholder="请输入越南语奖品名称" type="text"
					   value="@if (isset($news['vi_title'])){{$news['vi_title']}}@endif">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">奖品名称(英)</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="en_title" lay-verify="required" placeholder="请输入英语奖品名称" type="text"
					   value="@if (isset($news['en_title'])){{$news['en_title']}}@endif">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">奖品名称(泰)</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="th_title" lay-verify="required" placeholder="请输入泰语奖品名称" type="text"
					   value="@if (isset($news['th_title'])){{$news['th_title']}}@endif">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">奖品名称(印尼)</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="id_title" lay-verify="required" placeholder="请输入印尼语奖品名称" type="text"
					   value="@if (isset($news['id_title'])){{$news['id_title']}}@endif">
			</div>
		</div>

		<div class="layui-inline">
			<label class="layui-form-label">奖品类型</label>
			<div class="layui-input-inline">
				<select name="prize_type" class=""  lay-verify="required">
					<option value="0" @if(isset($news) && $news['prize_type'] == 0) selected @endif>数字币</option>
					<option value="1" @if(isset($news) && $news['prize_type'] == 1) selected @endif>实物</option>
					<option value="1" @if(isset($news) && $news['prize_type'] == 2) selected @endif>未中奖</option>
					<option value="1" @if(isset($news) && $news['prize_type'] == 3) selected @endif>再来一次</option>
				</select>
			</div>
		</div>

		<div class="layui-form-item" style="margin-top: 10px;">
			<div class="layui-inline">
				<label class="layui-form-label">奖品数值</label>
				<div class="layui-input-inline">
					<input class="layui-input" lay-verify="required" name="price" type="text" value="{{$news->price ?? 0 }}">
				</div>
				<div class="layui-form-mid layui-word-aux">USDT</div>
			</div>
		</div>


		<div class="layui-form-item">
			<div class="layui-inline">
				<label class="layui-form-label">中奖概率</label>
				<div class="layui-input-inline">
					<input class="layui-input" lay-verify="required" name="winning_probability" type="text" value="{{$news->winning_probability ?? ''}}">
				</div>
				<div class="layui-form-mid layui-word-aux">%</div>
			</div>
		</div>


		
		
		


		<div class="layui-form-item layui-form-text">
			<label class="layui-form-label">奖品图片</label>
			<div class="layui-input-block">
				<button class="layui-btn" type="button" id="img_cover_btn">选择图片</button>
				<br>
				<img src="{{$news->cover ?? ''}}" id="img_cover" class="cover" style="display: @if(!empty($news->cover)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
				<input type="hidden" name="cover" id="cover" value="{{$news->cover ?? ''}}">
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
<script type="text/javascript" src="{{URL("/admin/js/newsFormSubmit.js?v=").time()}}"></script>
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