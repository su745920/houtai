@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
<form class="layui-form" method="POST">
        <div class="layui-inline">
                <label class="layui-form-label">币种</label>
                <div class="layui-input-inline">
                   <!--  <input class="layui-input" lay-verify="required" placeholder="请输入提币地址" name="address" type="text"  value="{{$results->currency_name}}" disabled> -->
                 <span class="layui-form-label ">{{$results->currency_name}}</span>
                   @if(empty($list))
               
                <span class="layui-form-label ">暂时还没有提币地址</span>
                @endif
                </div>
                

         </div>
         <input type="hidden" name="user_id" value="{{$results->user_id}}">
         <input type="hidden" name="currency" value="{{$results->currency}}">


        
        <div id="full">
          
            <div >
               @if(!empty($list))
                @foreach($list as $key => $value)
                <div class="layui-form-item bonus" >

                    <input type="hidden" name="id" value="{{$value->id}}">

                    <div class="layui-inline">
                        <label class="layui-form-label">提币地址</label>
                        <div class="layui-input-inline">
                            <input class="layui-input" lay-verify="required" placeholder="请输入提币地址" name="address" type="text" readonly  value="{{$value->address}}">
                        </div>
                    </div>

                    <div class="layui-inline">
                        <label class="layui-form-label">链</label>
                        <div class="layui-input-inline">
                            <input class="layui-input" lay-verify="required" placeholder="请输入提币地址" name="lianTypeZH" type="text" readonly  value="{{$value->lianTypeZH}}">
                        </div>
                    </div>

                   <div class="layui-inline">
                        <label class="layui-form-label">二维码</label>
                        <div class="layui-input-inline">
                            <image src="{{$value->upload_pic}}" style="width: 30px;height: 30px;"  onclick="window.open('{{$value->upload_pic}}')"></image>
                        </div>
                    </div>
                    
                    <div class="layui-inline">
                        <button type="button" class="layui-btn delete"  onclick="delFn({{$value->id}});">删除</button>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
            
        </div>
   <!--  <div class="layui-form-item">
        <div class="layui-input-block">
            <button type="button" class="layui-btn" id="add_full">添加</button>
        </div>
    </div> -->
        

   
       <!--
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" id="btn1" type="button" >立即提交</button>
                 <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
        -->
</form>
@stop
@section('scripts')
    <script src="/admin/plugins/layui/layui.js"></script>
    <script type="text/javascript">

        function delFn(id){
            $.ajax({
                url:'/admin/account/delWithdrawAddress01',
                type:"post",
                dataType:"json",
                data:{
                    id:id,

                },
                success:function(data){
                    layer.msg(data.message);
                    if(data.type == 'ok'){
                        location.reload();
                    }
                }
            });
        }
        layui.use(['layer'], function() {
            });
        var html = '';
            html += '<div class="layui-form-item bonus" > ';
            html += '<div class="layui-inline"><label class="layui-form-label">提币地址</label><div class="layui-input-inline"><input class="layui-input" lay-verify="required" placeholder="请输入提币地址" name="address" type="text"  value=""></div></div>'
            html += '<div class="layui-inline"><label class="layui-form-label">备注</label><div class="layui-input-inline"><input class="layui-input" lay-verify="" placeholder="请备注" name="notes" type="text"  value=""></div></div>'
            html += '<div class="layui-inline"><button type="button" class="layui-btn delete"  id="del">删除</button></div></div>';
        $("#add_full").click(function(){
            $("#full >div").append(html);
        });
        $("#del").on("click",".delete",function(){


             
            
        });
      
        
        $("#btn1").click(function(){
             var total_arr = [],is_go=true;
             $(".bonus").each(function(i){
                 var num1 = $(this).find("input[name='address']").val();
                 var num2 = $(this).find("input[name='notes']").val();
                 
                 if(num1 == ''){
                     is_go = false;
                     layer.msg('提币地址不能为空');
                 }
                 
                
                 total_arr.push({"address":num1,"notes":num2});
             });
             if(!is_go){return false;}
            // console.log(total_arr);
            var user_id= $("input[name='user_id']").val();
            var currency= $("input[name='currency']").val();
            console.log(user_id);console.log(currency);
             $.ajax({
                  url:'/admin/user/address_edit',
                  type:"post",
                  dataType:"json",
                  data:{
                      user_id:user_id,
                      currency:currency,
                      total_arr:total_arr,

                  },
                  success:function(data){
                      layer.msg(data.message);
                      if(data.type == 'ok'){
                        var index = parent.layer.getFrameIndex(window.name);
                          parent.layer.close(index);
                            parent.window.location.reload();
                      }
                  }
             });
        })


    </script>
@stop