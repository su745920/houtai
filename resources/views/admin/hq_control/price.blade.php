@extends('admin._layoutNew')
@section('page-head')
@endsection
@section('page-content')
    <form class="layui-form" action="">
        {{ csrf_field() }}
        <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
             <div class="layui-form-item">
                <label for="currency_id" class="layui-form-label">币种</label>
                <div class="layui-input-block">
                     <select lay-filter="select-filter" name="currency_id" lay-verify="required" id="currency_id" lay-search>
                        @foreach ($currencies as $currency)
                        <option value="{{$currency->id}}" data-price="{{$currency->float_price}}">{{$currency->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="layui-col-md4 layui-col-xs4  layui-col-md4">
            <div class="layui-form-item">
                <label class="layui-form-label">浮动价格</label>
                <div class="layui-input-block">
                    <input class="layui-input" id="float_price" name="float_price" lay-verify="required" placeholder="浮动价格"
                           value="{{$currencies[0]->float_price}}" type="number">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button type="button" class="layui-btn" lay-submit lay-filter="save">保存</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
    <script>
       layui.use(['form', 'layer'], function() {
        var form = layui.form
        , layer = layui.layer
        form.on('select(select-filter)', function(data){
            let elem = data.elem; // 获得 select 原始 DOM 对象
            let float_price = $(elem).find("option:selected").attr("data-price")
            let value = data.value; // 获得被选中的值
            if(float_price && parseFloat(float_price) == 0) {
                float_price = 0
            }
            $('#float_price').val(float_price)
          });
        //表单提交事件
        form.on('submit(save)', function(dataObj){
           var data = dataObj.field;
            $.ajax({
                type: 'POST'
                ,url: '/admin/hqControl/savePrice'
                ,data: data
                ,dataType: "json"
                ,success: function(data) {
                     if(data.type == 'ok') {
                          layer.msg("操作成功", {
                            icon: 1,
                            time: 1000,
                            end: function() {
                                // parent.window.location.reload();
                            }
                        });
                     }else {
                        layer.msg(data.message, {icon:2});
                    }
                }
                ,error: function(data) {
                   layer.msg('错误：' + data.statusText, {icon: 2});
                }
            });
            parent.layui.layer.close();
            return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
    
        });
    });
    </script>
@endsection
