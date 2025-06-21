@extends('admin._layoutNew')

@section('page-head')
<style>
    .hide {
        display: none;
    }
    .icon-tips-help {
        font-weight: bolder;
        border: 1px solid #b7b7b7;
        font-size: 12px;
        border-radius: 50%;
        padding: 1px;
        color: #f9c83f;
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-tab-content">
            <div class="layui-form-item">
                <label class="layui-form-label">ID：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$id}}</label>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">用户：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$account_number}}</label>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">币种：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$name}}</label>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">申购数量：</label>
                <div class="layui-input-inline">
                    <label class="layui-form-label" style="text-align:left;">{{$coin_amount}}</label>
                </div>
            </div>
            
            <div class="layui-form-item">
                <label class="layui-form-label">通过数量：</label>
                <div class="layui-input-inline">
                    <input type="number" step=0.01 name="passed_amount" max="{{$coin_amount}}" min=0 style="padding-left:15px;" autocomplete="off" placeholder="通过数量" class="layui-input" value="0">
                </div>
                <div class="layui-form-mid layui-word-aux">通过数量不能大于申购数量</div>
            </div>
        </div>
        <input id="coin_amount" type="hidden" name="coin_amount" value="{{$coin_amount}}">
        <input id="currency_order_id" type="hidden" name="id" value="{{$id}}">
        <div class="layui-form-item">
            <div class="layui-input-block" style="margin-left:0px;text-align:center;">
                <button class="layui-btn" type="button" lay-submit="" lay-filter="*">确定</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
<script>
    function NumberCheck(obj) {
      var str = $(obj).val();
      var len1 = str.substr(0, 1);
      var len2 = str.substr(1, 1);
      //如果第一位是0，第二位不是点，就用数字把点替换掉
      if (str.length > 1 && len1 == 0 && len2 != ".") {
        str = str.substr(1, 1);
      }
      //第一位不能是.
      if (len1 == ".") {
        str = "";
      }
      //限制只能输入一个小数点
      if (str.indexOf(".") != -1) {
        var str_ = str.substr(str.indexOf(".") + 1);
        if (str_.indexOf(".") != -1) {
          str = str.substr(0, str.indexOf(".") + str_.indexOf(".") + 1);
        }
      }
      //正则替换，保留数字和小数点
      //str = str.replace(/[^\d^\.]+/g,'')
      //如果需要保留小数点后两位，则用下面公式
      str = str.replace(/\.\d\d$/,'')
      $(obj).val(str.replace(/^\D*(\d*(?:\.\d{0,3})?).*$/g,'$1'))
    }
    layui.use([ 'form','layer'], function () {
        // layui模块
        var upload = layui.upload 
            ,form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
        var coin_amount = Number($('#coin_amount').val())

        
        // 监听提交
        form.on('submit(*)', function(data) {
            var data = data.field;
            data.passed_amount = Number(data.passed_amount)
            if(data.passed_amount > coin_amount){
                layer.alert('通过数量不能大于申购数量')
                return false;
            }
            else{
                layer.confirm('真的要通过申购订单吗？', function (index) {
                    $.ajax({
                        url: '/admin/currency/project/sell/confirm'
                        ,type: 'post'
                        ,dataType: 'json'
                        ,data : data
                        ,success: function(res) {
                            layer.msg(res.message, {
                                time: 2000
                                ,end: function () {
                                    if (res.type == 'ok') {
                                        var parent_index = parent.layer.getFrameIndex(window.name); // 先得到当前iframe层的索引
                                        parent.layer.close(parent_index); // 再执行关闭
                                        parent.window.layui.table.reload('mobileSearch', {});
                                    }
                                }
                            })
                        }
                    });
                    return false;
                });
            }
        });
    });
</script>
@endsection