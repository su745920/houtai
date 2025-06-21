<?php

namespace App\Http\Controllers\Admin;

use App\Models\WalletAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletAddressController extends Controller
{
    public function index(){
        return view('admin.wallet_address.index');
    }
    public function lists(Request $request){
        $limit = $request->get('limit',10);
        $list = WalletAddress::orderBy('is_default','desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    public function add(Request $request){
        $id = $request->get('id',null);
        if (empty($id)){
            $result = new WalletAddress();
        }else{
            $result = WalletAddress::find($id);
        }
        return view('admin.wallet_address.add')->with(['result'=>$result]);
    }
    public function del(Request $request){
    	$id = $request->get('id',null);
    	$wallet_address = WalletAddress::find($id);
    	$wallet_address_count = WalletAddress::count();
    	if($wallet_address_count == 1) {
    	    return $this->error('最后一个钱包地址不能删除'); 
    	}
    	if($wallet_address){
    		$result = $wallet_address->delete();
    		if($result){
    			return $this->success('删除成功');
    		}else{
    			return $this->error('删除失败');
    		}
    	}else{
    		return $this->error('没有找到该钱包地址');
    	}
    }
    public function setVoucher(Request $request) {
        $id = $request->get('id',null);
        $wallet_address = WalletAddress::find($id);
    	if($wallet_address){
    	    $wallet_address->is_voucher = $wallet_address->is_voucher == 1 ? 0 : 1;
    		$result = $wallet_address->save();
    		if($result){
    			return $this->success('修改成功');
    		}else{
    			return $this->error('修改失败');
    		}
    	}else{
    		return $this->error('没有找到该钱包地址');
    	}
    }
    public function setShow(Request $request) {
        $id = $request->get('id',null);
        $wallet_address = WalletAddress::find($id);
    	if($wallet_address){
    	    $wallet_address->is_show = $wallet_address->is_show == 1 ? 0 : 1;
    		$result = $wallet_address->save();
    		if($result){
    			return $this->success('修改成功');
    		}else{
    			return $this->error('修改失败');
    		}
    	}else{
    		return $this->error('没有找到该钱包地址');
    	}
    }
    public function setDefault(Request $request) {
        $id = $request->get('id',null);
        $wallet_address = WalletAddress::find($id);
        // 判断是否存在默认的
        $count = WalletAddress::where('is_default',1)->count();
        if($count == 1 && $wallet_address->is_default == 1) {
            return $this->error('修改失败，必须指定一个默认地址！');
        }
    	if($wallet_address){
    	    // 把其它相同币种名称的改成不默认
    	    $wallet_address->is_default = $wallet_address->is_default == 1 ? 0 : 1;
    		$result = $wallet_address->save();
    		if($wallet_address->is_default == 1) {
    		    WalletAddress::where('id','!=',$id)->update(['is_default' => 0]);
    		}
    		if($result){
    			return $this->success('修改成功');
    		}else{
    			return $this->error('修改失败');
    		}
    	}else{
    		return $this->error('没有找到该钱包地址');
    	}
    }
    public function postAdd(Request $request){
        $id = $request->get('id',null);
        $name = $request->get('name','');
        $is_default = $request->get('is_default',0);
        $messages  = [
            'name.required'       => '分组名称必填'
        ];

        //验证
        $validator = Validator::make($request->all(), [
            'name' => 'required', //正则验证 如有多条不能用| 必须是数组 ['required','regex:/^[a-zA-Z0-9]$/']
        ], $messages);

        if ($validator->fails()){
            return $this->error($validator->errors()->first());
        }
        if (empty($id)){
            $wallet_address = new WalletAddress();
        }else{
            $wallet_address = WalletAddress::find($id);
            if($is_default == 0) {
                // 判断是否存在默认的
                $count = WalletAddress::where('is_default',1)->count();
                if($count == 1 && $wallet_address->is_default == 1) {
                    return $this->error('修改失败，必须指定一个默认地址！');
                }
            }
        }
        try{
            $wallet_address->name = $name;
            $wallet_address->is_default = $is_default;
            $wallet_address->save();
            if($is_default == 1) {
                if(empty($id)) {
                    // 把其它相同默认币种名称的改成不默认
                    WalletAddress::where('name',$wallet_address->name)->update(['is_default' => 0]);
                }else {
                     WalletAddress::where('name',$wallet_address->name)->where('id','!=',$id)->update(['is_default' => 0]);
                }
            }
            return $this->success('保存成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }
    }
}
