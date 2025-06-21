@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<style>
    .bank .layui-form-label{width:100px;}
    .bank .layui-input-block{margin-left:130px;}
     .layui-table-cell {
        height: 30px;
    }
    .wallet-address .layui-form-label{width:130px;}
    .wallet-address .layui-input-block{margin-left:160px;}
</style>
<form class="layui-form" action="">
    <div class="layui-tab">
        <ul class="layui-tab-title">
            <li class="layui-this">基础信息</li>
            <li>用户资料</li>
            <li>充值地址</li>
            <li>收款信息</li>
           
        </ul>
        <div class="layui-tab-content">
            <div class="layui-tab-item layui-show">
                <div class="layui-form-item">
                    <label class="layui-form-label">账号</label>
                    <div class="layui-input-block">
                        <input type="text" name="account_number" autocomplete="off" placeholder="" class="layui-input" value="{{$result->account_number}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">手机号</label>
                    <div class="layui-input-block">
                        <input type="text" name="phone" autocomplete="off" placeholder="" class="layui-input" value="{{$result->phone}}">
                    </div>
                </div>
                 @if(strpos($authorityList,"91031")>0)
                 <div class="layui-form-item">
                    <label class="layui-form-label">邮箱</label>
                    <div class="layui-input-block">
                        <input type="text" name="email" autocomplete="off" placeholder="" class="layui-input" value="{{$result->email}}">
                    </div>
                </div>
                 @endif
                <div class="layui-form-item">
                    <label class="layui-form-label">密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="password" autocomplete="off" placeholder="" class="layui-input" value="">
                    </div>
                </div>

                <div class="layui-form-item">
                    <label class="layui-form-label">交易密码</label>
                    <div class="layui-input-block">
                        <input type="text" name="pay_password" autocomplete="off" placeholder="" class="layui-input" value="">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">信用分</label>
                    <div class="layui-input-block">
                        <input type="text" name="credit_score" autocomplete="off" placeholder="" class="layui-input" value="{{$result->credit_score}}">
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">风控比例</label>
                    <div class="layui-input-block">
                        <input type="text" name="subcontrol_ratio" autocomplete="off" placeholder="" class="layui-input" value="{{$result->subcontrol_ratio}}">
                    </div>
                </div>
                
                <div class="layui-form-item">
                    <label class="layui-form-label">备注</label>
                    <div class="layui-input-block">
                        <input type="text" name="remark" autocomplete="off" placeholder="" class="layui-input" value="{{$result->remark}}">
                    </div>
                </div>
                 <div class="layui-form-item">
                    <label class="layui-form-label">所属代理</label>
                    <div class="layui-input-block">
                        <select name="agent_note_id" lay-filter="" lay-search>
                            <option value=""></option>
                            @if(!empty($agent_list))
                            @foreach($agent_list as $agent)
                            <option value="{{$agent->id}}" @if($agent->id == $result->agent_note_id) selected @endif>{{$agent->username}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item">
                <div class="layui-form-item">
                    <label class="layui-form-label">真实姓名</label>
                    <div class="layui-input-block">
                        <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="{{$result->name ?? ''}}">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">身份证号</label>
                    <div class="layui-input-block">
                        <input type="text" name="card_id" autocomplete="off" placeholder="" class="layui-input" value="{{$result->card_id ?? ''}}" @if(empty($result->card_id)) disabled @endif>
                    </div>
                </div>
            </div>
            <div class="layui-tab-item wallet-address">
                 <div class="layui-form-item">
                    <label class="layui-form-label">分组选择</label>
                    <div class="layui-input-block">
                        <select name="wallet_address_id" lay-filter="" lay-search>
                            <option value=""></option>
                            @if(!empty($wallet_address_list))
                            @foreach($wallet_address_list as $wallet_address)
                            <option value="{{$wallet_address->id}}" @if($wallet_address->id == $result->wallet_address_id) selected @endif>{{$wallet_address->name}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <!--<div class="layui-form-item">-->
                <!--    <label class="layui-form-label">USDT(TRC)：地址</label>-->
                <!--    <div class="layui-input-block">111</div>-->
                <!--</div>-->
                <!--<div class="layui-form-item">-->
                <!--    <label class="layui-form-label">USDT(ERC)：地址</label>-->
                <!--    <div class="layui-input-block">222</div>-->
                <!--</div>-->
                <!-- <div class="layui-form-item">-->
                <!--    <label class="layui-form-label">ETH：地址</label>-->
                <!--    <div class="layui-input-block">333</div>-->
                <!--</div>-->
                <!-- <div class="layui-form-item">-->
                <!--    <label class="layui-form-label">BTC：地址</label>-->
                <!--    <div class="layui-input-block">444</div>-->
                <!--</div>-->
                <!-- <div class="layui-form-item">-->
                <!--    <label class="layui-form-label">USDC：地址</label>-->
                <!--    <div class="layui-input-block">555</div>-->
                <!--</div>-->
            </div>
            <div class="layui-tab-item">
                <div class="layui-collapse">
                    <div class="layui-colla-item" style="display: none;">
                        <h2 class="layui-colla-title">微信</h2>
                        <div class="layui-colla-content">
                            <div class="layui-form-item">
                                <label class="layui-form-label">微信昵称</label>
                                <div class="layui-input-block">
                                    <input type="text" name="wechat_nickname" autocomplete="off" placeholder="" class="layui-input" value="{{$cashinfo->wechat_nickname}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">微信账号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="wechat_account" autocomplete="off" placeholder="" class="layui-input" value="{{$cashinfo->wechat_account}}">
                                </div>
                            </div>
                            <div class="layui-form-item layui-form-text">
                                <label class="layui-form-label">微信收款码</label>
                                <div class="layui-input-block">
                                    <button class="layui-btn upload_btn" type="button">选择图片</button>
                                    <br>
                                    <img src="@if(!empty($cashinfo->wechat_collect)){{$cashinfo->wechat_collect}}@endif" class="thumbnail" style="display: @if(!empty($cashinfo->wechat_collect)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                                    <input type="hidden" class="thumbnail_input" name="wechat_collect" id="wechat_collect" value="@if(!empty($cashinfo->wechat_collect)){{$cashinfo->wechat_collect}}@endif">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-colla-item" style="display: none;">
                        <h2 class="layui-colla-title">支付宝</h2>
                        <div class="layui-colla-content">
                            <div class="layui-form-item">
                                <label class="layui-form-label">支付宝账号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="alipay_account" autocomplete="off" placeholder="" class="layui-input" value="{{$cashinfo->alipay_account}}">
                                </div>
                            </div>
                            <div class="layui-form-item layui-form-text">
                                <label class="layui-form-label">支付宝收款码</label>
                                <div class="layui-input-block">
                                    <button class="layui-btn upload_btn" type="button">选择图片</button>
                                    <br>
                                    <img src="@if(!empty($cashinfo->alipay_collect)){{$cashinfo->alipay_collect}}@endif" class="thumbnail" style="display: @if(!empty($cashinfo->alipay_collect)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                                    <input type="hidden" class="thumbnail_input" name="alipay_collect" id="alipay_collect" value="@if(!empty($cashinfo->alipay_collect)){{$cashinfo->alipay_collect}}@endif">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-colla-item bank">
                        <h2 class="layui-colla-title">银行卡</h2>
                        <div class="layui-colla-content">
                            <div class="layui-form-item">
                                <label class="layui-form-label">姓名</label>
                                <div class="layui-input-block">
                                    <input type="text" name="truename" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->truename}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">银行账号</label>
                                <div class="layui-input-block">
                                    <input type="text" name="bank_account" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->bankcardno}}">
                                </div>
                            </div>
                           <div class="layui-form-item">
                                <label class="layui-form-label">开户行</label>
                                <div class="layui-input-block">
                                    <input type="text" name="bank_name" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->bankname}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">开户省市</label>
                                <div class="layui-input-block">
                                    <input type="text" name="provincecity" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->provincecity}}">
                                </div>
                            </div>

                             <div class="layui-form-item">
                                <label class="layui-form-label">开户网点</label>
                                <div class="layui-input-block">
                                    <input type="text" name="store" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->store}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">身份证ID(护照)</label>
                                <div class="layui-input-block">
                                    <input type="text" name="idcard" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->idcard}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">国际汇款代码</label>
                                <div class="layui-input-block">
                                    <input type="text" name="international_code" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->international_code}}">
                                </div>
                            </div>
                            <div class="layui-form-item">
                                <label class="layui-form-label">联系电话</label>
                                <div class="layui-input-block">
                                    <input type="text" name="link_mp" autocomplete="off" placeholder="" class="layui-input" value="{{$bankPO->link_mp}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                 <table id="addresslist" lay-filter="addresslist"></table>
                 @if(!empty($address))
                <div>
                     @foreach($address as $key => $value)
                      <div class="layui-form-item layui-inline layui-form-text">
                        <label class="layui-form-label">币种</label>
                        <div class="layui-input-block">{{$value->coin}}</div>
                    </div>
                     <div class="layui-form-item layui-inline">
                        <label class="layui-form-label">钱包二维码</label>
                        <div class="layui-input-block">
                            <img src="@if(!empty($value->upload_pic)){{$value->upload_pic}}@endif" class="thumbnail" style="display: @if(!empty($value->upload_pic)){{"block"}}@else{{"none"}}@endif;max-width: 30px;height: 30px;margin-top: 5px;">
                        </div>
                    </div>
                    <div class="layui-form-item layui-inline">
                        <label class="layui-form-label">钱包地址</label>
                        <div class="layui-input-block">{{$value->address}}</div>
                    </div>
                      @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    <input type="hidden" name="id" value="{{$result->id}}">
    <div class="layui-form-item">
        <div class="layui-input-block">
            <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
            <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
    </div>
</form>

@endsection
@section('scripts')
<script type="text/html" id="barDemo">
<a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
</script>
<script>
    layui.use(['element', 'form', 'laydate', 'upload','table'], function() {
        var form = layui.form,
            $ = layui.jquery,
            laydate = layui.laydate,
            table = layui.table,
            upload = layui.upload,
            element = layui.element
        var index = parent.layer.getFrameIndex(window.name)
            ,uploadInst = upload.render({
                elem: '.upload_btn' //绑定元素
                ,url: '/api/upload' //上传接口
                ,done: function(res, index, upload) {
                    //上传完毕回调
                    if (res.type == "ok") {
                        var img = $(this.item).nextAll('img.thumbnail')
                        img.next(".thumbnail_input").val(res.message);
                        img.attr("src",res.message).show();
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            }); 
        //监听提交
        form.on('submit(demo1)', function(data) {
            var data = data.field;
            $.ajax({
                url: '/admin/user/edit',
                type: 'post',
                dataType: 'json',
                data: data,
                success: function(res) {
                    if (res.type == 'error') {
                        layer.msg(res.message);
                    } else {
                        parent.layer.close(index);
                        parent.window.location.reload();
                    }
                }
            });
            return false;
        });
        
         //监听工具条
            table.on('tool(addresslist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data;
                var layEvent = obj.event;
                if(layEvent === 'edit'){ //编辑
                    layer_show('编辑钱包地址',"{{url('admin/user/edit_address')}}?id="+data.id);
                }
            })
        
        function tbRend(url) {
            table.render({
                elem: '#addresslist'
                ,toolbar: false
                ,url: url + "?id=" +"{{$result->id}}"
                ,page: true
                ,limit: 12
                ,height: 300
                ,cols: [[
                    {field: 'id', title: '钱包ID', width: 80}
                    ,{field:'coin', title:'币种名称', width: 150}
                    ,{field:'upload_pic', title:'钱包二维码', width: 150,templet: function(d){
                        return '<img src="' + d.upload_pic + '" alt="钱包二维码" width="30" height="30" class="img-click">';
                    }}
                    ,{field:'address', title:'钱包地址', width: 253}
                    ,{fixed: 'right', title: '操作', width: 120, align: 'center', toolbar: '#barDemo'}
                ]]
            });
        }
        tbRend("{{url('/admin/user/address_list')}}");
        
        // 图片点击事件
          $(document).on('click', '.img-click', function(){
            var imgSrc = $(this).attr('src');
            layer.open({
              type: 1,
              title: false,
              closeBtn: 0,
              area: 'auto',
              skin: 'layui-layer-nobg', // 没有背景色的class
              shadeClose: true,
              content: '<img src="'+ imgSrc +'" alt="图片" style="margin: auto; max-width: 100%; max-height: 100%;">'
            });
          });
    });
</script>

@endsection