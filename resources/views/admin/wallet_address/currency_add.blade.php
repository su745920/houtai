@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<style>
    .layui-form-label{width:140px;}
    .layui-input-block{margin-left:170px;}
</style>
    <form class="layui-form" action="">
         <div class="layui-form-item">
            <label class="layui-form-label">币种选择</label>
            <div class="layui-input-block">
                <select name="name" lay-filter="" lay-verify="required">
                    <option value=""></option>
                    <option value="USDT(TRC20)" @if($result->name == 'USDT(TRC20)') selected @endif>USDT(TRC20)</option>
                    <option value="USDT(ERC20)" @if($result->name == 'USDT(ERC20)') selected @endif>USDT(ERC20)</option>
                    <option value="ETH" @if($result->name == 'ETH') selected @endif>ETH</option>
                    <option value="BTC" @if($result->name == 'BTC') selected @endif>BTC</option>
                    <option value="USDC" @if($result->name == 'USDC') selected @endif>USDC</option>
 
                    <option value="XRP" @if($result->name == 'XRP') selected @endif>XRP</option>
                    <option value="SOL" @if($result->name == 'SOL') selected @endif>SOL</option>
                    <option value="BNB" @if($result->name == 'BNB') selected @endif>BNB</option>
                    <option value="DOGE" @if($result->name == 'DOGE') selected @endif>DOGE</option>
                    <option value="ADA" @if($result->name == 'ADA') selected @endif>ADA</option>
                    <option value="SHIB" @if($result->name == 'SHIB') selected @endif>SHIB</option>
                    <option value="FIL" @if($result->name == 'FIL') selected @endif>FIL</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">地址</label>
            <div class="layui-input-block">
                <input type="text" name="address" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->address)){{$result->address}}@endif"/>
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">最小额度</label>
            <div class="layui-input-block">
                <input type="number" name="min_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->min_limit)){{$result->min_limit}}@endif">
            </div>
        </div>
        <div class="layui-form-item" style="width: 100%;">
            <label class="layui-form-label">最大额度</label>
            <div class="layui-input-block">
                <input type="number" name="max_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->max_limit)){{$result->max_limit}}@endif">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">凭证开关:</label>
            <div class="layui-input-block">
                <select name="is_voucher" lay-filter="">
                    <option value="1" @if($result->is_voucher == 1) selected @endif>开启</option>
                    <option value="0" @if($result->is_voucher == 0) selected @endif>关闭</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">是否显示</label>
            <div class="layui-input-block">
                <select name="is_show" lay-filter="">
                    <option value="1" @if($result->is_show == 1) selected @endif>是</option>
                    <option value="0" @if($result->is_show == 0) selected @endif>否</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item" style="width: 100%;">
            <label class="layui-form-label">排序</label>
            <div class="layui-input-block">
                <input type="number" name="sort" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->sort)){{$result->sort}}@endif">
            </div>
        </div>
        <input type="hidden" name="id" value="@if(!empty($result->id)){{$result->id}}@endif">
        <input type="hidden" name="wallet_address_id" value="@if(!empty($wallet_address_id)){{$wallet_address_id}}@endif">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="currency_submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        layui.use(['element','form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,element = layui.element
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(currency_submit)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/wallet_address_list/postAdd')}}'
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