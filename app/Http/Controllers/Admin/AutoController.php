<?php

namespace App\Http\Controllers\Admin;

use App\Models\AutoList;
use App\Models\AutoRobot;
use App\Models\Currency;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

class AutoController extends Controller
{
    public function index(){
    	
        return view('admin.auto.index');
    }
	public function robot(){

        $authorityList=session()->get("authorityList");


        return view('admin.auto.robot',['authorityList' => $authorityList]);
    }
    public function add(Request $request){
        $id = $request->get('id',null);
        if (empty($id)){
            $result = new AutoList();
        }else{
            $result = AutoList::find($id);
        }
        $currencies = Currency::where('is_display',1)->orderBy('id','desc')->get();
        $legals = Currency::where('is_legal',1)->orderBy('id','desc')->get();
        return view('admin.auto.add')->with(['currencies'=>$currencies,'legals'=>$legals,'result'=>$result]);
    }

    public function postAdd(Request $request){
        $id = $request->get('id',null);
        $sell_account = $request->get('sell_account',null);
        $buy_account = $request->get('buy_account',null);
        $currency_id = $request->get('currency_id',null);
        $legal_id = $request->get('legal_id',null);
        $min_price = $request->get('min_price',null);
        $max_price = $request->get('max_price',null);
        $min_number = $request->get('min_number',null);
        $max_number = $request->get('max_number',null);
        $need_second = $request->get('need_second',null);

        $messages  = [
            'sell_account.required'       => '卖家账号必填',
            'buy_account.required'           => '买家账号必填',
            'currency_id.required' => '请选择交易币',
            'currency_id.integer' => '交易币值必须为整型',
            'legal_id.required' => '请选择法币',
            'legal_id.integer' => '法币值必须为整型',
            'min_price.required' => '请填写最低价格区间',
            'min_price.numeric' => '最低价格区间必须为数字',
            'max_price.required' => '请填写最高价格区间',
            'max_price.numeric' => '最高价格区间必须为数字',
            'min_number.required' => '请填写最低随机购买数量',
            'min_number.numeric' => '最低随机购买数量必须为数字',
            'max_number.required' => '请填写最高随机购买数量',
            'max_number.numeric' => '最高随机购买数量必须为数字',
            'need_second.required' => '请填写生成频率',
            'need_second.integer' => '生成频率必须为整型',
        ];

        //验证
        $validator = Validator::make($request->all(), [
            'sell_account' => 'required', //正则验证 如有多条不能用| 必须是数组 ['required','regex:/^[a-zA-Z0-9]$/']
            'buy_account'   => 'required',
            'currency_id' => 'required|integer',
            'legal_id' => 'required|integer',
            'min_price' => 'required|numeric',
            'max_price' => 'required|numeric',
            'min_number' => 'required|numeric',
            'max_number' => 'required|numeric',
            'need_second' => 'required|integer',
        ], $messages);

        if ($validator->fails()){
            return $this->error($validator->errors()->first());
        }

        $sell_user = Users::where('account_number',$sell_account)->first();
        if (empty($sell_user)) return $this->error('卖家用户不存在');
        $buy_user = Users::where('account_number',$buy_account)->first();
        if (empty($buy_user)) return $this->error('卖家账号不存在');
        $currency = Currency::find($currency_id);
        if (empty($currency)) return $this->error('交易币不存在');
        $legal = Currency::find($legal_id);
        if (empty($legal) || empty($legal->is_legal)) return $this->error('该币不是法币');
        if ($min_price >= $max_price) return $this->error('请设置正确的价格区间');
        if ($min_number >= $max_number) return $this->error('请填写正确的随机数量');
        if ($need_second <= 0) return $this->error('请填写正确的生成秒数');

        $is = AutoList::where('currency_id',$currency_id)->where('legal_id',$legal_id)->first();

        if (!empty($is) && empty($id)){
            return $this->error('该交易对已经有机器人了');
        }

        if (empty($id)){
            $auto_list = new AutoList();
            $auto_list->create_time = time();

        }else{
            $auto_list = AutoList::find($id);
        }
        try{
            $auto_list->buy_user_id = $buy_user->id;
            $auto_list->sell_user_id = $sell_user->id;
            $auto_list->currency_id = $currency_id;
            $auto_list->legal_id = $legal_id;
            $auto_list->min_price = $min_price;
            $auto_list->max_price = $max_price;
            $auto_list->min_number = $min_number;
            $auto_list->max_number = $max_number;
            $auto_list->need_second = $need_second;
            $auto_list->save();
            return $this->success('添加成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }


    }
    
    public function autoRobotDel(Request $request){
    	$id = $request->get('id',null);
    	
    	$auto_robot = AutoRobot::find($id);
    	if($auto_robot){
    		$result = $auto_robot->delete();
    		if($result){
    			return $this->success('删除成功');
    		}else{
    			return $this->error('删除失败');
    		}
    	}else{
    		return $this->error('没有找到该机器人');
    	}
    }
	public function robotAdd(Request $request){
        $id = $request->get('id',null);
        if (empty($id)){
            $result = new AutoRobot();
        }else{
            $result = AutoRobot::find($id);
        }
        $currencies = Currency::where('is_display',1)->orderBy('id','desc')->get();
        $legals = Currency::where('is_legal',1)->orderBy('id','desc')->get();
       
        return view('admin.auto.robotAdd')->with(['currencies'=>$currencies,'legals'=>$legals,'result'=>$result]);
    }

    public function postRobotAdd(Request $request){
        $id = $request->get('id',null);
        $sell_account = $request->get('sell_account',null);
        $buy_account = $request->get('buy_account',null);
        $currency_id = $request->get('currency_id',null);
        $legal_id = $request->get('legal_id',null);
        $init_price = $request->get('init_price',null);
        $up_weight = $request->get('up_weight',null);
        $down_weight = $request->get('down_weight',null);

        $min_need_second = '5';
        $max_need_second = '10';
        $price_precision = '8';
		$num_precision = '8';
		
        $messages  = [
            'sell_account.required'       => '卖家账号必填',
            'buy_account.required'           => '买家账号必填',
            'currency_id.required' => '请选择交易币',
            'currency_id.integer' => '交易币值必须为整型',
            'legal_id.required' => '请选择法币',
            'legal_id.integer' => '法币值必须为整型',
            'up_weight.required' => '请选择涨幅权重',
            'down_weight.required' => '请选择跌幅权重',
            'init_price.required' => '请设置目标价格',
            'init_price.numeric' => '目标价格必须数字',
        ];

        //验证
        $validator = Validator::make($request->all(), [
            'sell_account' => 'required', //正则验证 如有多条不能用| 必须是数组 ['required','regex:/^[a-zA-Z0-9]$/']
            'buy_account'   => 'required',
            'currency_id' => 'required|integer',
            'legal_id' => 'required|integer',
            'init_price' => 'required|numeric',
        ], $messages);

        if ($validator->fails()){
            return $this->error($validator->errors()->first());
        }

        $base_price = floatval($init_price);
        if($up_weight>$down_weight){
            $min_price = $base_price*0.7; //初始价格-10%
            $max_price = $base_price*1.1; //初始价格+10%
        }
        else if($up_weight<$down_weight){
            $min_price = $base_price*0.9; //初始价格-10%
            $max_price = $base_price*1.3; //初始价格+10%
        }else {
            $min_price = $base_price*0.9; //初始价格-10%
            $max_price = $base_price*1.1; //初始价格+10%
        }
        $min_number = ceil(10000.0/$base_price);  //100/初始价格
        $max_number = ceil(100000.0/$base_price);  //10000//初始价格
        $up_price = $base_price/200.0;      //初始价格/200
        $down_price = $base_price/2000.0;  //初始价格/2000


        $sell_user = Users::where('account_number',$sell_account)->first();
        if (empty($sell_user)) return $this->error('卖家用户不存在');
        $buy_user = Users::where('account_number',$buy_account)->first();
        if (empty($buy_user)) return $this->error('卖家账号不存在');
        $currency = Currency::find($currency_id);
        if (empty($currency)) return $this->error('交易币不存在');
        $legal = Currency::find($legal_id);
        if (empty($legal) || empty($legal->is_legal)) return $this->error('该币不是法币');
        // if ($up_price >= $down_price) return $this->error('请设置正确的每次涨跌价格');
        // if ($up_weight >= $down_weight) return $this->error('请设置正确的涨跌幅权重');
        if ($min_price >= $max_price) return $this->error('请设置正确的价格区间');
        if ($min_number >= $max_number) return $this->error('请填写正确的随机数量');
        if ($min_need_second <= 0) return $this->error('请填写正确的生成最小秒数');
		if ($max_need_second <= 0) return $this->error('请填写正确的生成最大秒数');
        $is = AutoRobot::where('currency_id',$currency_id)->where('legal_id',$legal_id)->first();

        if (!empty($is) && empty($id)){
            return $this->error('该交易对已经有机器人了');
        }

        if (empty($id)){
            $auto_list = new AutoRobot();
            $auto_list->create_time = time();

        }else{
            $auto_list = AutoRobot::find($id);
            if($auto_list->init_price>$max_price)  $max_price=$auto_list->init_price;
            if($auto_list->init_price<$min_price)  $min_price=$auto_list->init_price;
        }
        try{
        	
            $auto_list->buy_user_id = $buy_user->id;
            $auto_list->sell_user_id = $sell_user->id;
            $auto_list->currency_id = $currency_id;
            $auto_list->legal_id = $legal_id;
            $auto_list->min_price = $min_price;
            $auto_list->max_price = $max_price;
            $auto_list->min_number = $min_number;
            $auto_list->max_number = $max_number;
            
            $auto_list->up_price = $up_price;
            $auto_list->down_price = $down_price;
            $auto_list->min_need_second = $min_need_second;
            $auto_list->max_need_second = $max_need_second;
            $auto_list->init_price = $init_price;
            $auto_list->up_weight = $up_weight;
            $auto_list->down_weight = $down_weight;
            $auto_list->price_precision = $price_precision;
            $auto_list->num_precision = $num_precision;
            
            $auto_list->save();
            return $this->success('添加成功');
        }catch (\Exception $exception){
        	
            return $this->error($exception->getMessage()."|||".$exception->getLine());
        }


    }

    public function lists(Request $request){
        $limit = $request->get('limit');
        $results = AutoList::orderBy('id','desc')->paginate($limit);
        return $this->layuiData($results);
    }
	public function robotList(Request $request){
        $limit = $request->get('limit');
        $results = AutoRobot::orderBy('id','desc')->paginate($limit);
        return $this->layuiData($results);
    }

    public function postStart(Request $request){
        $id = $request->get('id',null);
        try{
            if (empty($id)) return $this->error('参数错误');
            $auto = AutoList::find($id);
            if (empty($auto)) return $this->error('无此机器人');

            if ($auto->is_start == 1){
                $auto->is_start = 0;
                $auto->save();
            }else {
                $is = AutoList::where('is_start',1)->first();
                //if (!empty($is)) return $this->error('目前有正在开启的机器人，请关闭后再开启此机器人');
                $auto->is_start = 1;
                $auto->save();
                $path           = base_path();
                $process        = new Process('nohup php artisan auto_order ' . $id . ' >./auto_order.log 2>&1 &', $path); //第一个参数是运行的命令,命令方式跟 Linux 一致，第二个参数是可以执行此条命令的路径
                $process->start();
            }
            return $this->success('操作成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }

    }
    public function newPostStart(Request $request){
        $id = $request->get('id',null);
        try{
            if (empty($id)) return $this->error('参数错误');
            $auto = AutoRobot::find($id);
            if (empty($auto)) return $this->error('无此机器人');

            if ($auto->is_start == 1){
                $auto->is_start = 0;
                $auto->save();
            }else {
                $is = AutoRobot::where('is_start',1)->first();
                //if (!empty($is)) return $this->error('目前有正在开启的机器人，请关闭后再开启此机器人');
                $auto->is_start = 1;
                $auto->save();
                $path           = base_path();
                $process        = new Process('nohup php artisan auto_robot ' . $id . ' >./auto_robot.log 2>&1 &', $path); //第一个参数是运行的命令,命令方式跟 Linux 一致，第二个参数是可以执行此条命令的路径
                $process->start();
            }
            return $this->success('操作成功');
        }catch (\Exception $exception){
            return $this->error($exception->getMessage());
        }

    }
}
