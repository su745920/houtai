<div class="layui-form-item" id="wallet_setting_usd">
    <span style="color: red;">美元电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['bankname'])){{$setting['usd']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['account_no'])){{$setting['usd']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['account_name'])){{$setting['usd']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['bank_address'])){{$setting['usd']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['swiftcode'])){{$setting['usd']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['company_address'])){{$setting['usd']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['erc_min'])){{$setting['usd']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="usd_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['usd']['erc_max'])){{$setting['usd']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['usd']['voucher_switch']) && $setting['usd']['voucher_switch'] =='on')
                <input type="checkbox" name="usd_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="usd_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['usd']['switch']) && $setting['usd']['switch'] =='on')
                <input type="checkbox" name="usd_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="usd_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
            <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_usd">编辑</a>
            <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_usd" style="display:none;margin-left:0;">取消</a>
            <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_usd" lay-submit lay-filter="usd" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>

<div class="layui-form-item" id="wallet_setting_eur">
    <span style="color: red;">欧元电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['bankname'])){{$setting['eur']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['account_no'])){{$setting['eur']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['account_name'])){{$setting['eur']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['bank_address'])){{$setting['eur']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['swiftcode'])){{$setting['eur']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['company_address'])){{$setting['eur']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['erc_min'])){{$setting['eur']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="eur_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['eur']['erc_max'])){{$setting['eur']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['eur']['voucher_switch']) && $setting['eur']['voucher_switch'] =='on')
                <input type="checkbox" name="eur_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="eur_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['eur']['switch']) && $setting['eur']['switch'] =='on')
                <input type="checkbox" name="eur_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="eur_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
                <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_eur">编辑</a>
                <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_eur" style="display:none;margin-left:0;">取消</a>
                <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_eur" lay-submit lay-filter="eur" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>





<div class="layui-form-item" id="wallet_setting_gbp">
    <span style="color: red;">英镑电汇:</span>
    <div class="layui-form-item">
        <label class="layui-form-label">银行名称</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_bankname" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['bankname'])){{$setting['gbp']['bankname']}}@endif">
        </div>
        <label class="layui-form-label">账户号码</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_account_no" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['account_no'])){{$setting['gbp']['account_no']}}@endif">
        </div>
        <label class="layui-form-label">收款账号名称</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_account_name" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['account_name'])){{$setting['gbp']['account_name']}}@endif">
        </div>
    </div>

    <div class="layui-form-item">
        <label class="layui-form-label">银行地址</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_bank_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['bank_address'])){{$setting['gbp']['bank_address']}}@endif">
        </div>
        <label class="layui-form-label">swiftcode</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_swiftcode" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['swiftcode'])){{$setting['gbp']['swiftcode']}}@endif">
        </div>
        <label class="layui-form-label">公司地址</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_company_address" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['company_address'])){{$setting['gbp']['company_address']}}@endif">
        </div>
    </div>
    <div class="layui-form-item">
        <label class="layui-form-label">最小额度</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_erc_min" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['erc_min'])){{$setting['gbp']['erc_min']}}@endif">
        </div>
        <label class="layui-form-label">最大额度</label>
        <div class="layui-input-inline">
            <input type="text" name="gbp_erc_max" autocomplete="off" class="layui-input"
                   value="@if(isset($setting['gbp']['erc_max'])){{$setting['gbp']['erc_max']}}@endif">
        </div>
        <label class="layui-form-label" style="width:auto">凭证开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['gbp']['voucher_switch']) && $setting['gbp']['voucher_switch'] =='on')
                <input type="checkbox" name="gbp_voucher_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="gbp_voucher_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
        <label class="layui-form-label" style="width:auto">通道开关</label>
        <div class="layui-input-inline" style="width:auto">
            @if(isset($setting['gbp']['switch']) && $setting['gbp']['switch'] =='on')
                <input type="checkbox" name="gbp_switch" lay-skin="switch" lay-text="开启|关闭" checked>
            @else
                <input type="checkbox" name="gbp_switch" lay-skin="switch" lay-text="开启|关闭">
            @endif
        </div>
    </div>
    <div class="layui-form-item">
        <div class="layui-input-block" style="margin-left:0;text-align:center;">
            @if(strpos($authorityList,"9998")>0)
                <a class="layui-btn wallet_setting_edit"  data-form="wallet_setting_gbp">编辑</a>
                <a class="layui-btn wallet_setting_cancel"  data-form="wallet_setting_gbp" style="display:none;margin-left:0;">取消</a>
                <a class="layui-btn wallet_setting_submit"  data-form="wallet_setting_gbp" lay-submit lay-filter="gbp" style="display:none">提交</a>
            @endif
        </div>
    </div>
</div>
