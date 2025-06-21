@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">卖方账号</label>
            <div class="layui-input-inline">
                <input type="text" name="sell_account" lay-verify="required" disabled autocomplete="off" placeholder="" class="layui-input" value="su123123">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">买方账号</label>
            <div class="layui-input-inline">
                <input type="text" name="buy_account" lay-verify="required" disabled autocomplete="off" placeholder="" class="layui-input" value="su123123">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">交易币</label>
            <div class="layui-input-inline">
                <select name="currency_id" lay-filter="" lay-search>
                    <option value=""></option>
                    @if(!empty($currencies))
                    @foreach($currencies as $currency)
                    <option value="{{$currency->id}}" @if($currency->id == $result->currency_id) selected @endif>{{$currency->name}}</option>
                    @endforeach
                        @endif
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">法币</label>
            <div class="layui-input-inline">
                <select name="legal_id" lay-filter="">
                    <option value=""></option>
                    @if(!empty($currencies))
                    @foreach($legals as $legal)
                        <option value="{{$legal->id}}" @if($legal->id == $result->legal_id) selected @endif>{{$legal->name}}</option>
                    @endforeach
                        @endif
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">涨幅权重</label>
            <div class="layui-input-inline">
                <select name="up_weight" lay-filter="" lay-search>
                    <option value=""></option>

                    @for($i = 0; $i <= 10; $i++)
                    <option value="{{$i}}" @if($i == $result->up_weight) selected @endif>{{$i}}</option>
                    @endfor
                </select>
            </div>
            和跌幅权重的总值不得大于10，目的是设定机器人的涨跌趋势，当其设定为8时，及下次价格的计算有80%的概率的涨。非必要情况下不要设置10:0,0:10这种极端比例，图形很难看。
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">跌幅权重</label>
            <div class="layui-input-inline">
                <select name="down_weight" lay-filter="" lay-search>
                    <option value=""></option>

                    @for($i = 0; $i <= 10; $i++)
                    <option value="{{$i}}" @if($i == $result->down_weight) selected @endif>{{$i}}</option>
                    @endfor
                </select>
            </div>
            和涨幅权重的总值不得大于10，目的是设定机器人的涨跌趋势，当其设定为8时，及下次价格的计算有80%的概率的跌。非必要情况下不要设置10:0,0:10这种极端比例，图形很难看。
            
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">目标价格</label>
            <div class="layui-input-inline">
                <input type="text" name="init_price" lay-verify="required" autocomplete="off" placeholder="" class="layui-input" value="@if(!empty($result->init_price)){{$result->init_price}}@endif">
            </div>
            没有行情时取此价格开始，有行情时根据此行情设置震荡空间，当涨跌幅设置为9vs1或1vs9时，为全力拉或压价，此时每隔5-10秒拉升价格0.05%到0.5%，一个小时以内即能拉升或打压价格100%<br/>
            
        </div>

        
        <input type="hidden" name="id" value="@if(!empty($result->id)){{$result->id}}@endif">
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


        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/robot/robot_add')}}'
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