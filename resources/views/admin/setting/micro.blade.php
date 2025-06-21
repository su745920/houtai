<div class="layui-form-item">
    <label class="layui-form-label">止盈止亏功能</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="risk_mode" value="5" title="概率" @if (isset($setting['risk_mode'])) {{$setting['risk_mode'] == 5 ? 'checked' : ''}} @endif >
            <input type="radio" name="risk_mode" value="1" title="点控" @if (isset($setting['risk_mode'])) {{$setting['risk_mode'] == 1 ? 'checked' : ''}} @else checked @endif >
            <input type="radio" name="risk_mode" value="4" title="单控" @if (isset($setting['risk_mode'])) {{$setting['risk_mode'] == 4 ? 'checked' : ''}} @else checked @endif >
        </div>
        <div class="layui-form-mid layui-word-aux">针对交割合约交易进行设置</div>
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">概率指标</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="text" name="risk_profit_probability" class="layui-input" value="{{$setting['risk_profit_probability'] ?? 0 }}" placeholder="交割合约交易风控概率达到或大于设定值时才会赢">
        </div>
        <div class="layui-form-mid layui-word-aux">%</div>
        <div class="layui-form-mid layui-word-aux">交割合约交易风控概率达到或大于设定值时才会赢</div>
    </div>
</div>