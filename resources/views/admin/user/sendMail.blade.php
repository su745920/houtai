@extends('admin._layoutNew')

@section('page-head')
@endsection

@section('page-content')
    <style>
        .bank .layui-form-label{width:80px;}
        .bank .layui-input-block{margin-left:90px;}
    </style>
    <form class="layui-form" action="POST">
		<div class="layui-form-item">
			<label class="layui-form-label">标题</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="title" lay-verify="required" placeholder="请输入标题" type="text" value="">
			</div>
		</div>
        <div class="layui-form-item">
			<label class="layui-form-label">内容</label>
			<div class="layui-input-block">
				<script id="mail_content" name="content" lay-verify="required" type="text/plain" style="width:100%; height:400px;"></script>
			</div>
		</div>

        <input type="hidden" name="user_id" value="{{$result->id}}">
        <div class="layui-form-item">
            <div class="layui-input-block" style="display: flex;align-items: center;justify-content: center;">
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

	//初始化日期控件和富文本编辑器
	laydate.render({
		elem: '#create_time' //指定元素
	});

	//百度富文本初始化
	var ue = UE.getEditor('mail_content');
	var current_editor;

	ueditor_image_callback = function(urlArray) {
		for(var i = 0 ;i<urlArray.length;i++)
		{
			current_editor.execCommand('insertHtml', "<img class='imgloading' data-original='"+urlArray[i]+"' alt='' src='"+urlArray[i]+"' style='display: inline;'>");
		}
		layer.closeAll();
	}

	upload_image_callback = function (url)
	{
		$(".wx_qr_code").show()
		$("#wx_qr_code").val(url[0])
		$(".wx_qr_code").attr("src",url[0])
	}

	baidu.editor.commands['customupload'] = 
	{
		execCommand: function() {
			upload_select('ueditor_image_callback',10)
			current_editor = this;
			return true;
		}, queryCommandState: function() { }
	};

	

	//表单提交事件
	form.on('submit(submit)', function(dataObj){
		var serData = $(dataObj.form).serialize();
		$.ajax({
			type: 'POST'
			,url: "{{url('admin/user/send_mail')}}"
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

@endsection