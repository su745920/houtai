<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-dollar"></i>
        <span>合约交易</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label" style="color:#F56C6C;">交易手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="contract_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['contract_fee'])){{$setting['contract_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
    </div>
</fieldset>



<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-fire"></i>
        <span>币币交易</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label" style="color:#F56C6C;">卖出手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="match_sell_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['match_sell_fee'])){{$setting['match_sell_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label"  style="color:#F56C6C;">买入手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="match_buy_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['match_buy_fee'])){{$setting['match_buy_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
    </div>
</fieldset>

<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-dollar"></i>
        <span>秒合约交易</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label" style="color:#F56C6C;">手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="option_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['option_fee'])){{$setting['option_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
    </div>
</fieldset>

<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-dollar"></i>
        <span>提现手续费</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label" style="color:#F56C6C;">手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="withdraw_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['withdraw_fee'])){{$setting['withdraw_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
    </div>
</fieldset>

<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-dollar"></i>
        <span>交易手续费邀请返佣</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label" style="color:#F56C6C;">返佣比例</label>
            <div class="layui-input-inline">
                <input type="text" name="invite_ratio" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['invite_ratio'])){{$setting['invite_ratio']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
    </div>
</fieldset>

<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-dollar"></i>
        <span>贷款手续费</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label" style="color:#F56C6C;">手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="loan_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['loan_fee'])){{$setting['loan_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
    </div>
</fieldset>


<fieldset class="layui-elem-field" style="display: none;">
    <legend>
        <i class="layui-icon layui-icon-rmb"></i>
        <span>法币交易</span>
    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <label class="layui-form-label">卖出手续费</label>
            <div class="layui-input-inline">
                <input type="text" name="legal_sell_fee" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['legal_sell_fee'])){{$setting['legal_sell_fee']}}@endif">
            </div>
            <div class="layui-form-mid layui-word-aux">%</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">未支付超过</label>
            <div class="layui-input-inline">
                <input type="text" name="legal_timeout" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['legal_timeout'])){{$setting['legal_timeout']}}@endif">
            </div><div class="layui-form-mid layui-word-aux">分钟,未支付超时将自动取消交易</div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">未确认超过</label>
            <div class="layui-input-inline">
                <input type="text" name="legal_confirm_timeout" autocomplete="off" class="layui-input"
                       value="@if(isset($setting['legal_confirm_timeout'])){{$setting['legal_confirm_timeout']}}@endif">
            </div><div class="layui-form-mid layui-word-aux">分钟,买方支付后卖方超时未确认将自动确认</div>
        </div>
    </div>
</fieldset>

<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-release"></i>
        <span>杠杆交易</span>
        <button class="layui-btn layui-btn-sm layui-btn-warm" type="button" id="currency_set">币种管理</button>
    </legend>
    <div class="layui-field-box">
        @include('admin.setting.lever')
        <div class="layui-form-item">
            <label class="layui-form-label"></label>
            <div class="layui-inline">
                <h5 style="color: #aba8a8;">其它请在币种管理,找到对应法币在交易对中进行设置</h5>
                <div class="layui-form-mid layui-word-aux"></div>
            </div>
        </div>
</fieldset>
<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon layui-icon-release"></i>
        <span>秒合约交易</span>
    </legend>
    <div class="layui-field-box">
        @include('admin.setting.micro')
    </div>
</fieldset>