<fieldset class="layui-elem-field">
    <legend>
        <i class="layui-icon"></i>
        <span style="font-weight: 700">货币汇率</span>

    </legend>
    <div class="layui-field-box">
        <div class="layui-form-item">
            <div class="layui-inline" hidden>
                <label class="layui-form-label"></label>
                <div class="layui-input-inline">
                    <input class="layui-input" lay-verify="required" placeholder="用户名" name="smsBao_username" type="text" value="{{$setting['smsBao_username'] ?? '' }}" autocomplete="off">
                    <span style="position: absolute;right: 5px;top: 12px;"></span>
                </div>
            </div>
            <div class="layui-inline" hidden>
                <label class="layui-form-label">密码</label>
                <div class="layui-input-inline">
                    <input class="layui-input" type="password" lay-verify="required" name="password"
                        value="{{$setting['password']  ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
            <div class="layui-inline" hidden>
                <label class="layui-form-label">短信签名</label>
                <div class="layui-input-inline">
                    <input class="layui-input" lay-verify="required" name="sms_signature"
                        value="{{$setting['sms_signature'] ?? '' }}" placeholder="">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">泰铢:USDT</label>
                <div class="layui-input-inline">
                    <input class="layui-input" lay-verify="required" name="thb"  type="number"
                           value="{{$setting['thb'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">越南盾:USDT</label>
                <div class="layui-input-inline" style="width: 240px">
                    <input class="layui-input" type="number" lay-verify="required" name="vnd" value="{{$setting['vnd'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">印尼卢比:USDT</label>
                <div class="layui-input-inline">
                    <input class="layui-input"  type="number"
                           lay-verify="required" name="idr" value="{{$setting['idr'] ?? '' }}" placeholder="" autocomplete="off">
                </div> 
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">人民币:USDT</label>
                <div class="layui-input-inline" style="width: 240px">
                    <input class="layui-input" type="number" lay-verify="required" name="rmb" value="{{$setting['rmb'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">韩元:USDT</label>
                <div class="layui-input-inline">
                    <input class="layui-input"  type="number"
                           lay-verify="required" name="krw" value="{{$setting['krw'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
            <div class="layui-inline">

            </div>
        </div>
        
        
         
        
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">美元:USDT</label>
                <div class="layui-input-inline">
                    <input class="layui-input"  type="number"
                           lay-verify="required" name="usd" value="{{$setting['usd'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">欧元:USDT</label>
               <div class="layui-input-inline" style="width: 240px">
                    <input class="layui-input"  type="number"
                           lay-verify="required" name="eur" value="{{$setting['eur'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">英镑:USDT</label>
                <div class="layui-input-inline">
                    <input class="layui-input"  type="number"
                           lay-verify="required" name="gbp" value="{{$setting['gbp'] ?? '' }}" placeholder="" autocomplete="off">
                </div>
            </div>
            <div class="layui-inline">

            </div>
        </div>
        
        
    </div>
</fieldset>
