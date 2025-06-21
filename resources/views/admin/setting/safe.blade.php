<div class="layui-form-item">
    <label class="layui-form-label">手机号必填</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="mobile_must" value="1" title="是" @if (isset($setting['mobile_must'])) {{$setting['mobile_must'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="mobile_must" value="0" title="否" @if (isset($setting['mobile_must'])) {{$setting['mobile_must'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>

<div class="layui-form-item">
    <label class="layui-form-label">邀请码必填</label>
    <div class="layui-input-block">
        <div class="layui-input-inline">
            <input type="radio" name="invite_code_must" value="1" title="是" @if (isset($setting['invite_code_must'])) {{$setting['invite_code_must'] == 1 ? 'checked' : ''}} @endif >
            <input type="radio" name="invite_code_must" value="0" title="否" @if (isset($setting['invite_code_must'])) {{$setting['invite_code_must'] == 0 ? 'checked' : ''}} @else checked @endif >
        </div>
    </div>
</div>