@extends('admin._layoutNew')
@section('page-head')
<link rel="stylesheet" type="text/css" href="{{URL("admin/css/personal.css")}}" media="all">
@endsection
@section('page-content')
	<form class="layui-form" method="POST">
		{{ csrf_field() }}
        <input type="hidden" name="id" value="@if (isset($id)){{ $id }}@endif" >
		<div class="layui-form-item">
			<label class="layui-form-label">放款机构</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="loan_institution" lay-verify="required" autocomplete="off" placeholder="请输入放款机构" type="text" value="@if (isset($loan_institution)){{ $loan_institution }}@endif">
			</div>
		</div>
			<div class="layui-form-item">
			<label class="layui-form-label">贷款天数</label>
			<div class="layui-input-block">
				<input class="layui-input" placeholder="请输入贷款天数" type="number" lay-verify="required" autocomplete="off" name="loan_days" value="@if(isset($loan_days)){{ $loan_days }}@else{{0}}@endif">
			</div>
		</div>
			<div class="layui-form-item">
			<label class="layui-form-label">贷款利率%</label>
			<div class="layui-input-block">
				<input class="layui-input" placeholder="请输入贷款利率" type="number" lay-verify="required" autocomplete="off" name="loan_rate" value="@if(isset($loan_rate)){{ $loan_rate }}@else{{0}}@endif">
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">显示顺序</label>
			<div class="layui-input-block">
				<input class="layui-input" placeholder="请输入文章关键字" type="number" name="sorts" value="@if(isset($sorts)){{ $sorts }}@else{{0}}@endif">
			</div>
		</div>
		
		<div class="layui-form-item">
			<label class="layui-form-label">是否显示</label>
			<div class="layui-input-block">
                <input type="radio" name="status" value="1" title="是" @if (isset($status))  @if ($status == 1) checked @endif @else checked @endif >
                <input type="radio" name="status" value="0" title="否" @if (isset($status) && $status == 0) checked @endif>
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
<script type="text/javascript">
    layui.use(['element', 'form', 'layedit', 'laypage', 'layer'], function() {
    var element = layui.element, form = layui.form, $ = layui.$, layedit = layui.layedit, laypage = layui.laypage;
    form.on('submit(submit)', function(dataObj) {
        var serData =  $(dataObj.form).serialize();
        $.ajax({
            type : 'POST'
            ,url : window.location.href
            ,data: serData
            ,success: function(data) {
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                icon = data.type == 'ok' ? 1 : 2;
                parent.layer.msg(data.message, {icon: icon});
                icon == 1 && setTimeout(() => {
                    parent.layer.close(index);             
                    parent.window.location.reload();
                }, 800);
            }
            ,error: function(data) {
                layer.msg('错误：' + data.statusText, {icon: 2});
            }

        });
        return false;        
    });
});
</script>
@endsection