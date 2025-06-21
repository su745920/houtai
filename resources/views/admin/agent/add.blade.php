@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <header class="larry-personal-tit">
        <span>代理</span>
    </header><!-- /header -->
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">用户名</label>
                <div class="layui-input-block">
                    <input type="text" name="username" autocomplete="off" class="layui-input" value="{{ $agent_user['username'] }}" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">密码</label>
                <div class="layui-input-block">
                    <input type="password" name="password" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
             <div class="layui-form-item">
                <label class="layui-form-label">头寸比例（%）</label>
                <div class="layui-input-block">
                     <input type="text" name="pro_loss" value=""
                               lay-verify="pro_loss" placeholder="代理商的头寸比例" autocomplete="off"
                               class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">设置下级代理商的头寸比例，该值不能超过<span
                                class="layui-badge">100</span>。如20.85%，则输入20.85
                    </div>
            </div>

            <div class="layui-form-item">
                <label class="layui-form-label">手续费比例（%）</label>
                <div class="layui-input-block">
                    <input type="text" name="pro_ser" value=""
                               lay-verify="pro_ser" placeholder="手续费比例" autocomplete="off" class="layui-input">
                </div>
                <div class="layui-form-mid layui-word-aux">设置下级代理商的手续费比例，该值不能超过<span
                                class="layui-badge">100</span>。如20.85%，则输入20.85
                    </div>
            </div>
             <div class="layui-form-item" lay-filter="sex">
                <label class="layui-form-label">是否锁定</label>
                <div class="layui-input-block">
                     <input type="radio" name="is_lock" value="0" title="否" checked>
                     <input type="radio" name="is_lock" value="1" title="是" >
                </div>
                <div class="layui-form-mid layui-word-aux">当锁定时，该用户不能登录代理商管理平台</div>
            </div>
            <div class="layui-form-item" lay-filter="sex">
                <label class="layui-form-label">允许拉新</label>
                <div class="layui-input-block">
                   <input type="radio" name="is_addson" value="0" title="禁止">
                   <input type="radio" name="is_addson" value="1" title="允许" checked>
                </div>
                <div class="layui-form-mid layui-word-aux">当禁止填加新代理商时，该用户不能添加自己的下级代理商</div>
            </div>
            <input type="hidden" name="id" value="{{$agent_user['id']}}">
            <input type="hidden" name="parent_agent_id" value="{{$agent_user['parent_agent_id']}}">
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
                    url: '/admin/agent/add',
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