



<div class="layui-form-item">
    <label class="layui-form-label">USDT地址(TRC)(会员注册时显示)</label>
    <div class="layui-input-inline">
        <input type="text" name="USDTAddress" autocomplete="off" class="layui-input"
            value="@if(isset($setting['USDTAddress'])){{$setting['USDTAddress']}}@endif">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">USDT地址(ERC)(会员注册时显示)</label>
    <div class="layui-input-inline">
        <input type="text" name="USDTAddress_erc" autocomplete="off" class="layui-input"
            value="@if(isset($setting['USDTAddress_erc'])){{$setting['USDTAddress_erc']}}@endif">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">ETH地址(会员注册时显示)</label>
    <div class="layui-input-inline">
        <input type="text" name="ETHAddress" autocomplete="off" class="layui-input"
            value="@if(isset($setting['ETHAddress'])){{$setting['ETHAddress']}}@endif">
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">BTC地址(会员注册时显示)</label>
    <div class="layui-input-inline">
        <input type="text" name="BTCAddress" autocomplete="off" class="layui-input"
            value="@if(isset($setting['BTCAddress'])){{$setting['BTCAddress']}}@endif">
    </div>
</div>

