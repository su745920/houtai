@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<style>
    .layui-form-label{width:140px;}
    .layui-input-block{margin-left:170px;}
</style>
    <form class="layui-form" action="">
        <!-- <div class="layui-form-item">-->
        <!--    <label class="layui-form-label">币种选择</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <select name="name" lay-filter="">-->
        <!--            <option value=""></option>-->
        <!--            <option value="USDT(TRC)" @if($result->name == 'USDT(TRC)') selected @endif>USDT(TRC)</option>-->
        <!--            <option value="USDT(ERC)" @if($result->name == 'USDT(ERC)') selected @endif>USDT(ERC)</option>-->
        <!--            <option value="ETH" @if($result->name == 'ETH') selected @endif>ETH</option>-->
        <!--            <option value="BTC" @if($result->name == 'BTC') selected @endif>BTC</option>-->
        <!--            <option value="USDC" @if($result->name == 'USDC') selected @endif>USDC</option>-->
        <!--        </select>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="layui-form-item">
            <label class="layui-form-label">分组名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->name)){{$result->name}}@endif"/>
            </div>
        </div>
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">凭证开关:</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <select name="is_voucher" lay-filter="">-->
        <!--            <option value="1" @if($result->is_voucher == 1) selected @endif>开启</option>-->
        <!--            <option value="0" @if($result->is_voucher == 0) selected @endif>关闭</option>-->
        <!--        </select>-->
        <!--    </div>-->
        <!--</div>-->
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">是否显示</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <select name="is_show" lay-filter="">-->
        <!--            <option value="1" @if($result->is_show == 1) selected @endif>是</option>-->
        <!--            <option value="0" @if($result->is_show == 0) selected @endif>否</option>-->
        <!--        </select>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="layui-form-item">
            <label class="layui-form-label">是否默认</label>
            <div class="layui-input-block">
                <select name="is_default" lay-filter="">
                    <option value="1" @if($result->is_default == 1) selected @endif>是</option>
                    <option value="0" @if($result->is_default == 0) selected @endif>否</option>
                </select>
            </div>
        </div>
        
        
        
        <script type="text/html" id="barWallet">
            <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
            <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
        </script>
        
        <!--<div class="layui-tab layui-tab-brief">-->
        <!--  <ul class="layui-tab-title">-->
        <!--    <li class="layui-this">USDT(TRC)</li>-->
        <!--    <li>USDT(ERC)</li>-->
        <!--    <li>ETH</li>-->
        <!--    <li>BTC</li>-->
        <!--    <li>USDC</li>-->
        <!--  </ul>-->
        <!--  <div class="layui-tab-content">-->
        <!--    <div class="layui-tab-item layui-show">-->
        <!--        <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">地址</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="text" name="usdt_trc_address" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdt_trc_address)){{$result->usdt_trc_address}}@endif"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--         <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">最小额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="usdt_trc_min_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdt_trc_min_limit)){{$result->usdt_trc_min_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="layui-form-item" style="width: 100%;">-->
        <!--            <label class="layui-form-label">最大额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="usdt_trc_max_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdt_trc_max_limit)){{$result->usdt_trc_max_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--    <div class="layui-tab-item">-->
        <!--        <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">地址</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="text" name="usdt_erc_address" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdt_erc_address)){{$result->usdt_erc_address}}@endif"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--         <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">最小额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="usdt_erc_min_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdt_erc_min_limit)){{$result->usdt_erc_min_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="layui-form-item" style="width: 100%;">-->
        <!--            <label class="layui-form-label">最大额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="usdt_erc_max_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdt_erc_max_limit)){{$result->usdt_erc_max_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--    <div class="layui-tab-item">-->
        <!--        <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">地址</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="text" name="eth_address" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->eth_address)){{$result->eth_address}}@endif"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--         <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">最小额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="eth_min_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->eth_min_limit)){{$result->eth_min_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="layui-form-item" style="width: 100%;">-->
        <!--            <label class="layui-form-label">最大额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="eth_max_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->eth_max_limit)){{$result->eth_max_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--    <div class="layui-tab-item">-->
        <!--        <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">地址</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="text" name="btc_address" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->btc_address)){{$result->btc_address}}@endif"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--         <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">最小额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="btc_min_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->btc_min_limit)){{$result->btc_min_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="layui-form-item" style="width: 100%;">-->
        <!--            <label class="layui-form-label">最大额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="btc_max_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->btc_max_limit)){{$result->btc_max_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--    <div class="layui-tab-item">-->
        <!--        <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">地址</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="text" name="usdc_address" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdc_address)){{$result->usdc_address}}@endif"/>-->
        <!--            </div>-->
        <!--        </div>-->
        <!--         <div class="layui-form-item">-->
        <!--            <label class="layui-form-label">最小额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="usdc_min_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdc_min_limit)){{$result->usdc_min_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--        <div class="layui-form-item" style="width: 100%;">-->
        <!--            <label class="layui-form-label">最大额度</label>-->
        <!--            <div class="layui-input-block">-->
        <!--                <input type="number" name="usdc_max_limit" lay-verify="required" autocomplete="off" placeholder="请输入" class="layui-input" value="@if(!empty($result->usdc_max_limit)){{$result->usdc_max_limit}}@endif">-->
        <!--            </div>-->
        <!--        </div>-->
        <!--    </div>-->
        <!--  </div>-->
        <!--</div>-->
        
       

        <input type="hidden" name="id" value="@if(!empty($result->id)){{$result->id}}@endif">
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
    @if($result->id)
     <div style="text-align: right;">
        <button class="layui-btn" onclick="onAdd()">新增</button>
    </div>
    <table id="walletTable" lay-filter="test"></table>
    @endif
@endsection

@section('scripts')
    <script>
        layui.use(['element','table','form','laydate'],function () {
            var form = layui.form
                ,table = layui.table
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,element = layui.element
                ,index = parent.layer.getFrameIndex(window.name);
                
                table.render({
                    elem: '#walletTable'
                    ,url: '{{url('admin/wallet_address_list/lists')}}' //数据接口
                    ,page: true //开启分页
                    ,id:'mobileSearch'
                    ,cols: [[ //表头
                        {field: 'id', title: 'ID', Width:50, sort: true}
                        ,{field: 'currency_name', title: '币种名称', Width:150}
                        ,{field: 'address', title: '地址', minWidth:250}
                        ,{field: 'min_limit', title: '最小额度', minWidth:50}
                        ,{field: 'max_limit', title: '最大额度', minWidth:50}
                        ,{field:'is_voucher', title:'凭证开关', minWidth:100, templet: '#switchVoucherTpl', unresize: true}
                        ,{field:'is_show', title:'是否显示', minWidth:100, templet: '#switchShowTpl', unresize: true}
                        ,{title:'操作',toolbar: '#barWallet'}
    
                    ]]
                });
                
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'{{url('admin/wallet_address/postAdd')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
         function onAdd() {
            layer_show('添加币种','{{url('admin/wallet_address_list/add')}}?wallet_address_id='+"{{$result->id}}",800)
        }
    </script>

@endsection