<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use App\Models\MicroNumber;
use App\Models\MicroOrder;
use App\Models\MicroSecond;
use App\Models\LockMining;
use App\Models\LockMiningOrder;
use App\Models\Currency;
use App\Models\CurrencyMatch;
use App\Models\Setting;
use App\Models\UsersWallet;
use App\Models\Users;

class LockController extends Controller
{


    public function index()
    {
        return view('admin.micro.index');
    }

    public function add(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new MicroNumber();
        } else {
            $result = MicroNumber::find($id);
        }
        $currencies = Currency::where('is_micro', 1)->get();

        return view('admin.micro.add')->with('result', $result)->with('currencies', $currencies);
    }

    public function postAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $currency_id = $request->get('currency_id', '');
        $number = $request->get('number', '');

        if (empty($id)) {
            $micro_number = new MicroNumber();
        } else {
            $micro_number = MicroNumber::find($id);
            if ($micro_number == null) {
                return redirect()->back();
            }
        }
        $micro_number->currency_id = $currency_id;
        $micro_number->number = $number;

        DB::beginTransaction();
        try {
            $micro_number->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function lists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroNumber();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function del(Request $request)
    {
        $id = $request->get('id', 0);
        $lock_mining = LockMining::find($id);
        if (empty($lock_mining)) {
            return $this->error('参数错误');
        }
        try {
            $lock_mining->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }


    //micro_seconds

    public function secondsIndex()
    {
        return view('admin.micro.seconds_index');
    }

    public function secondsAdd(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            $result = new MicroSecond();
        } else {
            $result = MicroSecond::find($id);
        }
        //        $currencies = Currency::where('is_micro',1)->get();

        return view('admin.micro.seconds_add')->with('result', $result);
    }

    public function secondsPostAdd(Request $request)
    {
        $id = $request->get('id', 0);
        $seconds = $request->get('seconds', '');
        $status = $request->get('status', '');
        $profit_ratio = $request->get('profit_ratio', '');

        if (empty($id)) {
            $result = new MicroSecond();
        } else {
            $result = MicroSecond::find($id);
            if ($result == null) {
                return redirect()->back();
            }
        }
        $result->seconds = $seconds;
        $result->profit_ratio = $profit_ratio;
        $result->status = $status;

        DB::beginTransaction();
        try {
            $result->save(); //保存币种
            DB::commit();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->error($exception->getMessage());
        }
    }

    public function secondsLists(Request $request)
    {
        $limit = $request->get('limit', 10);
        $result = new MicroSecond();
        $result = $result->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($result);
    }

    public function secondsDel(Request $request)
    {
        $id = $request->get('id', 0);
        $result = MicroSecond::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        try {
            $result->delete();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function secondsStatus(Request $request)
    {
        $id = $request->get('id', 0);
        $result = MicroSecond::find($id);
        if (empty($result)) {
            return $this->error('参数错误');
        }
        if ($result->status == 1) {
            $result->status = 0;
        } else {
            $result->status = 1;
        }
        try {
            $result->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }

    public function order()
    {
        $currencies = Currency::where('is_display', 1)->get();
        $authorityList=session()->get("authorityList");
        return view('admin.lock.orders')
            ->with('currencies', $currencies)
            ->with('authorityList',$authorityList);
    }

    public function orderList(Request $request)
    {
        $from_name = $request->input('from_name', '');
        $to_name = $request->input('to_name', '');
        $account = $request->input('account', '');
        $name = $request->input('name', '');
        $limit = $request->input('limit', 10);
        $status = $request->input('status', '');
        $start = $request->input("start_time", '');
        $end = $request->input("end_time", '');
        $user_id = request()->input('user_id', '');
        $results = LockMiningOrder::with(['user'])
            ->when($to_name!='', function ($query) use ($to_name) {
                $query->where('to_name', $to_name);
            })->when($from_name != '', function ($query) use ($from_name) {
                $query->where('from_name', $from_name);
            })->when($status != '', function ($query) use ($status) {
                $query->where('status', $status);
            })->when($account != '' || $name != '', function ($query) use ($account, $name) {
                $query->whereHas('user', function ($query) use ($account, $name) {
                    $account != '' && $query->where("phone", 'like', '%' . $account . '%')->orwhere('email', 'like', '%' . $account . '%');
                    // $query->when($name != '', function ($query) use ($name) {
                    //     $query->whereHas('userReal', function ($query) use ($name) {
                    //         $query->where("name", 'like', '%' . $name . '%');
                    //     });
                    // });
                });
            })->when($start !='', function ($query) use ($start) {
                $query->where('created_at','>=', strtotime($start));
            })->when($end !='', function ($query) use ($end) {
                $query->where('created_at','<=', strtotime($end));
            })->orderBy('id', 'desc');
            
        if (!empty($user_id)) {
            $results = $results->where('user_id', '=', $user_id);
        }
        $results = $results->paginate($limit);
            
        // var_dump($results);
        // exit;
        return $this->layuiData($results);
    }

    public function edit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = MicroOrder::findOrNew($id);

        return view('admin.micro.edit', ['result' => $result]);
    }

    //编辑用户信息  
    public function editPost(Request $request)
    {

        $risk = $request->get('risk', 0);

        $id = $request->get("id");

        if (empty($id)) return $this->error("参数错误");

        $res = MicroOrder::find($id);
        if (empty($res)) {
            return $this->error("数据未找到");
        }
        if ($res->status != 1) {
            return $this->error("数据状态下不能修改");
        }

        $res->pre_profit_result = $risk;


        DB::beginTransaction();

        try {
            $res->save();

            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }

    public function batchRisk(Request $request)
    {
        try {
            $ids = Input::get('ids', []);
            $risk = Input::get('risk', 0);
            if (empty($ids)) {
                throw new \Exception('请先选择用户');
            }
            if (!in_array($risk, [-1, 0, 1])) {
                throw new \Exception('风控类型不正确');
            }
            $affect_rows = Users::whereIn('id', $ids)
                ->update([
                    'risk' => $risk,
                ]);
            return $this->success('本次提交:' . count($ids) . '条,设置成功:' . $affect_rows . '条');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage());
        }
    }
    
    public function config()
    {
        $authorityList=session()->get("authorityList");
        return view('admin.lock.config',['authorityList' =>$authorityList]);
    
    }
     public function configIndex(Request $request)
    {
        $limit = $request->get('limit', 10);
        $list = LockMining::orderBy('id', 'asc')
        ->paginate($limit);
        foreach ($list as $po){
            $title=$po->title;
            if ($title=='1'){
                $po->titleZH="初级";
            }
            if ($title=='2'){
                $po->titleZH="中级";
            }
            if ($title=='3'){
                $po->titleZH="高级";
            }
            if ($title=='4'){
                $po->titleZH="高级";
            }
            if ($title=='5'){
                $po->titleZH="砖石VIP";
            }
        }
        return response()->json(['code' => 0, 'data' => $list->items(), 'count' => $list->total()]);
        
    }
    
    public function configEdit(Request $request)
    {
        $id = $request->get('id', 0);
        if (empty($id)) {
            return $this->error("参数错误");
        }

        $result = LockMining::findOrNew($id);
        $currency = Currency::where('is_display',1)->get();
        return view('admin.lock.config_edit', ['result' => $result,'currencies'=>$currency]);
    }
    
    public function configAdd()
    {
        
        $currency = Currency::where('is_display',1)->get();
       
        return view('admin.lock.config_add',['currencies'=>$currency]);
    }
    
      //编辑锁仓挖矿 
    public function configdoEdit(Request $request)
    {

        //$status = $request->get('status', 0);
        
        $title=$request->get('title','');
        $entitle =$request->get('entitle','');
        $hktitle=$request->get('hktitle','');

        $id = $request->get("id");
        $from_name = $request->post("from_name");
        $to_name = $request->post("to_name");
        $day = $request->post("day");
        $rate = $request->post("rate");
        $money = $request->post('money');
        $min_money = $request->post('min_money');
        $max_money = $request->post('max_money');
        $rate_max = $request->post('rate_max');
        $adance_redeem_falsify = $request->post('adance_redeem_falsify');
        $adance_redeem_credit = $request->post('adance_redeem_credit');
        // var_dump($seconds);
        // exit;
        if (empty($id)) return $this->error("参数错误");

        $res = LockMining::find($id);
        if (empty($res)) {
            return $this->error("数据未找到");
        }
        
        $res->title = $title;
        $res->entitle = $entitle;
        $res->hktitle = $hktitle;
        
        $res->from_name = $from_name;
        $res->to_name = $to_name;
        $res->day = $day;
        $res->rate = $rate;
        $res->money = $money;
        $res->min_money = $min_money;
        $res->max_money = $max_money;
        $res->rate_max = $rate_max;
        $res->adance_redeem_falsify = $adance_redeem_falsify;
        $res->adance_redeem_credit = $adance_redeem_credit;
        DB::beginTransaction();

        try {
            $res->save();

            DB::commit();
            return $this->success('编辑成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
    
    
    public function lcokconfigStatus(Request $request)
    {
        $id = $request->input('id', 0);

        $user = LockMining::find($id);
        if (empty($user)) {
            return $this->error('参数错误');
        }
        if ($user->status == 1) {
            $user->status = 0;
        } else {
            $user->status = 1;
        }
        try {
            $user->save();
            return $this->success('操作成功');
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    
    /*
        进行添加
    */
    
     //新增锁仓挖矿
    public function configdoAdd(Request $request)
    {

        //$status = $request->get('status', 0);
        $from_name = $request->post("from_name");
        $to_name = $request->post("to_name");
        $day = $request->post("day");
        $rate = $request->post("rate");
        $money = $request->post('money');
        $min_money = $request->post('min_money');
        $max_money = $request->post('max_money');
        $rate_max = $request->post('rate_max');
        $adance_redeem_falsify = $request->post('adance_redeem_falsify');
        $adance_redeem_credit = $request->post('adance_redeem_credit');
        // var_dump($seconds);
        // exit;
       
        $res = new LockMining();
        $res->from_name = $from_name;
        $res->to_name = $to_name;
        $res->day = $day;
        $res->rate = $rate;
        $res->money = $money;
        $res->min_money = $min_money;
        $res->max_money = $max_money;
        $res->rate_max = $rate_max;
        $res->adance_redeem_falsify = $adance_redeem_falsify;
        $res->adance_redeem_credit = $adance_redeem_credit;
       
        DB::beginTransaction();

        try {
            $res->save();

            DB::commit();
            return $this->success('添加成功');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->error($ex->getMessage());
        }
    }
    
  


}
