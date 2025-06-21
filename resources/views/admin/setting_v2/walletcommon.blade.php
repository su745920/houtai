<style>
    .layui-form-item  .address-input {
        width: 350px;
    }
</style>
<!--<div class="layui-form-item" id="wallet_setting_usdt_trc">-->
<!--    <label class="layui-form-label">USDT(TRC)：地址</label>-->
<!--    <div class="layui-input-inline address-input">-->
<!--        <input type="text" name="usdt_trc_address" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdt_trc']['address'])){{$setting['usdt_trc']['address']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最小额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="usdt_trc_erc_min" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdt_trc']['erc_min'])){{$setting['usdt_trc']['erc_min']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最大额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--    <input type="text" name="usdt_trc_erc_max" autocomplete="off" class="layui-input"-->
<!--           value="@if(isset($setting['usdt_trc']['erc_max'])){{$setting['usdt_trc']['erc_max']}}@endif">-->
<!--</div>-->
<!--<label class="layui-form-label" style="width:auto">凭证开关</label>-->
<!--<div class="layui-input-inline" style="width:auto">-->
<!--    @if(isset($setting['usdt_trc']['voucher_switch']) && $setting['usdt_trc']['voucher_switch'] =='on')-->
<!--        <input type="checkbox" name="usdt_trc_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--    @else-->
<!--        <input type="checkbox" name="usdt_trc_voucher_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--    @endif-->
<!--</div>-->
<!--<label class="layui-form-label" style="width:auto">通道开关</label>-->
<!--<div class="layui-input-inline" style="width:auto">-->
<!--    @if(isset($setting['usdt_trc']['switch']) && $setting['usdt_trc']['switch'] =='on')-->
<!--        <input type="checkbox" name="usdt_trc_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--    @else-->
<!--        <input type="checkbox" name="usdt_trc_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--    @endif-->
<!--</div>-->
<!--<div class="layui-input-inline" style="width:auto;margin-left:10px;">-->
<!--    @if(strpos($authorityList,"9998")>0)-->
<!--    <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usdt_trc">编辑</a>-->
<!--    <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usdt_trc" style="display:none;margin-left:0;">取消</a>-->
<!--    <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usdt_trc" lay-submit lay-filter="usdt_trc" style="display:none">提交</a>-->
<!--    @endif-->
<!--</div>-->
<!--</div>-->

<!--<div class="layui-form-item" id="wallet_setting_usdt_erc">-->
<!--    <label class="layui-form-label">USDT(ERC)：地址</label>-->
<!--    <div class="layui-input-inline address-input">-->
<!--        <input type="text" name="usdt_erc_address" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdt_erc']['address'])){{$setting['usdt_erc']['address']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最小额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="usdt_erc_erc_min" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdt_erc']['erc_min'])){{$setting['usdt_erc']['erc_min']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最大额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--    <input type="text" name="usdt_erc_erc_max" autocomplete="off" class="layui-input"-->
<!--           value="@if(isset($setting['usdt_erc']['erc_max'])){{$setting['usdt_erc']['erc_max']}}@endif">-->
<!--</div>-->
<!--<label class="layui-form-label" style="width:auto">凭证开关</label>-->
<!--<div class="layui-input-inline" style="width:auto">-->
<!--    @if(isset($setting['usdt_erc']['voucher_switch']) && $setting['usdt_erc']['voucher_switch'] =='on')-->
<!--        <input type="checkbox" name="usdt_erc_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--    @else-->
<!--        <input type="checkbox" name="usdt_erc_voucher_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--    @endif-->
<!--</div>-->
<!--<label class="layui-form-label" style="width:auto">通道开关</label>-->
<!--<div class="layui-input-inline" style="width:auto">-->
<!--    @if(isset($setting['usdt_erc']['switch']) && $setting['usdt_erc']['switch'] =='on')-->
<!--        <input type="checkbox" name="usdt_erc_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--    @else-->
<!--        <input type="checkbox" name="usdt_erc_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--    @endif-->
<!--</div>-->
<!--<div class="layui-input-inline" style="width:auto;margin-left:10px;">-->
<!--    @if(strpos($authorityList,"9998")>0)-->
<!--    <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usdt_erc">编辑</a>-->
<!--    <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usdt_erc" style="display:none;margin-left:0;">取消</a>-->
<!--    <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usdt_erc" lay-submit lay-filter="usdt_erc" style="display:none">提交</a>-->
<!--    @endif-->
<!--</div>-->
<!--</div>-->
<!--<div class="layui-form-item" id="wallet_setting_usdc">-->
<!--    <label class="layui-form-label">USDC：地址</label>-->
<!--    <div class="layui-input-inline address-input">-->
<!--        <input type="text" name="usdc_address" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdc']['address'])){{$setting['usdc']['address']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最小额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="usdc_erc_min" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdc']['erc_min'])){{$setting['usdc']['erc_min']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最大额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="usdc_erc_max" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['usdc']['erc_max'])){{$setting['usdc']['erc_max']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">凭证开关</label>-->
<!--    <div class="layui-input-inline" style="width:auto">-->
<!--        @if(isset($setting['usdc']['voucher_switch']) && $setting['usdc']['voucher_switch'] =='on')-->
<!--            <input type="checkbox" name="usdc_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--        @else-->
<!--            <input type="checkbox" name="usdc_voucher_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--        @endif-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">通道开关</label>-->
<!--    <div class="layui-input-inline" style="width:auto">-->
<!--        @if(isset($setting['usdc']['switch']) && $setting['usdc']['switch'] =='on')-->
<!--            <input type="checkbox" name="usdc_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--        @else-->
<!--            <input type="checkbox" name="usdc_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--        @endif-->
<!--    </div>-->
<!--    <div class="layui-input-inline" style="width:auto;margin-left:10px;">-->
<!--        @if(strpos($authorityList,"9998")>0)-->
<!--        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usdc">编辑</a>-->
<!--        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usdc" style="display:none;margin-left:0;">取消</a>-->
<!--        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usdc" lay-submit lay-filter="usdc" style="display:none">提交</a>-->
<!--        @endif-->
<!--    </div>-->
<!--</div>-->
<!--<div class="layui-form-item" id="wallet_setting_eth">-->
<!--    <label class="layui-form-label">ETH：地址</label>-->
<!--    <div class="layui-input-inline address-input">-->
<!--        <input type="text" name="eth_address" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['eth']['address'])){{$setting['eth']['address']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最小额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="eth_erc_min" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['eth']['erc_min'])){{$setting['eth']['erc_min']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最大额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="eth_erc_max" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['eth']['erc_max'])){{$setting['eth']['erc_max']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">凭证开关</label>-->
<!--    <div class="layui-input-inline" style="width:auto">-->
<!--        @if(isset($setting['eth']['voucher_switch']) && $setting['eth']['voucher_switch'] =='on')-->
<!--            <input type="checkbox" name="eth_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--        @else-->
<!--            <input type="checkbox" name="eth_voucher_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--        @endif-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">通道开关</label>-->
<!--    <div class="layui-input-inline" style="width:auto">-->
<!--        @if(isset($setting['eth']['switch']) && $setting['eth']['switch'] =='on')-->
<!--            <input type="checkbox" name="eth_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--        @else-->
<!--            <input type="checkbox" name="eth_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--        @endif-->
<!--    </div>-->
<!--    <div class="layui-input-inline" style="width:auto;margin-left:10px;">-->
<!--        @if(strpos($authorityList,"9998")>0)-->
<!--        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_eth">编辑</a>-->
<!--        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_eth" style="display:none;margin-left:0;">取消</a>-->
<!--        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_eth" lay-submit lay-filter="eth" style="display:none">提交</a>-->
<!--        @endif-->
<!--    </div>-->
<!--</div>-->
<!--<div class="layui-form-item" id="wallet_setting_bit">-->
<!--    <label class="layui-form-label">BTC(bitcoin)：地址</label>-->
<!--    <div class="layui-input-inline address-input">-->
<!--        <input type="text" name="bit_address" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['bit']['address'])){{$setting['bit']['address']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最小额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="bit_erc_min" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['bit']['erc_min'])){{$setting['bit']['erc_min']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">最大额度</label>-->
<!--    <div class="layui-input-inline" style="width:80px">-->
<!--        <input type="text" name="bit_erc_max" autocomplete="off" class="layui-input"-->
<!--               value="@if(isset($setting['bit']['erc_max'])){{$setting['bit']['erc_max']}}@endif">-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">凭证开关</label>-->
<!--    <div class="layui-input-inline" style="width:auto">-->
<!--        @if(isset($setting['bit']['voucher_switch']) && $setting['bit']['voucher_switch'] =='on')-->
<!--            <input type="checkbox" name="bit_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--        @else-->
<!--            <input type="checkbox" name="bit_voucher_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--        @endif-->
<!--    </div>-->
<!--    <label class="layui-form-label" style="width:auto">通道开关</label>-->
<!--    <div class="layui-input-inline" style="width:auto">-->
<!--        @if(isset($setting['bit']['switch']) && $setting['bit']['switch'] =='on')-->
<!--            <input type="checkbox" name="bit_switch" lay-skin="switch" lay-text="开启|关闭" checked>-->
<!--        @else-->
<!--            <input type="checkbox" name="bit_switch" lay-skin="switch" lay-text="开启|关闭">-->
<!--        @endif-->
<!--    </div>-->
<!--    <div class="layui-input-inline" style="width:auto;margin-left:10px;">-->
<!--        @if(strpos($authorityList,"9998")>0)-->
<!--        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_bit">编辑</a>-->
<!--        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_bit" style="display:none;margin-left:0;">取消</a>-->
<!--        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_bit" lay-submit lay-filter="bit" style="display:none">提交</a>-->
<!--        @endif-->
<!--    </div>-->
<!--</div>-->

<!--还款设置-->
<div class="layui-form-item" id="wallet_setting_usdt_trc_repayment">
    <label class="layui-form-label">还款USDT(ERC)：地址</label>
    <div class="layui-input-inline address-input">
        <input type="text" name="usdt_trc_address_repayment" autocomplete="off" class="layui-input"
               value="@if(isset($setting['usdt_trc_repayment']['address'])){{$setting['usdt_trc_repayment']['address']}}@endif">
    </div>
    <label class="layui-form-label" style="width:auto">通道开关</label>
    <div class="layui-input-inline" style="width:auto">
        @if(isset($setting['usdt_trc_repayment']['switch']) && $setting['usdt_trc_repayment']['switch'] =='on')
            <input type="checkbox" name="usdt_trc_switch_repayment" lay-skin="switch" lay-text="开启|关闭" checked>
        @else
            <input type="checkbox" name="usdt_trc_switch_repayment" lay-skin="switch" lay-text="开启|关闭">
        @endif
    </div>
    <div class="layui-input-inline" style="width:auto;margin-left:10px;">
        @if(strpos($authorityList,"9998")>0)
        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usdt_trc_repayment">编辑</a>
        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usdt_trc_repayment" style="display:none;margin-left:0;">取消</a>
        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usdt_trc_repayment" lay-submit lay-filter="usdt_trc_repayment" style="display:none">提交</a>
        @endif
    </div>
</div>
<div class="layui-form-item" id="wallet_setting_usdt_erc_repayment">
    <label class="layui-form-label">还款USDT(TRC)：地址</label>
    <div class="layui-input-inline address-input">
        <input type="text" name="usdt_erc_address_repayment" autocomplete="off" class="layui-input"
               value="@if(isset($setting['usdt_erc_repayment']['address'])){{$setting['usdt_erc_repayment']['address']}}@endif">
    </div>
    <label class="layui-form-label" style="width:auto">通道开关</label>
    <div class="layui-input-inline" style="width:auto">
        @if(isset($setting['usdt_erc_repayment']['switch']) && $setting['usdt_erc_repayment']['switch'] =='on')
            <input type="checkbox" name="usdt_erc_switch_repayment" lay-skin="switch" lay-text="开启|关闭" checked>
        @else
            <input type="checkbox" name="usdt_erc_switch_repayment" lay-skin="switch" lay-text="开启|关闭">
        @endif
    </div>
    <div class="layui-input-inline" style="width:auto;margin-left:10px;">
        @if(strpos($authorityList,"9998")>0)
        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usdt_erc_repayment">编辑</a>
        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usdt_erc_repayment" style="display:none;margin-left:0;">取消</a>
        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usdt_erc_repayment" lay-submit lay-filter="usdt_erc_repayment" style="display:none">提交</a>
        @endif
    </div>
</div>
<div class="layui-form-item" id="wallet_setting_usdc_repayment">
    <label class="layui-form-label">还款USDC：地址</label>
    <div class="layui-input-inline address-input">
        <input type="text" name="usdc_address_repayment" autocomplete="off" class="layui-input"
               value="@if(isset($setting['usdc_repayment']['address'])){{$setting['usdc_repayment']['address']}}@endif">
    </div>
    <label class="layui-form-label" style="width:auto">通道开关</label>
    <div class="layui-input-inline" style="width:auto">
        @if(isset($setting['usdc_repayment']['switch']) && $setting['usdc_repayment']['switch'] =='on')
            <input type="checkbox" name="usdc_switch_repayment" lay-skin="switch" lay-text="开启|关闭" checked>
        @else
            <input type="checkbox" name="usdc_switch_repayment" lay-skin="switch" lay-text="开启|关闭">
        @endif
    </div>
    <div class="layui-input-inline" style="width:auto;margin-left:10px;">
        @if(strpos($authorityList,"9998")>0)
        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usdc_repayment">编辑</a>
        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usdc_repayment" style="display:none;margin-left:0;">取消</a>
        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usdc_repayment" lay-submit lay-filter="usdc_repayment" style="display:none">提交</a>
        @endif
    </div>
</div>
<div class="layui-form-item" id="wallet_setting_eth_repayment">
    <label class="layui-form-label">还款ETH：地址</label>
    <div class="layui-input-inline address-input">
        <input type="text" name="eth_address_repayment" autocomplete="off" class="layui-input"
               value="@if(isset($setting['eth_repayment']['address'])){{$setting['eth_repayment']['address']}}@endif">
    </div>
    <label class="layui-form-label" style="width:auto">通道开关</label>
    <div class="layui-input-inline" style="width:auto">
        @if(isset($setting['eth_repayment']['switch']) && $setting['eth_repayment']['switch'] =='on')
            <input type="checkbox" name="eth_switch_repayment" lay-skin="switch" lay-text="开启|关闭" checked>
        @else
            <input type="checkbox" name="eth_switch_repayment" lay-skin="switch" lay-text="开启|关闭">
        @endif
    </div>
    <div class="layui-input-inline" style="width:auto;margin-left:10px;">
        @if(strpos($authorityList,"9998")>0)
        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_eth_repayment">编辑</a>
        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_eth_repayment" style="display:none;margin-left:0;">取消</a>
        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_eth_repayment" lay-submit lay-filter="eth_repayment" style="display:none">提交</a>
        @endif
    </div>
</div>
<div class="layui-form-item" id="wallet_setting_bit_repayment">
    <label class="layui-form-label">还款BTC(bitcoin)：地址</label>
    <div class="layui-input-inline address-input">
        <input type="text" name="bit_address_repayment" autocomplete="off" class="layui-input"
               value="@if(isset($setting['bit_repayment']['address'])){{$setting['bit_repayment']['address']}}@endif">
    </div>
    <label class="layui-form-label" style="width:auto">通道开关</label>
    <div class="layui-input-inline" style="width:auto">
        @if(isset($setting['bit_repayment']['switch']) && $setting['bit_repayment']['switch'] =='on')
            <input type="checkbox" name="bit_switch_repayment" lay-skin="switch" lay-text="开启|关闭" checked>
        @else
            <input type="checkbox" name="bit_switch_repayment" lay-skin="switch" lay-text="开启|关闭">
        @endif
    </div>
    <div class="layui-input-inline" style="width:auto;margin-left:10px;">
        @if(strpos($authorityList,"9998")>0)
        <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_bit_repayment">编辑</a>
        <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_bit_repayment" style="display:none;margin-left:0;">取消</a>
        <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_bit_repayment" lay-submit lay-filter="bit_repayment" style="display:none">提交</a>
        @endif
    </div>
</div>

<div class="layui-form-item" id="wallet_setting_th">
    <span style="color: red;">泰国电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="th_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['bankname'])){{$setting['th']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="th_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['account_no'])){{$setting['th']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="th_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['account_name'])){{$setting['th']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="th_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['bank_address'])){{$setting['th']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="th_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['swiftcode'])){{$setting['th']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="th_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['company_address'])){{$setting['th']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="th_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['erc_min'])){{$setting['th']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="th_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['th']['erc_max'])){{$setting['th']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['th']['voucher_switch']) && $setting['th']['voucher_switch'] =='on')
                <input type="checkbox" name="th_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="th_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['th']['switch']) && $setting['th']['switch'] =='on')
                <input type="checkbox" name="th_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="th_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
            <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_th">编辑</a>
            <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_th" style="display:none;margin-left:0;">取消</a>
            <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_th" lay-submit lay-filter="th" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>

<div class="layui-form-item" id="wallet_setting_id">
    <span style="color: red;">印尼电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="id_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['bankname'])){{$setting['id']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="id_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['account_no'])){{$setting['id']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="id_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['account_name'])){{$setting['id']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="id_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['bank_address'])){{$setting['id']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="id_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['swiftcode'])){{$setting['id']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="id_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['company_address'])){{$setting['id']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="id_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['erc_min'])){{$setting['id']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="id_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['id']['erc_max'])){{$setting['id']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['id']['voucher_switch']) && $setting['id']['voucher_switch'] =='on')
                <input type="checkbox" name="id_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="id_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['id']['switch']) && $setting['id']['switch'] =='on')
                <input type="checkbox" name="id_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="id_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
                <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_id">编辑</a>
                <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_id" style="display:none;margin-left:0;">取消</a>
                <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_id" lay-submit lay-filter="id" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>





<div class="layui-form-item" id="wallet_setting_vi">
    <span style="color: red;">越南电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['bankname'])){{$setting['vi']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['account_no'])){{$setting['vi']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['account_name'])){{$setting['vi']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['bank_address'])){{$setting['vi']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['swiftcode'])){{$setting['vi']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['company_address'])){{$setting['vi']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['erc_min'])){{$setting['vi']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="vi_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['vi']['erc_max'])){{$setting['vi']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['vi']['voucher_switch']) && $setting['vi']['voucher_switch'] =='on')
                <input type="checkbox" name="vi_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="vi_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['vi']['switch']) && $setting['vi']['switch'] =='on')
                <input type="checkbox" name="vi_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="vi_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
                <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_vi">编辑</a>
                <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_vi" style="display:none;margin-left:0;">取消</a>
                <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_vi" lay-submit lay-filter="vi" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>


<div class="layui-form-item" id="wallet_setting_kor">
    <span style="color: red;">韩国电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['bankname'])){{$setting['kor']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['account_no'])){{$setting['kor']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['account_name'])){{$setting['kor']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['bank_address'])){{$setting['kor']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['swiftcode'])){{$setting['kor']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['company_address'])){{$setting['kor']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['erc_min'])){{$setting['kor']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="kor_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['kor']['erc_max'])){{$setting['kor']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['kor']['voucher_switch']) && $setting['kor']['voucher_switch'] =='on')
                <input type="checkbox" name="kor_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="kor_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['kor']['switch']) && $setting['kor']['switch'] =='on')
                <input type="checkbox" name="kor_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="kor_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
                <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_kor">编辑</a>
                <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_kor" style="display:none;margin-left:0;">取消</a>
                <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_kor" lay-submit lay-filter="kor" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>



<div class="layui-form-item" id="wallet_setting_china_eft">
    <span style="color: red;">以下是国内汇款:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="china_eft_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['china_eft']['bankname'])){{$setting['china_eft']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="china_eft_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['china_eft']['account_no'])){{$setting['china_eft']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="china_eft_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['china_eft']['account_name'])){{$setting['china_eft']['account_name']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="china_eft_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['china_eft']['erc_min'])){{$setting['china_eft']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="china_eft_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['china_eft']['erc_max'])){{$setting['china_eft']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['china_eft']['voucher_switch']) && $setting['china_eft']['voucher_switch'] =='on')
                <input type="checkbox" name="china_eft_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="china_eft_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['china_eft']['switch']) && $setting['china_eft']['switch'] =='on')
                <input type="checkbox" name="china_eft_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="china_eft_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
            <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_china_eft">编辑</a>
            <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_china_eft" style="display:none;margin-left:0;">取消</a>
            <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_china_eft" lay-submit lay-filter="china_eft" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>