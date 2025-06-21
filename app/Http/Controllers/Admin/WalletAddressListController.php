<?php

namespace App\Http\Controllers\Admin;
use App\Models\WalletAddressList;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class WalletAddressListController extends Controller
{
    public function index(Request $request){
        $wallet_address_id = $request->get('id',null);
        return view('admin.wallet_address.currency_list')->with(['wallet_address_id' => $wallet_address_id]);;
    }
    public function lists(Request $request){
        $limit = $request->get('limit',10);
        $wallet_address_id = $request->get('wallet_address_id',null);
        $list = WalletAddressList::where('wallet_address_id',$wallet_address_id)->orderBy('sort','desc')->paginate($limit);
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
    }
    public function add(Request $request){
        $id = $request->get('id',null);
        $wallet_address_id = $request->get('wallet_address_id',null);
        if (empty($id)){
            $result = new WalletAddressList();
        }else{
            $result = WalletAddressList::find($id);
        }
        return view('admin.wallet_address.currency_add')->with(['result'=>$result,'wallet_address_id' => $wallet_address_id]);
    }
    public function del(Request $request){
    	$id = $request->get('id',null);
    	$wallet_address_list = WalletAddressList::find($id);
    	if($wallet_address_list){
    		$result = $wallet_address_list->delete();
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
        $wallet_address_list = WalletAddressList::find($id);
    	if($wallet_address_list){
    	    $wallet_address_list->is_voucher = $wallet_address_list->is_voucher == 1 ? 0 : 1;
    		$result = $wallet_address_list->save();
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
        $wallet_address_list = WalletAddressList::find($id);
    	if($wallet_address_list){
    	    $wallet_address_list->is_show = $wallet_address_list->is_show == 1 ? 0 : 1;
    		$result = $wallet_address_list->save();
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
        $wallet_address_id = $request->get('wallet_address_id',null);
        $id = $request->get('id',null);
        $name = $request->get('name','');
        $is_voucher = $request->get('is_voucher',1);
        $is_show = $request->get('is_show',1);
        
        $address = $request->get('address','');
        $min_limit = $request->get('min_limit',0);
        $max_limit = $request->get('max_limit',0);
        
        $sort = $request->get('sort',0);
        if(empty($sort)) {
            $sort = 0;
        }
        
        $messages  = [
            'wallet_address_id.required' => '分组不能为空',
            'name.required'       => '币种不能为空',
            
            'address.required'           => '钱包地址必填',
            'min_limit.required' => '请填写最小额度',
            'min_limit.numeric' => '最小额度必须为数字',
            'max_limit.required' => '请填写最大额度',
            'max_limit.numeric' => '最大额度必须为数字'
        ];

        //验证
        $validator = Validator::make($request->all(), [
            'wallet_address_id' => 'required',
            'name' => 'required', //正则验证 如有多条不能用| 必须是数组 ['required','regex:/^[a-zA-Z0-9]$/']
            'address'   => 'required',
            'min_limit' => 'required|numeric',
            'max_limit' => 'required|numeric'
        ], $messages);

        if ($validator->fails()){
            return $this->error($validator->errors()->first());
        }

        if ($min_limit >= $max_limit) return $this->error('请设置正确的额度区间');

        if (empty($id)){
            // 判断币种是否已经存在
            $is_currency = WalletAddressList::where('wallet_address_id',$wallet_address_id)->where('name',$name)->first();
            if($is_currency) return $this->error('该币种已存在，请勿重复添加');
            $wallet_address_list = new WalletAddressList();
        }else{
            $wallet_address_list = WalletAddressList::find($id);
        }
        try{
            DB::beginTransaction();
            // 查询币种列表
            if($name == 'USDT(TRC20)' || $name == 'USDT(ERC20)') {
                $wallet_address_list->currency_id = 23;
            }else {
                $currency = Currency::where('name',$name)->first();
                if(empty($currency)) {
                    throw new \Exception("该币种不存在");
                }
                $wallet_address_list->currency_id = $currency->id;
            }
            $wallet_address_list->wallet_address_id = $wallet_address_id;
            $wallet_address_list->name = $name;
            $wallet_address_list->is_voucher = $is_voucher;
            $wallet_address_list->is_show = $is_show;
            
            $wallet_address_list->address = $address;
            $wallet_address_list->min_limit = $min_limit;
            $wallet_address_list->max_limit = $max_limit;
            $wallet_address_list->sort = $sort;
            
            $wallet_address_list->save();
            DB::commit();
            return $this->success('保存成功');
        }catch (\Exception $exception){
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }
}
