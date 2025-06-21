<div class="layui-form-item">
    <label class="layui-form-label">是否开启充提币功能</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="is_open_CTbi" value="1" title="打开" @if (isset($setting['is_open_CTbi'])) {{$setting['is_open_CTbi'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="is_open_CTbi" value="0" title="关闭" @if (isset($setting['is_open_CTbi'])) {{$setting['is_open_CTbi'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">是否开启（充值、提款、交易）需实名认证功能</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="is_open_transaction" value="1" title="打开" @if (isset($setting['is_open_transaction'])) {{$setting['is_open_transaction'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="is_open_transaction" value="0" title="关闭" @if (isset($setting['is_open_transaction'])) {{$setting['is_open_transaction'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>
<div class="layui-form-item">
    <label class="layui-form-label">是否开启机器人消息推送</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="is_open_robot" value="1" title="打开" @if (isset($setting['is_open_robot'])) {{$setting['is_open_robot'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="is_open_robot" value="0" title="关闭" @if (isset($setting['is_open_robot'])) {{$setting['is_open_robot'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>
<div class="layui-form-item" style="display:none">
    <label class="layui-form-label">提币使用链上接口</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="use_chain_api" value="1" title="打开" @if (isset($setting['use_chain_api'])) {{$setting['use_chain_api'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="use_chain_api" value="0" title="关闭" @if (isset($setting['use_chain_api'])) {{$setting['use_chain_api'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>
<!--<div class="layui-form-item">
    <label class="layui-form-label">充币账户</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <select name="recharge_to_balance" {{$setting['recharge_to_balance'] > 0 ? 'disabled' : ''}}>
                <option value="1" {{$setting['recharge_to_balance'] == 1 ? 'selected' : ''}}>法币资产</option>
                <option value="2" {{$setting['recharge_to_balance'] == 2 ? 'selected' : ''}}>币币资产</option>
                <option value="3" {{$setting['recharge_to_balance'] == 3 ? 'selected' : ''}}>杠杆资产</option>
            </select>
        </div>
    </div>
</div>-->
<div class="layui-form-item" style="display:none">
    <label class="layui-form-label">充币账户</label>
    <div class="layui-input-block">
        <div class="layui-input-inline" style="width: 400px">
            <input type="text" class="layui-input" name="recharge_to_balance" value="{{$setting['recharge_to_balance'] ?? ''}}" placeholder="请输入充币账户" >
        </div>
    </div>
</div>

<div class="layui-form-item" style="display:none">
    <label class="layui-form-label">银行账户名</label>
    <div class="layui-input-block">
        <div class="layui-input-inline" style="width: 400px">
            <input type="text" class="layui-input" name="bank_realname" value="{{$setting['bank_realname'] ?? ''}}" placeholder="请输入银行卡人姓名" >
        </div>
    </div>
</div>

<div class="layui-form-item" style="display:none">
    <label class="layui-form-label">银行及支行</label>
    <div class="layui-input-block">
        <div class="layui-input-inline" style="width: 400px">
            <input type="text" class="layui-input" name="bank_name" value="{{$setting['bank_name'] ?? ''}}" placeholder="请输入银行以及支行" >
        </div>
    </div>
</div>

<div class="layui-form-item" style="display:none">
    <label class="layui-form-label">银行卡号</label>
    <div class="layui-input-block">
        <div class="layui-input-inline" style="width: 400px">
            <input type="number" class="layui-input" name="bank_card_number" value="{{$setting['bank_card_number'] ?? ''}}" placeholder="请输入银行卡号" >
        </div>
    </div>
</div>
<!--<div class="layui-form-item">-->
<!--    <label class="layui-form-label">提币账户</label>-->
<!--    <div class="layui-input-block">-->
<!--        <div class="layui-input-inline">-->
<!--            <select name="withdraw_from_balance" {{$setting['withdraw_from_balance'] > 0 ? 'disabled' : ''}}>-->
<!--                <option value="1" {{$setting['withdraw_from_balance'] == 1 ? 'selected' : ''}}>法币资产</option>-->
<!--                <option value="2" {{$setting['withdraw_from_balance'] == 2 ? 'selected' : ''}}>币币资产</option>-->
<!--                <option value="3" {{$setting['withdraw_from_balance'] == 3 ? 'selected' : ''}}>杠杆资产</option>-->
<!--            </select>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->

<div class="layui-form-item layui-form-text">
	<label class="layui-form-label">背景图片</label>
	<div class="layui-input-block">
		<button class="layui-btn" type="button" id="backgroud_image">选择图片</button>
		<br>
		<img src="{{$setting['backgroud_image'] ?? ''}}" id="img_cover" class="cover" style="display: @if(!empty($setting['backgroud_image'])){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
		<input type="hidden" name="backgroud_image" id="cover" value="{{$setting['backgroud_image'] ?? ''}}">
	</div>
</div>

<div class="layui-form-item layui-form-text">
	<label class="layui-form-label">登录logo</label>
	<div class="layui-input-block">
		<button class="layui-btn" type="button" id="logo1">手机端登录logo</button>
		<br>
		<img src="{{$setting['logo1'] ?? ''}}" id="img_sign" class="sign" style="display: @if(!empty($setting['logo1'])){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
		<input type="hidden" name="logo1" id="sign" value="{{$setting['logo1'] ?? ''}}">
		<div class="layui-form-mid layui-word-aux">建议尺寸:240*240</div>
	</div>
</div>

<div class="layui-form-item layui-form-text">
    <label class="layui-form-label">首页logo</label>
    <div class="layui-input-block">
        <button class="layui-btn" type="button" id="logo2">PC端顶部logo</button>
        <br>
        <img src="{{$setting['logo2'] ?? ''}}" id="img_sign1" class="sign1" style="display: @if(!empty($setting['logo2'])){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
        <input type="hidden" name="logo2" id="sign1" value="{{$setting['logo2'] ?? ''}}">
		<div class="layui-form-mid layui-word-aux">建议尺寸:128*48</div>
    </div>
</div>