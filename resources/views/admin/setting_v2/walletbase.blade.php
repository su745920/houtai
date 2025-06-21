@extends('admin._layoutNew')
@section('page-head')
    <style>
        [hidden] {
            display: none;
        }
        .layui-form-label {
            width: 150px;
        }
        .layui-input,.layui-input:hover{background-color: #fff!important;}
    </style>
@stop
@section('page-content')
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-tab">
                <ul class="layui-tab-title">
                  
                    <!--<li class="layui-this"></li>-->
                    
                   
                   
                
                </ul>
                <div class="layui-tab-content">
                    <!--通知设置开始-->
                   
                    <!--基础设置开始-->
                    <div class="layui-tab-item layui-show"> 
                        @include('admin.setting_v2.walletcommon')
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">
        $("input").attr("disabled",true);
        $(".wallet_setting_edit").click(function(){
            let that = this
            layer.prompt({title: '谷歌验证', formType: 1}, function(google_code, index){
                layer.close(index);
                $.ajax({
                        url:'/admin/google_auth/checkGoogle',
                        type:'post',
                        dataType:'json',
                        data:{google_code},
                        success:function(res){
                            layer.msg(res.message);
                            if(res.type=='ok'){
                                var id = $(that).attr('data-form');
                                $(that).hide();
                                $(that).siblings().show();
                                $("#" + id).find("input").attr("disabled",false);
                                $("#" + id).find(".layui-form-switch").removeClass('layui-checkbox-disbaled');
                                $("#" + id).find(".layui-form-switch").removeClass('layui-disabled');
                            }
                        }
                    });
              });
            
            // var id = $(this).attr('data-form');
            // $(this).hide();
            // $(this).siblings().show();
            // $("#" + id).find("input").attr("disabled",false);
            // $("#" + id).find(".layui-form-switch").removeClass('layui-checkbox-disbaled');
            // $("#" + id).find(".layui-form-switch").removeClass('layui-disabled');
        })
        $(".wallet_setting_cancel").click(function(){
            var id = $(this).attr('data-form');
            $(this).hide();
            $("#" + id).find(".wallet_setting_submit").hide();
            $("#" + id).find(".wallet_setting_edit").show();    
            $("#" + id).find("input").attr("disabled",true);
            $("#" + id).find(".layui-form-switch").addClass('layui-checkbox-disbaled');
            $("#" + id).find(".layui-form-switch").addClass('layui-disabled'); 
        })
        layui.use(['element', 'form', 'upload', 'layer'], function () {
            var element = layui.element;
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
    		var upload = layui.upload;

            var submit_list = ['usdt_trc','usdt_erc','usdc','eth','bit','usdt_trc_repayment','usdt_erc_repayment','usdc_repayment','eth_repayment','bit_repayment','vi','china_eft','th','id','kor'];
                submit_list.forEach(function(e){
                    form.on('submit('+ e +')', function (data) {
                    var data = data.field;
                    data.data_type = e
                    layer.confirm('确定提交钱包设置？', function (index) {
                        $.ajax({
                            url: '/admin/setting/save_v2',
                            type: 'post',
                            dataType: 'json',
                            data: data,
                            success: function (res) {
                                layer.msg(res.message);
                                $("#wallet_setting_" + e).find(".wallet_setting_submit").hide();
                                $("#wallet_setting_" + e).find(".wallet_setting_cancel").hide();
                                $("#wallet_setting_" + e).find(".wallet_setting_edit").show();
                                $("#wallet_setting_" + e).find("input").attr("disabled",true);
                                $("#wallet_setting_" + e).find(".layui-form-switch").addClass('layui-checkbox-disbaled');
                                $("#wallet_setting_" + e).find(".layui-form-switch").addClass('layui-disabled');     
                            }
                        });
                    });
                    return false;
                });
            });
            
        });
    </script>
@stop