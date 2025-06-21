@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
   <div class="layui-form">
        <div class="layui-item">
        <form class="layui-form col-lg-5">
           <input name="agent_id" hidden="hidden" value="{{$agent_user['id']}}">
            <div class="layui-form-item">
                <label class="layui-form-label">secret</label>
                <div class="layui-input-block">
                    <input type="text" name="secret" id="secret"  value="{{ $createSecret['secret'] }}"  readonly>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">

        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;

            form.on('submit(adminuser_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/a_google_auth/bind',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });

        });


    </script>
@stop