<div class="layui-form-item">
    <label class="layui-form-label">平台名称</label>
    <div class="layui-input-inline">
        <input type="text" name="app_name" autocomplete="off" class="layui-input"
            value="@if(isset($setting['app_name'])){{$setting['app_name'] ?? ''}}@endif">
    </div>
</div>
<!--<div class="layui-form-item">-->
<!--    <label class="layui-form-label">USDT汇率</label>-->
<!--    <div class="layui-input-inline" style="display: flex;">-->
<!--        <input type="text" name="USDTRate" autocomplete="off" class="layui-input"-->
<!--            value="@if(isset($setting['USDTRate'])){{$setting['USDTRate']}}@endif">-->
<!--        <span style="width: 100px;margin-top: 10px;font-weight: 700;">&nbsp;(卢比)</span>-->
<!--    </div>-->
<!--</div>-->
<div class="layui-form-item">
    <label class="layui-form-label">在线客服</label>
    <div class="layui-input-inline">
        <input type="text" name="service_url" style="width: 400px" autocomplete="off" class="layui-input"
            value="@if(isset($setting['service_url'])){{$setting['service_url']}}@endif">
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">联系telegram</label>
    <div class="layui-input-inline">
        <input type="text" name="telegram_url" style="width: 400px" autocomplete="off" class="layui-input"
            value="@if(isset($setting['telegram_url'])){{$setting['telegram_url']}}@endif">
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">APP下载</label>
    <div class="layui-input-block">
        <div class="layui-input-inline" style="width: 400px">
            <input type="text" class="layui-input" name="registered_jump" value="{{$setting['registered_jump'] ?? ''}}" placeholder="APP下载地址" >
        </div>
    </div>
</div>


<div class="layui-form-item">
    <label class="layui-form-label">网站标题</label>
    <div class="layui-input-inline">
        <input type="text" name="web_site_title" style="width: 400px" autocomplete="off" class="layui-input"
            value="@if(isset($setting['web_site_title'])){{$setting['web_site_title']}}@endif">
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">网站描述</label>
    <div class="layui-input-inline">
        <input type="text" name="web_site_desc" style="width: 400px" autocomplete="off" class="layui-input"
            value="@if(isset($setting['web_site_desc'])){{$setting['web_site_desc']}}@endif">
    </div>

</div>
<div class="layui-form-item">
    <label class="layui-form-label">网站关键字</label>
    <div class="layui-input-inline">
        <input type="text" name="web_site_keyword" style="width: 400px" autocomplete="off" class="layui-input"
               value="@if(isset($setting['web_site_keyword'])){{$setting['web_site_keyword']}}@endif">
    </div>

</div>

<div class="layui-form-item">
    <label class="layui-form-label">提款最低信用分</label>
    <div class="layui-input-inline">
        <input type="text" name="credit_score" style="width: 400px" autocomplete="off" class="layui-input"
               value="@if(isset($setting['credit_score'])){{$setting['credit_score']}}@endif">
    </div>

</div>


<!--<div class="layui-form-item">-->
<!--    <label class="layui-form-label">USDT地址(TRC)(会员注册时显示)</label>-->
<!--    <div class="layui-input-inline">-->
<!--        <input type="text" name="USDTAddress" autocomplete="off" class="layui-input"-->
<!--            value="@if(isset($setting['USDTAddress'])){{$setting['USDTAddress']}}@endif">-->
<!--    </div>-->
<!--</div>-->
<!--<div class="layui-form-item">-->
<!--    <label class="layui-form-label">USDT地址(ERC)(会员注册时显示)</label>-->
<!--    <div class="layui-input-inline">-->
<!--        <input type="text" name="USDTAddress_erc" autocomplete="off" class="layui-input"-->
<!--            value="@if(isset($setting['USDTAddress_erc'])){{$setting['USDTAddress_erc']}}@endif">-->
<!--    </div>-->
<!--</div>-->
<!--<div class="layui-form-item">-->
<!--    <label class="layui-form-label">ETH地址(会员注册时显示)</label>-->
<!--    <div class="layui-input-inline">-->
<!--        <input type="text" name="ETHAddress" autocomplete="off" class="layui-input"-->
<!--            value="@if(isset($setting['ETHAddress'])){{$setting['ETHAddress']}}@endif">-->
<!--    </div>-->
<!--</div>-->
<!--<div class="layui-form-item">-->
<!--    <label class="layui-form-label">BTC地址(会员注册时显示)</label>-->
<!--    <div class="layui-input-inline">-->
<!--        <input type="text" name="BTCAddress" autocomplete="off" class="layui-input"-->
<!--            value="@if(isset($setting['BTCAddress'])){{$setting['BTCAddress']}}@endif">-->
<!--    </div>-->
<!--</div>-->
<div class="layui-form-item" style="display:none">
    <label class="layui-form-label">拆盲盒消耗USDT数量</label>
    <div class="layui-input-inline">
        <input type="text" name="Consumptionoffunds" autocomplete="off" class="layui-input"
            value="@if(isset($setting['Consumptionoffunds'])){{$setting['Consumptionoffunds']}}@endif">
    </div>
</div>

<div class="layui-form-item" style="display: none;">
    <label class="layui-form-label">总账号自动加密私钥</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="auto_encrypt_private" value="1" title="打开" @if (isset($setting['auto_encrypt_private'])) {{$setting['auto_encrypt_private'] == 1 ? 'checked' : ''}} @else checked @endif >
            <input type="radio" name="auto_encrypt_private" value="0" title="关闭" @if (isset($setting['auto_encrypt_private'])) {{$setting['auto_encrypt_private'] == 0 ? 'checked' : ''}} @endif >
        </div>
    </div>
</div>
		
@include('admin.setting.recharge_withdraw')