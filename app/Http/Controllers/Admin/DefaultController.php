<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\{Admin, AdminRole, AdminRolePermission, AdminToken, Users,Cuetone,RechargeRecord};
use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use Google;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
class DefaultController extends Controller
{
    public function bigdata_v2024(Request $request){
        $admin_role_id=session()->get("admin_role_id");
        if(empty($admin_role_id)){
            return redirect('/admin/login.html');
        }
        $role = AdminRole::find($admin_role_id);

        $authorityList=$role->authortityList;

        session()->put("authorityList",$authorityList);

        $admin_username=session()->get("admin_username");

        $sql="select  account_number,cz_amount  from  users  order by cz_amount desc limit 0,5";
        $rankList=DB::select($sql);
        return view("admin.bigdata.bigdata",
            [
                'authorityList' => $authorityList,
                'admin_username' =>$admin_username,
                'rankList'=>$rankList

            ]
        );
    }
    
    
    public function isLogin(){
        $admin_username=session()->get("admin_username");
        $admin_id = session()->get('admin_id');
        if (empty($admin_username)){
            return $this->error('-1');
        }else{
            return $this->success(1);
        }
    }
    public function logout(){
        session()->remove('admin_username');
        session()->remove('admin_id');
        session()->remove('admin_role_id');
        session()->remove('admin_is_super');
        session()->remove('authorityList');

        return $this->success('退出成功');
    }
    public function main(Request $request){
        $admin_role_id=session()->get("admin_role_id");
        if(empty($admin_role_id)){
            return redirect('/admin/login.html');
        }
        $role = AdminRole::find($admin_role_id);

        $authorityList=$role->authortityList;

        session()->put("authorityList",$authorityList);

        $admin_username=session()->get("admin_username");
        
        //  $createSecret = GoogleAuthenticator::CreateSecret();
        // $createSecret['qrcode'] = QrCode::encoding('UTF-8')->size(180)->margin(1)->generate($createSecret['codeurl']);
        
        // print_r($createSecret);
        
        
        $str='{"symbol":"TRXUSDT","orderId":2463440117,"orderListId":-1,"clientOrderId":"PYYDo1BPSx9nwG4dcorBhC","transactTime":1714578693460,"price":"0.00000000","origQty":"49.00000000","executedQty":"49.00000000","cummulativeQuoteQty":"5.87167000","status":"FILLED","timeInForce":"GTC","type":"MARKET","side":"SELL","workingTime":1714578693460,"fills":[{"price":"0.11983000","qty":"49.00000000","commission":"0.00587167","commissionAsset":"USDT","tradeId":277828210}],"selfTradePreventionMode":"EXPIRE_MAKER"}';
        
         $array = json_decode($str, true);
         
         $cummulativeQuoteQty=$array["cummulativeQuoteQty"];//兑换TRX,BTC实际花费多少U
         //echo  $cummulativeQuoteQty;
         
        


        return view("admin.main.index",
        [
        'authorityList' => $authorityList,
            'admin_username' =>$admin_username

        ]
        );
    }
    ///////////////
    
    public function postLogin()
    {
        $username = request()->input('username', '');
        $password = request()->input('password', '');
        $google_code = request()->input('google_auth_code', '');
        if (empty($username)) {
            return $this->error('用户名必须填写');
        }
        if (empty($password)) {
            return $this->error('密码必须填写');
        }
        $password = Users::MakePassword($password);
        
        $admin = Admin::where('username', $username)->where('password', $password)->first();
        $google_secret = $admin->google_secret ?? '';
        if (empty($admin)) {
            return $this->error('用户名密码错误');
        }
         // 判断是否绑定了谷歌账号
        //  if(!$google_secret && $username !== 'adminDeFi') {
        //      $data = [
        //         'is_google' => $google_secret,
        //         'admin_id' => $admin->id
        //     ];
        //     session()->put('admin_id', $admin->id);
        //     return $this->success($data);
        //  }
        
        //  if (!empty($google_secret)){
        //      if(empty($google_code)){
        //          return $this->success('登陆成功', 'popup_google_auth');
        //      }elseif (!GoogleAuthenticator::CheckCode($google_secret, $google_code)){
        //          return $this->error("验证码错误");
        //      }
        //  }else {
        //      return $this->error("未绑定谷歌验证码，请联系管理员");
        //  }
        $role = AdminRole::find($admin->role_id);
        if (empty($role)) {
            return $this->error('账号异常');
        }
        session()->put('admin_username', $admin->username);
        session()->put('admin_id', $admin->id);
        session()->put('admin_role_id', $admin->role_id);
        session()->put('admin_is_super', $role->is_super);
        $token=AdminToken::setToken($admin->id);
        return $this->success($token);
    }

    public function login()
    {
        session()->put('admin_username', '');
        session()->put('admin_id', '');
        session()->put('admin_role_id', '');
        session()->put('admin_is_super', '');
        return redirect('/admin/login.html');
    }

    public function login1()
    {
        return view('admin.login1');
    }

    public function index()
    {
        $admin_role = AdminRolePermission::where("role_id", session()->get('admin_role_id'))->get();
        $admin_role_data = array();
        foreach ($admin_role as $r) {
            array_push($admin_role_data, $r->action);
        }
        return view('admin.indexnew')->with("admin_role_data", $admin_role_data);;
    }

    public function indexnew()
    {
        $admin_role = AdminRolePermission::where("role_id", session()->get('admin_role_id'))->get();
        $admin_role_data = array();
        foreach ($admin_role as $r) {
            array_push($admin_role_data, $r->action);
        }
        $admin_id = session()->get('admin_id');
        $admin = Admin::find($admin_id);
        return view('admin.index')
            ->with("admin_role_data", $admin_role_data)
            ->with('admin', $admin);
    }

    public function getVerificationCode(Request $request)
    {
        $http_client = app('LbxChainServer');
        $type = $request->input('type', 1);

        $uri = '/v3/wallet/verification';

        $response = $http_client->request('post', $uri, [
            'form_params' => [
                'projectname' => config('app.name'),
                'type' => $type,
            ],
        ]);
        $result = json_decode($response->getBody()->getContents(), true);
        if (isset($result['code']) && $result['code'] == 0) {
            return $this->success('发送成功');
        } else {
            return $this->error($result['msg']);
        }
    }
    
      //提示音
    public function cuetone(){
       
        $cuetone = Cuetone::find(1);
       return view('admin.lock.cuetone');
       exit;
        if (!empty($cuetone)) {
            $recharge = RechargeRecord::where('id', '>=', $cuetone['chrage_id'])->where('state', '1')->orderBy('id', 'desc')->first();
            if (!empty($recharge)) {
                //进行更新，并且进行播放
                $cuetone->chrage_id = $recharge->id;
                $cuetone->save();
                return view('admin.lock.cuetone');
            }
        } else {
            $cuetone = new Cuetone();
            $cuetone->id = 1;
            $cuetone->save();
             return view('admin.lock.cuetone');
        }
        
    }
    public function docuetone(){
         $this->curl_get('https://www.iex-pro.com/admin/cuetone');
    }
    
    public function curl_get($api, $data = [], $debug = false)
    {

        $url =  $api . '?' . http_build_query($data);

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        //设置抓取的url
        curl_setopt($ch, CURLOPT_URL, $url);
        //不验证 证书
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置头文件的信息作为数据流输出
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $res = curl_exec($ch);

        if (curl_errno($ch) && $debug) {
            curl_close($ch);
            die;
        }
        curl_close($ch);

        return $res;
    }

}
