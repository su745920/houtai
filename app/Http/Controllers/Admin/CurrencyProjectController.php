<?php


namespace App\Http\Controllers\Admin;


use App\Models\Currency;
use App\Models\CurrencyProjectOrder;
use App\Models\CurrencyProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\UsersWallet;
use App\Models\AccountLog;


class CurrencyProjectController extends Controller
{


    public function projectDetail(Request $request){
        $id = $request->get('project_id');
        $project = CurrencyProject::find($id);
        return $this->success($project);
    }

    public function projectList(Request $request){
        $limit = $request->get('limit', 20);
        $model = new CurrencyProject();
        $currency_id = $request->get('currency_id');
        $where = [];
        if($currency_id){
            $where['currency_id'] = $currency_id;
        }
        $where['isdel'] = 0;
        $list = $model->where($where)->paginate($limit);
        return $this->layuiData($list);
    }

    public function newProject(Request $request){
        $currencyId = $request->get('currency_id');
        $payCurrencyId = $request->get('pay_currency_id');
        $title = $request->get('title');
        $summary = $request->get('summary');
        $content = $request->get('content');
        $startAt = $request->get('start_at');
        $endAt = $request->get('end_at');
        $whiteBook = $request->get('white_book');
        $price = $request->get('price');
        $link = $request->get('link');
        $logo = $request->get('logo');
        $min = $request->get('min',0);
        $amount = $request->get('amount');
        $sellBegin = $request->get('sell_begin',null);

        if(empty($amount) ||empty($currencyId) || empty($title) || empty($summary) || empty($content) || empty($startAt) || empty($endAt) || empty($whiteBook) || empty($link)){
            return $this->error('内容不能为空');
        }
        $currency = Currency::find($currencyId);
        if(!$currency){
            return $this->error('币种不存在');
        }
        $payCurrency = Currency::find($payCurrencyId);
        if(!$payCurrency){
            return $this->error('支付币种不存在');
        }
        // $res = CurrencyProject::where('currency_id',$currencyId)->first();
        // if($res){
        //     return $this->error('该币种已成立项目，不能重复');
        // }
        if(strtotime($startAt) < time() || strtotime($endAt) < time()){
            return $this->error('开始时间与结束时间不得早于当前时间');
        }
        if($sellBegin && strtotime($sellBegin) < strtotime($endAt)){
            return $this->error('配售时间不得早于结束时间');
        }
        if(empty($request->input('total_apply')) || empty($request->input('win_rate'))){
            return $this->error('中奖配置不能为空');
        }
        CurrencyProject::insert([
            'currency_id' => $currencyId,
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'white_book' => $whiteBook,
            'link' => $link,
            'price' => $price,
            'pay_currency_id' => $payCurrencyId,
            'start_at' => $startAt,
            'end_at' => $endAt,
            'logo' => $logo,
            'sell_begin' => $sellBegin,
            'min' => $min,
             'total_sell' => 0,
            'amount' => $amount,
            'sell_amount' => $request->input('sell_amount',0),
            'total_apply' => $request->input('total_apply'),
            'win_rate' => $request->input('win_rate')

        ]);
        return $this->success('创建成功');
    }

    public function editProject(Request $request){
        $projectId = $request->get('project_id');
        $title = $request->get('title');
        $summary = $request->get('summary');
        $content = $request->get('content');
        $startAt = $request->get('start_at');
        $price = $request->get('price');
        $endAt = $request->get('end_at');
        $whiteBook = $request->get('white_book');
        $logo = $request->get('logo');
        $link = $request->get('link');
        $min = $request->get('min',0);
        $sellBegin = $request->get('sell_begin',null);
        $amount = $request->get('amount',0);
        if(empty($amount) || empty($projectId) || empty($title) || empty($summary) || empty($content) || empty($startAt) || empty($endAt) || empty($whiteBook) || empty($link)){
            return $this->error('内容不能为空');
        }

        $project = CurrencyProject::find($projectId);
        if(!$project){
            return $this->error('找不到此项目');
        }
        if(strtotime($project->start_at) < time()){
            // return $this->error('项目已开启，不可编辑');
        }
        if($project->status == 2){
            return $this->error('此项目已完结');
        }
        if($sellBegin && strtotime($sellBegin) < strtotime($endAt)){
            return $this->error('配售时间不得早于结束时间');
        }
        $data = [
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'white_book' => $whiteBook,
            'price' => $price,
            'link' => $link,
            'start_at' => $startAt,
            'logo' => $logo,
            'end_at' => $endAt,
            'sell_begin' => $sellBegin,
            'min' => $min,
            'amount' =>$amount,
            'sell_amount' => $request->input('sell_amount',0),
            'total_apply' => $request->input('total_apply'),
            'win_rate' => $request->input('win_rate')
        ];
        CurrencyProject::where('id',$projectId)->update($data);
        return $this->success('创建成功');
    }

    public function del(Request $request){
        $id = $request->get('id');
        //DB::connection()->getPdo()->exec("update currency_project set  isdel=1  where currency_id = $id");//对
        $po=CurrencyProject::find($id);
        $bool = $po->delete();
        if($bool){
            return $this->success('删除成功');
        }else{
            return $this->error('删除失败');
        }

    }


    public function projectOrderList(Request $request){
        $limit = $request->input('limit', 10);
        $account_number = $request->input('account_number', '');
        $created_at = $request->input('created_at', '');
        $end_at = $request->input('end_at', '');
        $type = $request->input('type','');
        $status = $request->input('status','');
        $lists = CurrencyProjectOrder::query()->join('users','users.id','=','u_id')->join('currency','currency.id','=','currency_id');
        
        
        if (!empty($account_number)) {
            $lists = $lists->where("users.phone", 'like', '%' . $account_number . '%')
                ->orwhere('users.email', 'like', '%' . $account_number . '%')
                ->orWhere('users.account_number', 'like', '%' . $account_number . '%');
        }
        if (!empty($type)) {
            $lists = $lists->where('currency_project_order.type', $type);
        }
        if (!empty($status)) {
            $lists = $lists->where('currency_project_order.status', $status);
        }
        if (!empty($created_at)) {
            $lists = $lists->where('currency_project_order.created_at', '>=', $created_at);
        }
        if (!empty($end_at)) {
            $lists = $lists->where('currency_project_order.end_at', '<=', $end_at);
        }
        $orders = $lists->select(['currency_project_order.*','currency.name','users.account_number'])->orderBy('id', 'desc')->paginate($limit);
        return $this->layuiData($orders);
    }

    public function confirmSell(Request $request){
        $currency_order_id = $request->input('id');
        $passed_amount = $request->input('passed_amount');
        if(empty($currency_order_id)){
            return $this->error('参数错误');
        }
        $order = CurrencyProjectOrder::find($currency_order_id);
        if(!$order){
            return $this->error('找不到订单');
        }
        if($order->status != 2){
            return $this->error('订单状态异常');
        }
        if($passed_amount == 0){
            $total_price = 0;
        }
        if($passed_amount > 0){
            $total_price = bc_mul($order->price,$passed_amount,8);
        }
        $op_wallet = UsersWallet::where('user_id',$order->u_id)
            ->where('currency',$order->currency_id)
            ->first();
        DB::beginTransaction();
        try{
            //发币(冻结)
            $result = change_wallet_balance($op_wallet,
            1,
            $passed_amount,
            AccountLog::LOCK_MINING,
            '新币申购发放冻结资产','New currency subscription and issuance freeze assets',true);

            if((0 < $passed_amount) &&  ($passed_amount < $order->coin_amount)){
                $return_price = bc_sub($order->total_price,$total_price,8);      
                //退款
                $wallet = UsersWallet::where("user_id", $order->u_id)
                    ->where("currency", $order->pay_currency_id)
                    ->lockForUpdate()
                    ->first();
                $result = change_wallet_balance($wallet,
                    2,
                    +$return_price,
                    AccountLog::IEO_OPERATION,
                    '新币审核退还资金',
                    'Refund of funds for new currency review');
                if ($result !== true) {
                    throw new \Exception($result);
                }
            }
            $order->total_price = $total_price;
            $order->passed_amount = $passed_amount;
            $order->status = 3;
            $order->save();
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            return $this->error($e->getMessage());
        }
        return $this->success('success');
    }

    public function confirmSellView(Request $request){
        $data = [
            'id' => $request->get('id'),
            'account_number' => $request->get('account_number'),
            'coin_amount' => $request->get('coin_amount'),
            'name' => $request->get('name'),
            'passed_amount' => $request->get('passed_amount')
        ];

        return view('admin.currency.confirm_sell',$data);
    }

    public function projectOrderView(){

        $authorityList=session()->get("authorityList");
        return view('admin.currency.project_orders', [
            'authorityList' =>$authorityList
        ]);
    }

    public function projectView(){
        $authorityList=session()->get("authorityList");
        return view('admin.currency.project_list', [
            'authorityList' =>$authorityList
        ]);
    }
    public function projectDetailView(Request  $request){

        $id = $request->input('id',0);
        $info = null;
        if ($id){
            $info = CurrencyProject::find($id);
        }

         $currencies =  Currency::where('isnew', '1', 1)->orderBy('sort','desc')->get();
         //$currencies2 =  Currency::where('isnew', '0', 0)->orderBy('sort','desc')->get();
         
         $currencies2 =  Currency::where('id', '=',23)->orderBy('sort','desc')->get();
       
        return view('admin.currency.project_detail')->with('currencies', $currencies)->with('currencies2',$currencies2)
            ->with('info',$info);
    }
}
