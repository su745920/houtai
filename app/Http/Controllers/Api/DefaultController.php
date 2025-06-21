<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Utils\RPC;
use App\Models\{AppVersion, Bank, Setting, Token, Users,UsersWallet,MarketHour,Menu,Menu2};
use App\DAO\UploaderDAO;
use App\Jobs\{LeverUpdate, SendMarket};
use App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;



class DefaultController extends Controller
{
    
    public function updateOrderFn2(Request $request){

        $raw_data = '{"code":0,"message":"success","body":{"charset":"utf-8","sign":"arbmq36OaP%2F1OnjcDQVyHa3QZHH6I1PdClqA6wBpG9kqaaMo1%2FY75sFVfSoGQotcaG1WxnD6%2Fh4MPWAgwLoTEdAPKO2A3wI1k0uBo%2FJAkpTTgSxiHFdsIHNmOoG8CX%2B52kJZ3ETvmdz1MruU7UYGV97VQtUWzCPWxkdqIUTl8GE%3D","method":"tigerpay.trade.wap","version":"1.0.0","outTradeNo":"2024050810210148","data":"MasJtSM%2BraueUE2RD6CWV%2BG%2FgXqCfGSIQf7O9%2B0XQY9MV55LrfGr8YPOBLR3jLeT4IKNwT%2BgrE9R7nCl1UrowYzsTQT0fspOpGPlzmi0QXKzJ0%2BfVhSpSHVTuJ0kXloJSabQiw2a6cKhXSUXroqxOXym4SigsuSh9nthmzvP5X8vNp3eDo3syfwu9iopT92MfRQRNaIw7v3BE0tHY4s%2B7AuQm4Q%2BaRfxKLQ5Esm9WYGUI3zPQvDU9nxOSNXxdwFrf3mLU6GnsM%2FUwuwIPxljIxyCmar82s7tLszlW8JFaK33U5Rrrcp%2FvIIVhMIxlT02XK8SuQnOU1lVF%2Fu%2FpROiFqWVlLDvvExjHhYcyuwT2TEfiIX1tJ9niTVlhEwIOcqdiWqCaw7m4EbNA0OfqgCAGtBfei8gWkT0vYg28DgBvJ9V1BaQoG64ncAUd6TyewA3COBXwXZZLKH4ynmIcdq%2B7O55z7ENfzsew3wJ18C8i3ifCgY0hOd%2BT%2BJUoAHP66xx"}}';

        $path = base_path() . '/storage/logs/test/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . '---回调测试--'. PHP_EOL, 3, $path . $filename);

        error_log(date('Y-m-d H:i:s') . $raw_data. PHP_EOL, 3, $path . $filename);

        $serverUrl = "https://api.in.dvbfservice.com/trade";
        $appId="1712884112930";
        $APPPrivateKEY='MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALwM33cgp7BdRcnHyLXSnFyT1RVvhUSENs34sLOnQlBmXGAsZu7AskNDrr9SUZPmeR3aPHClfyBjnixrYst0XcY1JkDM0It0J0n4FU9QmzBVdi+KEX5fyICBtCafrU/WHFCP+A5mUTb8LLWmVNKEVApr40zy/7or8FWg9E8W8JvFAgMBAAECgYEAne7tyv4Y960OTK5GqjXs7m+WGT+lHGsyIACKXwfNUPr3ACqqdEBQNt/kJni7XMzG0cSU2EKWJxyjHkk+GwNrMPZo8JB1CEhE+REpfNFiXa2CfULctTD1ReJ3ScIY2QSLw1WcLtz6Jv9k76VKTw9z7caMTBRUomS4Fnj80KXb80ECQQD1G9OKU/FZeFd6ZnOuOOTTOXmD06DdcgrUWfos+vv8ghEv8qNP/EwtTUwZRGO551n4Lo4EtqHfJmJ/1Q0doeMRAkEAxGf+Hs9oBR/cIfFNp/WyVwq0JfsTVm3s0zYSJ1ineq4AUZTxmH33vq6/dcWU0t3Hd8tDb+YBRoWJP0rUqTyFdQJBAOMs5UYCQ2GPflS9/F3v8XYsgQnD7gcRGiRzOMLT5RXGX7O3CdKqntgmGnAYyO2XFfZG/+4yb5lp9EVS7BMY5mECQFCanR/n3ri7qowDi+syWk5+hhBQSk9eLaNAvZKIP7OJVXgluEDs8Y/AB7M9syYW/pWbRcHWkw9uHjVrcHtD60ECQFQa/QIOvQ+np7LXcIBi8kfc93YM9rgXRyy31gyJi3NjxLi9z9CE2Ulr9XGowz+9Fi1g85+CGNn3eYSbqR6tIls=';
        $ServerPublicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCzykwv0WC7wBIFFX5ewkiuViWgvHeLTAjERFgFBF+wqXEbGC42dxOIgD4bQD5kYmhiPc1mgd1ghSLzPaHQROvuxhRc2kjI3fEvtg1jwc37sa+nDPxgj8HlJONvERtIniqWdrUjv3tc9b38SIYilt4d4LGMJpfdcAd76nyyJKX1twIDAQAB';

        $client = new TigerpayClient($serverUrl, $appId, $APPPrivateKEY, $ServerPublicKey);

        $body = json_decode($raw_data)->body;
        $jsonStr= json_encode($client->checkResponse($body));

        error_log(date('Y-m-d H:i:s') . '验签后==>'.$jsonStr. PHP_EOL, 3, $path . $filename);
    }
    
    
    public function updateOrderFn(Request $request){
         //$body=$request->input("body");
        $raw_data = file_get_contents("php://input");

        $path = base_path() . '/storage/logs/test/';
        $filename = date('Ymd') . '.log';
        file_exists($path) || @mkdir($path);
        error_log(date('Y-m-d H:i:s') . '---回调测试--'. PHP_EOL, 3, $path . $filename);

        error_log(date('Y-m-d H:i:s') . $raw_data. PHP_EOL, 3, $path . $filename);


    }
    
    
    //  https://binancb.com/setCNYFn
    public function setCNYFn(){
        $usdtrateurl = 'https://api.coinbase.com/v2/exchange-rates?currency=USDT';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $usdtrateurl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_json = json_decode($response);
        $usdtrate = $response_json->data->rates->CNY;
        //echo "USDT to CNY rate: " . $usdtrate;
        $usdtrate=number_format($usdtrate, 2, '.', '');
        Redis::set('CNY', $usdtrate);
    }

    //  https://binancb.com/getCNYFn
    public function getCNYFn(){
        $cny=Redis::get('CNY');
        return $this->success($cny);
    }
    public function getWebSiteConfig()
    {


        $title=Setting::getValueByKey("web_site_title");
        $desc=Setting::getValueByKey("web_site_desc");
        $keyword=Setting::getValueByKey("web_site_keyword");
        $kefu=Setting::getValueByKey("service_url");
        $telegram=Setting::getValueByKey("telegram_url");

        $jo["title"]=$title;
        $jo["desc"]=$title;
        $jo["keyword"]=$keyword;
        $jo["kefu"] = $kefu;
        $jo["telegram"] = $telegram;


        return $this->success($jo);

    }
    
    
   
    
    
    public function getAppConfig(){
        $key = request()->input('key', '');
        return $this->success(Setting::where('key',$key)->get());
    }
    
    public function getRegConfig(){
        $invite_code_must=Setting::getValueByKey("invite_code_must");
        $mobile_must =Setting::getValueByKey("mobile_must");;

        $jo["mobile_must"]=$mobile_must;
        $jo["invite_code_must"]=$invite_code_must;
        return $this->success($jo);
    }
    
    
    public function getMenu2()
    {
        $menu = Menu2::where('show', 1)->orderBy('sort','asc')->get();
        return $this->success($menu);
    }
    
    public function getMenu()
    {
        $menu = Menu::where('show', 1)->orderBy('sort','asc')->get();
        return $this->success($menu);
    }
    
    
    
    
    
    public function jumpDist()
    {
        
        $result = MarketHour::deleteEsearchMarketByQuery('SHWB','USDT','1mon','1648742400','1650643200');
        var_dump($result);die;
        UsersWallet::makeWallet(95);
        var_dump(11);die;
        $i = 1;
        $a = [1,2,3,4,5,6,7,8,9];
        foreach($a as $key =>$value){
            
            $i++;
            var_dump($i);
        }
        var_dump($i);die;
        
        var_dump(1);die;
        $result = MarketHour::getEsearchMarketById('BTC','USDT','1mon',1649952000,1627747200);
        //$result = MarketHour::getEsearchMarketById('BTC','USDT','1mon',1619798400);
        
        //MarketHour::deleteEsearchMarketById('BTC','USDT','1min',1621699200);
        // $symbol= array(
        //     'a' => 1
        // );
        // SendMarket::dispatch($symbol)->onQueue('kline.all');
        var_dump($result);die;
        //return redirect('/dist');
    }
    public function getRechargeCount()
    {
        //Redis::set('recharge_tip_count', 1);
        $count = Redis::get("recharge_tip_count");
        
        Redis::set('recharge_tip_count', 0);
        
        $count1 = Redis::get("rel_tip_count");
        
        Redis::set('rel_tip_count', 0);
        
        $count2 = Redis::get("cashb_tip_count");
        
        Redis::set('cashb_tip_count', 0);
        
        $count3 = Redis::get("micro_tip_count");
        
        Redis::set('micro_tip_count',0);
       
        
        return $this->success('成功',["tip_count"=>$count,'tip_count1'=>$count1,'tip_count2'=>$count2,'tip_count3'=>$count3]);
    }
    
    // public function getRechargeCounts()
    // {
    //     //Redis::set('recharge_tip_count', 1);
    //     $count = Redis::get("recharge_tip_counts");
    //     Redis::set('recharge_tip_counts', 0);
    //     return $this->success('成功',["tip_count"=>$count]);
    // }
    
    
   
    
    // public function getCashbCount()
    // {
    //     //Redis::set('recharge_tip_count', 1);
    //     $count = Redis::get("cashb_tip_counts");
    //     Redis::set('cashb_tip_counts', 0);
    //     return $this->success('成功',["tip_count"=>$count]);
    // }
    public function getLang()
    {
        return response()->json([
            'type' => 'ok',
            'message' => session()->get('lang'),
            'session_id' => session()->getId(),
        ]);
    }

    // public function setLang()
    // {
    //     $lang = request()->input('lang', '');
    //     if ($lang == '') {
    //         return response()->json([
    //             'type' => 'error',
    //             'message' => 'Error: lang cannot be empty',
    //         ]);
    //     }
    //     session()->put('lang', $lang);
        
    //     $token = Token::getToken();
    //     if (!empty($token)) {
    //         Token::setTokenLang($lang);
    //     }
    //     return response()->json([
    //         'type' => 'ok',
    //         'message' => 'Switch lange success!(' . session()->get('lang') . ')',
    //         'session_id' => session()->getId(),
    //     ]);
    // }
  public function setLang(Request $request)
    {
    //     $user_id = Users::getUserId();
    //     $key = 'lan_type_'.$user_id;
    //     //var_dump($user_id);
    //     $type = $request->input('lang', 'zh_cn');
    //     // var_dump($type);
    //     if($type == 'zh'){
    //       $type = 'zh_cn';  
    //     }
    //      //var_dump(Cache::get($key));
    //     if($type != Cache::get($key)){

    //         Cache::put($key,$type,24 * 3600 * 365);
             
    //     }
    //     // var_dump($type);
    //     // var_dump($user_id);
    //   App::setLocale($type);
        //exit;
       
       
        return response()->json([
            'type' => 'ok',
            'message' => 'Switch lange success!(' . session()->get('lang') . ')',
        ]);
    }
    public function setLang1(Request $request)
    {
        $user_id = Users::getUserId();
        if($this->isMobile()){
            $duan_type = 'm';
        }else{
            $duan_type = 'pc';
        }
        //var_dump($user_id);
        $key = 'lan_type_'.$duan_type.$user_id;
        //var_dump($user_id);
        //var_dump($user_id);
        $type = $request->input('lang', 'en');
        // var_dump($type);
        if($type == 'zh'){
          $type = 'zh_cn';  
        }
         //var_dump(Cache::get($key));
        //if($type != Cache::get($key)){
       
        Cache::put($key,$type,24 * 3600 * 365);
            //var_dump(1111);
             
       // }
        // var_dump($type);
        // var_dump($user_id);
       App::setLocale($type);
        //exit;
       
       
        return response()->json([
            'type' => 'ok',
            'message' => 'Switch lange success!(' . Cache::get($key) . ')',
        ]);
    }
    public function env()
    {
        $result = file_get_contents('./env.json');
        return response()->json(json_decode($result, true));
    }

    public function dataGraph()
    {
        $data = Setting::getValueByKey("chart_data");
        if (empty($data)) return $this->error("暂无数据");

        $data = json_decode($data, true);
        return $this->success(
            array(
                "data" => array(
                    $data["time_one"], $data["time_two"], $data["time_three"], $data["time_four"], $data["time_five"], $data["time_six"], $data["time_seven"]
                ),
                "value" => array(
                    $data["price_one"], $data["price_two"], $data["price_three"], $data["price_four"], $data["price_five"], $data["price_six"], $data["price_seven"]
                ),
                "all_data" => $data
            )
        );
    }

    public function index()
    {
        $coin_list = RPC::apihttp("https://api.coinmarketcap.com/v2/ticker?limit=10");
        $coin_list = @json_decode($coin_list, true);
        if (!empty($coin_list["data"])) {
            foreach ($coin_list["data"] as &$d) {
                if ($d["total_supply"] > 10000) {
                    $d["total_supply"] = substr($d["total_supply"], 0, -4) . "万";
                }
            }
        }
        return $this->success(
            array(
                "coin_list" => $coin_list["data"]
            )
        );
    }

    public function upload(Request $request)
    {
        
        $file = $request->file('file');
        
        $scene = $request->input('scene', ''); //场景,子文件夹
        if (!$file) {
            return $this->error('文件不存在');
        }
        
        //文件类型验证
        $validator = Validator::make($request->all(), [
            'file' => 'required|image',
        ], [], [
            'file' => '上传附件',
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $result = UploaderDAO::fileUpload($file, $scene);
        if ($result['state'] != 'SUCCESS') {
            return $this->error($result['state']);
        }
        return $this->success($result['url']);
    }

    public function getNode(\Illuminate\Http\Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $show_message["real_teamnumber"] = Users::find($user_id)->real_teamnumber;
        $show_message["top_upnumber"] = Users::find($user_id)->top_upnumber;
        $account_number = $request->input('account_number', null);
        if (!empty($account_number)) {
            $user_id_search = Users::where('account_number', $account_number)->first();
            if (!empty($user_id_search)) {
                $user_id = $user_id_search->id;
            } else {
                $user_id = 0;
            }
        }
        $users = Users::where('parent_id', $user_id)->get();
        $results = array();
        foreach ($users as $key => $user) {
            $results[$key]['name'] = $user->account_number;
            $results[$key]['id'] = $user->id;
            $results[$key]['parent_id'] = $user->parent_id;
        }
        $data["show_message"] = $show_message;
        $data["results"] = $results;
        return $this->success($data);
    }

    public function getVersion()
    {
        $version = Setting::getValueByKey('app_name', '');
        return $this->success($version);
    }

    public function getBanks()
    {
        $result = Bank::all();
        return $this->success($result);
    }

    public function checkUpdate(Request $request)
    {
        $name = $request->input('name', '');
        $version = $request->input('version', '');
        $os = strtolower($request->input('os', 'android') ?? 'android');
        $type = $os == 'android' ? 1 : 2;
        try {
            $app_version = AppVersion::where('type', $type)
                ->orderBy('version_num', 'desc')
                ->firstOrFail();
            if (version_compare($app_version->version_name, $version) > 0) {
                list($main_version) = explode('.', $version);
                list($app_main_version) = explode('.', $app_version->version_name);
                $main_version = intval($main_version);
                $app_main_version = intval($app_main_version);
                if ($app_main_version > $main_version) {
                    $pkg_url = $app_version->pkg_url;
                    $wgt_url = '';
                } else {
                    $pkg_url = '';
                    $wgt_url = $app_version->wgt_url;
                }
                return [
                    'code' => 0,
                    'msg' => $os . '发现新版本',
                    'data' => [
                        'update' => true,
                        'wgtUrl' => $wgt_url,
                        'pkgUrl' => $pkg_url,
                        'downUrl' => $app_version->down_url,
                    ],
                ];
            } else {
                throw new \Exception('您的App已经是最新版本');
            }
        } catch (\Throwable $th) {
            return [
                'code' => 0,
                'msg' => $th->getMessage(),
                'data' => [
                    'update' => false,
                    'wgtUrl' => '',
                    'pkgUrl' => '',
                    'downUrl' => $app_version->down_url ?? '',
                ],
            ];
        }
    }

    public function base64ImageUpload(Request $request)
    {
        $base64_image_content = $request->input('base64_file', '');
        $res = self::base64ImageContent($base64_image_content);
        if (!$res) {
            return $this->error('上传失败');
        }
        return $this->success($res);
    }

    public static function base64ImageContent($base64_image_content)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            if (!in_array($type, ['jpg', 'jpeg', 'png',])) {
                return false;
            }
            $path = '/upload/' . date('Ymd') . '/';
            $new_file  = public_path() . $path;
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $filename = time() . rand(0, 999999) . ".{$type}";
            $full_file = $new_file . $filename;
            if (file_put_contents($full_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return $path . $filename;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public function makewallet(Request $request){
        UsersWallet::makewallet($request->user_id);
    }
    
    public function getlogo(){
        return $this->success(Setting::whereIn('key',['logo1','logo2'])->get());
    }
    
     //判断是否是移动端访问
    public function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return TRUE;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE;// 找不到为flase,否则为TRUE
        }
        // 判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'mobile',
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return TRUE;
            }
        }
        if (isset ($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return TRUE;
            }
        }
        return FALSE;

    }
}
