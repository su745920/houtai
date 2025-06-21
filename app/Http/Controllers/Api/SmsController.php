<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use PHPMailer\PHPMailer\PHPMailer;
use App\Utils\RPC;
use App\Models\{SmsProject, Setting, Users};
use App;
use PHPMailer\PHPMailer\Exception;
use Mail;
class SmsController extends Controller
{
public function sendMail4Login(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

        $email = $request->input('user_string');
        //登录之前发送验证 需要先检查email是否存在
        $user = Users::getByString($email);
        if (empty($user)) {
            return $this->error(trans('login.zhbcz'));
        }
        $google_secret=$user->google_secret;
        if (!empty($google_secret)&&strlen($google_secret)>8){
            $jo["hasGoogle"]=1;
            return $this->success($jo);
        }


        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //服务器配置
            $mail->CharSet ="UTF-8";                     //设定邮件编码
            $mail->SMTPDebug = 0;                        // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = 'smtp.gmail.com';                // SMTP服务器
            $mail->SMTPAuth = true;                      // 允许 SMTP 认证

//            $mail->Username = 'hksg1900@gmail.com';                // SMTP 用户名  即邮箱的用户名
//            $mail->Password = 'smpuwtzfevotrscc';             // SMTP 密码  部分邮箱是授权码(例如163邮箱) Gmail是应用专用密码

            $mail->Username = 'deepcoine@gmail.com';                // SMTP 用户名  即邮箱的用户名
            $mail->Password = 'zsutnvodqopbmdkg';             // SMTP 密码  部分邮箱是授权码(例如163邮箱) Gmail是应用专用密码

            $mail->SMTPSecure = '';                    // 允许 TLS 或者ssl协议
            $mail->Port = 25;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

            $mail->setFrom('deepcoine@gmail.com', 'Mailer');  //发件人
            $mail->addAddress($email, 'Joe');  // 收件人
            //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
            $mail->addReplyTo('deepcoine@gmail.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
            //$mail->addCC('cc@example.com');                    //抄送
            //$mail->addBCC('bcc@example.com');                    //密送

            //发送附件
            // $mail->addAttachment('../xy.zip');         // 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名
            $verification_code = $this->createSmsCode(4);
            
            //$verification_code = "123456";
            
            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = 'Sign-up email code';


            $emailContent="Thanks for creating an account with bicn.";
            $emailContent=$emailContent."</br>";
            $emailContent=$emailContent."Here's your verification code to complete the registration:";
            $emailContent=$emailContent."</br>";
$emailContent=$emailContent."<h1>".$verification_code."</h1>";
            $emailContent=$emailContent."</br>";


$emailContent=$emailContent."This code is valid for 10 minutes, do not share the code with anyone. Not you? Change your password and contact customer support immediately to freeze your account.";
            $emailContent=$emailContent."</br>";


$emailContent=$emailContent."Regards,";
            $emailContent=$emailContent."</br>";

$emailContent=$emailContent."bicn Team";

$mail->Body    = $emailContent;
            
            
            


            //session()->put('code@' . $email, $verification_code);
            Cache::put('code@' . $email, $verification_code, 300);

            $mail->send();

            $jo["hasGoogle"]=0;
            $jo["msg"]="Send Success";
            return $this->success($jo);
            //return $this->success("Send Success");

        } catch (Exception $e) {
            echo '邮件发送失败: ', $mail->ErrorInfo;
        }

    }
    public function send_mail_message(Request $request)
    {
        $lang = request()->input('lang','en');
        $type = request()->input('type','register');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }

       $email = $request->input('user_string');
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
            //服务器配置
            $mail->CharSet ="UTF-8";                     //设定邮件编码
            $mail->SMTPDebug = 0;                        // 调试模式输出
            $mail->isSMTP();                             // 使用SMTP
            $mail->Host = 'smtp.coinbmex.com';                // SMTP服务器
            $mail->SMTPAuth = true;                      // 允许 SMTP 认证
            $mail->Username = 'support@coinbmex.com';                // SMTP 用户名  即邮箱的用户名
            $mail->Password = 'Azhi.1688';             // SMTP 密码  部分邮箱是授权码(例如163邮箱) Gmail是应用专用密码
            // $mail->SMTPSecure = 'ssl';                    // 允许 TLS 或者ssl协议
            
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            
            $mail->Port = 25;                            // 服务器端口 25 或者465 具体要看邮箱服务器支持

            $mail->setFrom('support@coinbmex.com', 'Mailer');  //发件人
            $mail->addAddress($email, 'Joe');  // 收件人
            //$mail->addAddress('ellen@example.com');  // 可添加多个收件人
            $mail->addReplyTo('support@coinbmex.com', 'info'); //回复的时候回复给哪个邮箱 建议和发件人一致
            //$mail->addCC('cc@example.com');                    //抄送
            //$mail->addBCC('bcc@example.com');                    //密送

            //发送附件
            // $mail->addAttachment('../xy.zip');         // 添加附件
            // $mail->addAttachment('../thumb-1.jpg', 'new.jpg');    // 发送附件并且重命名
            $verification_code = $this->createSmsCode(4);
            //Content
            $mail->isHTML(true);                                  // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
            $mail->Subject = 'Sign-up email code';


            $emailContent="Thanks for creating an account with Deepcoine.";
            $emailContent=$emailContent."</br>";
            $emailContent=$emailContent."Here's your verification code to complete the registration:";
            $emailContent=$emailContent."</br>";
            $emailContent=$emailContent."<h1>".$verification_code."</h1>";
            $emailContent=$emailContent."</br>";


            $emailContent=$emailContent."This code is valid for 10 minutes, do not share the code with anyone. Not you? Change your password and contact customer support immediately to freeze your account.";
            $emailContent=$emailContent."</br>";


            $emailContent=$emailContent."Regards,";
            $emailContent=$emailContent."</br>";

            $emailContent=$emailContent."Bibiom Team";
            
            if($type == 'bind') {
                $mail->setFrom('support@coinbmex.com', 'Coinbm');  //发件人
                $mail->Subject = 'Coinbm Email Verification Code';
                
    		    $emailContent = '
    		    <div style=";text-align: center;display: none;">
			    <img style="height: 70px;" src="https://okx.huayi.me/bg_logo11111.png" />
    			</div>
    			<p style="font-weight: 700;margin-top: 6px;font-size: 18px;color: #838181;">Dear Valued Customer,</p>
    			<p style="margin-top: 20px;font-size: 16px;color: #838181;">You are linking your email address to Coinbm. Here is your verification code:</p>';
    			$emailContent=$emailContent.'<h3 style="font-size: 50px;color: #0F1C39;font-weight: 700;text-align: center;margin:0;margin-top: 20px;">'.$verification_code.'</h3>';
    			$emailContent=$emailContent.'<p style="font-size: 16px;color: #838181;">Please note: This one-time code will expire in 5 minutes.</p>
    			<p style="margin-top: 40px;font-size: 16px;color: #838181;">If you did not request the code, please ignore this email or reach out to us at <a href="mailto:support@coinbmex.com">support@coinbmex.com</a>.</p>
    			<p style="margin-top: 40px;font-size: 16px;color: #838181;">This is an automated message, please do not reply!</p>
    			<p style="margin:0;margin-top: 40px;font-size: 20px;color: #838181;">Best Regards,</p>
    			<p style="margin:0;font-size: 20px;color: #838181;">Coinbm Team</p>';
                
                // $emailContent = '</br><p>Dear Valued Customer,</p>';
                // $emailContent = $emailContent.'</br></br><p>You are linking your email address to Coinbm Crypto. Here is your </br> verification code: </p>';
                // $emailContent=$emailContent."</br></br>";
                // $emailContent=$emailContent."<p>".$verification_code."</p>";
                // $emailContent=$emailContent."</br></br>";
                // $emailContent=$emailContent."<p>Please note: This one-time code will expire in 5 minutes.</p>";
                // $emailContent=$emailContent."</br></br>";
                // $emailContent=$emailContent."<p>If you did not request the code, please ignore this email or reach out to us at <a href='mailto:support@coinbmex.com'>support@coinbmex.com</a>.</p>";
                // $emailContent=$emailContent."<p style='margin-top: 50px;'>This is an automated message, please do not reply!</p>";
                // $emailContent=$emailContent."<p style='margin-top: 50px;'>Best Regards,</p>";
                // $emailContent=$emailContent."<p>Coinbm Crypto Team</p>";
                // $emailContent=$emailContent."</br>";
            }
            if($type == 'withdraw') {
                $mail->setFrom('support@coinbmex.com', 'Coinbm');  //发件人
                $mail->Subject = 'Coinbm Email Verification Code';
                $emailContent = '
    		    <div style=";text-align: center;display: none;">
			    <img style="height: 70px;" src="https://okx.huayi.me/bg_logo11111.png" />
    			</div>
    			<p style="font-weight: 700;margin-top: 6px;font-size: 18px;color: #838181;">Dear Valued Customer,</p>
    			<p style="margin-top: 20px;font-size: 16px;color: #838181;">Your Coinbm account is initiating a withdrawal. Here is your verification code:</p>';
    			$emailContent=$emailContent.'<h3 style="font-size: 50px;color: #0F1C39;font-weight: 700;text-align: center;margin:0;margin-top: 20px;">'.$verification_code.'</h3>';
    			$emailContent=$emailContent.'<p style="font-size: 16px;color: #838181;">Please note: This one-time code will expire in 5 minutes.</p>
    			<p style="margin-top: 40px;font-size: 16px;color: #838181;">If you did not request the code, please ignore this email or reach out to us at <a href="mailto:support@coinbmex.com">support@coinbmex.com</a>.</p>
    			<p style="margin-top: 40px;font-size: 16px;color: #838181;">This is an automated message, please do not reply!</p>
    			<p style="margin:0;margin-top: 40px;font-size: 20px;color: #838181;">Best Regards,</p>
    			<p style="margin:0;font-size: 20px;color: #838181;">Coinbm Team</p>';
                // $emailContent = '</br><p>Dear Valued Customer,</p>';
                // $emailContent = $emailContent.'</br></br><p>Your Coinbm Crypto account is initiating a withdrawal. Here is your verification code: </p>';
                // $emailContent=$emailContent."</br></br>";
                // $emailContent=$emailContent."<p>".$verification_code."</p>";
                // $emailContent=$emailContent."</br></br>";
                // $emailContent=$emailContent."<p>Please note: This one-time code will expire in 5 minutes.</p>";
                // $emailContent=$emailContent."</br></br>";
                // $emailContent=$emailContent."<p>If you did not request the code, please ignore this email or reach out to us at <a href='mailto:support@coinbmex.com'>support@coinbmex.com</a>.</p>";
                // $emailContent=$emailContent."<p style='margin-top: 50px;'>This is an automated message, please do not reply!</p>";
                // $emailContent=$emailContent."<p style='margin-top: 50px;'>Best Regards,</p>";
                // $emailContent=$emailContent."<p>Coinbm Crypto Team</p>";
                // $emailContent=$emailContent."</br>";
            }

            $mail->Body    = $emailContent;
            
            //session()->put('code@' . $email, $verification_code);
            
            Cache::put('code@' . $email, $verification_code, 300);
           
            $result = $mail->send();
            
            $session_code = Cache::get('code@' . $email);
            
            return $this->success("Send Success");
        
        } catch (Exception $e) {
            echo '邮件发送失败: ', $mail->ErrorInfo;
        }

    }
    use Notifiable;
    private $_sms_ip_check_expire_time = 60;
    /**
     * 发送短信
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $ALIYUN_SMS_AK = env("ALIYUN_SMS_AK");
        $ALIYUN_SMS_AS = env("ALIYUN_SMS_AS");
        $ALIYUN_SMS_SIGN_NAME = env("ALIYUN_SMS_SIGN_NAME");
        $ALIYUN_SMS_VARIABLE = env("ALIYUN_SMS_VARIABLE");  //内容变量
        $tplId = env('ALIYUN_SMS_CODE');                //模版ID 模版CODE 格式为 SMS_140736882

        if (empty($tplId) || empty($ALIYUN_SMS_AK) || empty($ALIYUN_SMS_AS) || empty($ALIYUN_SMS_SIGN_NAME) || empty($ALIYUN_SMS_VARIABLE)) {
            return $this->error('系统配置错误，请联系系统管理员');
        }
        Config::set("aliyunsms.access_key", $ALIYUN_SMS_AK);
        Config::set("aliyunsms.access_secret", $ALIYUN_SMS_AS);
        Config::set("aliyunsms.sign_name", $ALIYUN_SMS_SIGN_NAME);
        $mobile = request()->input('mobile', '');
        if (empty($mobile)) {
            return $this->error('手机号不能为空');
        }
        //检查1分钟内该ip是否发送过验证码
        // if ($this->checkSmsIp($request->ip().$mobile)) {
        //     return $this->error('验证码发送过于频繁');
        // }
        $verification_code = $this->createSmsCode(6);
        $params = [
            $ALIYUN_SMS_VARIABLE => $verification_code
        ];

        try {
            $smsService = \App::make('Curder\LaravelAliyunSms\AliyunSms');
            $return = $smsService->send(strval($mobile), $tplId, $params);

            if ($return->Message == "OK") {
                //记入session
                session(['sms_captcha' => $verification_code]);
                session(['sms_mobile' => $mobile]);

                //设置缓存key
                //$this->setSmsIpKey($request->ip().$mobile, $mobile);
                return $this->success("发送成功");
            } else {
                return $this->error($return->Message);
            }
        } catch (\ErrorException $e) {
            return $this->error($e->getMessage());
        }
    }
    /**
     * 发送短信
     */
    public function smsSend(Request $request)
    {
        $mobile = $request->input('user_string', '');
        $country_code = $request->input('country_code', 86);
        $scene = $request->input('scene', '');
        $scene_list = SmsProject::enumScene();
        $region_list = SmsProject::enumRegion();
        $country_code = str_replace('+', '', $country_code);
        if (Cache::has("{$mobile}@$country_code")) {
            return $this->error(trans('sms.qwcfdj'));
        }
        // if (empty($scene) || !in_array($scene, array_keys($scene_list))) {
        //     return $this->error(trans('sms.dxcjcw'));
        // }
        // if (empty($country_code) || !in_array($country_code, array_keys($region_list))) {
        //     return $this->error(trans('sms.gjqydmcw'));
        // }
        if (empty($mobile)) {
            return $this->error(trans('sms.dhhmbnwk'));
        }
        // $username = Setting::getValueByKey('smsBao_username', '');
        // $password = Setting::getValueByKey('password', '');
        // $sms_signature = Setting::getValueByKey('sms_signature', '【签名】');
        $sms_signature = "【VELIB】";
        $appkey = "3mYhCSdX";
        $secretkey = "HzaUc1QJ";
        $verification_code = $this->createSmsCode(4);
        
        $api = "";
        $content = "";
        if($country_code == "+86"){
            $content = $sms_signature . 'Your verification code is [' . $verification_code . '], If it is not done by yourself, please ignore this message.';
            $api = 'http://api.wftqm.com/api/sms/mtsend';
            $mobile = "+".$country_code.$mobile;
        }
        else
        {
            $content = $sms_signature . 'Your verification code is [' . $verification_code . '], If it is not done by yourself, please ignore this message.';
            $api = 'http://api.wftqm.com/api/sms/mtsend';
            $mobile = "+".$country_code.$mobile;
        }
        $data['appkey'] = $appkey;
        $data['secretkey'] = $secretkey;
        $data['phone'] = $mobile;
        $data['content'] = $content;
        
       
        $return_message = RPC::apihttp($api,"POST",$data);
        $return_message = json_decode($return_message,true);
        if ($return_message['code'] == 0) {
            Cache::put('code@' . $country_code . $mobile, $verification_code, 300);
            
            return $this->success('Send success');
        } else {
            $statusStr = array(
                "1" => '应用不可用或key错误',
                "2" => '参数错误或为空',
                "3" => '余额不足',
                "4" => '内容为空或包含非法关键词',
                "5" => '内容过长',
                "6" => '号码有误',
                "7" => '群发号码数量不得超过50000个',
                "8" => 'sourceaddress必须为3-10位数字或英文字母',
                "9" => 'IP非法',
                "88" => '请求失败',
                "99" => '系统错误',
            );
            return $this->error($statusStr[$return_message['code']]);
        }
    }
    /**
     * 发送短信
     */
    public function smsSend111(Request $request)
    {
        $mobile = $request->input('user_string', '');
        $country_code = $request->input('country_code', 86);
        $scene = $request->input('scene', '');
        $scene_list = SmsProject::enumScene();
        $region_list = SmsProject::enumRegion();
        $country_code = str_replace('+', '', $country_code);
        if (Cache::has("{$mobile}@$country_code")) {
            return $this->error('请勿重复点击');
        }
        if (empty($scene) || !in_array($scene, array_keys($scene_list))) {
            return $this->error('短信场景错误');
        }
        if (empty($country_code) || !in_array($country_code, array_keys($region_list))) {
            return $this->error('国际区域代码错误');
        }
        if (empty($mobile)) {
            return $this->error('电话号码不能为空');
        }
        $has_user_scene_list = [
            'login',
            'change_password',
            'reset_password',
        ];
        if (in_array($scene, $has_user_scene_list)) {
            // 修改密码直接从已经登录的session中取用户
            if ($scene == 'change_password') {
                $user_id = Users::getUserId();
                $user = Users::findOrFail($user_id);
                $country_code = $user->country_code;
            } else {
                $user = Users::getByString($mobile, $country_code);
            }
            if (empty($user)) {
                return $this->error('账号不存在');
            }
        } else {
            $user = Users::getByString($mobile, $country_code);
            if (!empty($user)) {
                return $this->error('账号已存在');
            }
        }
        //取短信模板,如果不存在则取默认
        $sms_project = SmsProject::where('scene', $scene)->get();
        if (count($sms_project) <= 0) {
            return $this->error('短信模板不存在');
        }
        $project = $sms_project->where('country_code', $country_code)->first();
        $project || $project = $sms_project->where('is_default', 1)->first();
        if (!$project) {
            return $this->error('短信模板不存在');
        }
        if (empty($mobile)) {
            return $this->error('请填写手机号');
        }
        $content = $project->project ?? $project->contents; //赛邮只需要模板id,不需要拼接内容
        $verification_code = $this->createSmsCode(6);
        session()->put('code@' . $country_code . $mobile, $verification_code);
        $class_name = '\\App\Notifications\\';
        if ($scene == 'register') {
            $class_name .= 'UserRegisterCode';
        } elseif ($scene == 'login') {
            $class_name .= 'UserLoginCode';
        } elseif ($scene == 'change_password') {
            $class_name .= 'ChangePasswordCode';
        } elseif ($scene == 'reset_password') {
            $class_name .= 'ResetPasswordCode';
        }
        $notification = new $class_name($mobile, $content, ['code' => $verification_code], $country_code);
        try {
            $this->notify($notification);
            Cache::put("{$mobile}@{$country_code}", 1, Carbon::now()->addSeconds(59));
            return $this->success('发送成功');
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    /**
     * 短信宝发送短信
     */
    public function smsBaoSend(Request $request)
    {
        $mobile = $request->input('user_string');
        if (empty($mobile)) {
            return $this->error('电话不能为空');
        }
        $type = $request->input('type'); //
        if ($type == 'forget' || $type == 'login') {
            $user = Users::getByString($mobile);
            if (empty($user)) {
                return $this->error('账号错误');
            }
        } else {
            $user = Users::getByString($mobile);
            if (!empty($user)) {
                return $this->error('账号已存在');
            }
        }

        /* $user = Users::getByString($mobile);
        if(!empty($user)) return $this->error('账号已存在'); */
        $username = Setting::getValueByKey('smsBao_username', '');
        $password = Setting::getValueByKey('password', '');
        $sms_signature = Setting::getValueByKey('sms_signature', '【签名】');
        if (empty($mobile)) {
            return $this->error('请填写手机号');
        }
        $verification_code = $this->createSmsCode(4);
        //$verification_code = '8888';

        $content = $sms_signature . '您的验证码为 [' . $verification_code . ']，请勿泄漏。';
        $api = 'http://api.smsbao.com/sms';
        $send_url = $api . "?u=" . $username . "&p=" . md5($password) . "&m=" . $mobile . "&c=" . urlencode($content);
        $return_message = RPC::apihttp($send_url);
        if ($return_message == 0) {
            Cache::put("{$mobile}@{$country_code}", 1, Carbon::now()->addSeconds(300));
            
            return $this->success('发送成功');
        } else {
            $statusStr = array(
                "-1" => "参数不全",
                "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
                "30" => "密码错误",
                "40" => "账号不存在",
                "41" => "余额不足",
                "42" => "帐户已过期",
                "43" => "IP地址限制",
                "44" => "账号被禁用",
                "50" => "内容含有敏感词",
            );
            return $this->error($statusStr[$return_message]);
        }
    }

    /**
     * 检查1分钟内$ip是否发送过验证码
     * @param $ip
     * @return bool|\Illuminate\Http\JsonResponse
     */
    private function checkSmsIp($ip)
    {
        if (empty($ip)) {
            return $this->error('ip参数不正确');
        }
        return $this->checkSmsIpKey($ip);
    }

    /**
     * 生成短信验证码
     * @param int $num  验证码位数
     * @return string
     */
    public function createSmsCode($num = 6)
    {
        //验证码字符数组
        $n_array = range(0, 9);
        //随机生成$num位验证码字符
        $code_array = array_rand($n_array, $num);
        //重新排序验证码字符数组
        shuffle($code_array);
        //生成验证码
        $code = implode('', $code_array);
        return $code;
    }

    /**
     * 设置sms发送短信Ip缓存限制
     * @param $ip
     * @param $mobile
     */
    public function setSmsIpKey($ip, $mobile)
    {
        $key = Config::get('cache.keySmsIpCheck') . $ip;
        Redis::setex($key, $this->_sms_ip_check_expire_time, $mobile); //已发送

    }

    /**
     * 检查sms发送短信Ip缓存限制
     * @param $ip
     * @return bool
     */
    public function checkSmsIpKey($ip)
    {
        $key = Config::get('cache.keySmsIpCheck') . $ip;

        if (Redis::exists($key)) {
            return true;
        }
        return false;
    }

    /**
     * 发送邮箱验证 composer 安装的phpmailer
     */
    public function sendMail(Request $request)
    {
        $email = $request->input('user_string');
        $country_code = $request->input('country_code', '86');
        $country_code = str_replace('+', '', $country_code);
        $scene = $request->input('scene');
        $lang = $request->input('lang');
        if($lang == 'zh'){
            $lang = 'zh_cn';
        }
        if($lang){
            App::setLocale($lang);
        }
        if (empty($email)) {
            return $this->error(trans('sms.yxbnwk'));
        }
        $user = Users::getByString($email);
        if ($scene == 'login' || $scene == 'change_password' || $scene == 'reset_password') {
            if (empty($user)) {
                return $this->error(trans('sms.zhcw'));
            }
        } else {
            if (!empty($user)) {
                return $this->error(trans('sms.zhycz'));
            }
        }
        //  从设置中取出值
        $username = Setting::getValueByKey('phpMailer_username', '');
        $host = Setting::getValueByKey('phpMailer_host', '');
        $password = Setting::getValueByKey('phpMailer_password', '');
        $port = Setting::getValueByKey('phpMailer_port', 25);
        $from_name = Setting::getValueByKey('phpMailer_from_name', "[ExChange]");
        $port == '' && $port = 25;
        
        //实例化phpMailer
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->CharSet = "utf-8";
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "";
            $mail->Host = $host;
            $mail->Port = $port;
            $mail->Username = $username;
            $mail->Password = $password; //去开通的qq或163邮箱中找,这里用的不是邮箱的密码，而是开通之后的一个token
            $mail->setFrom($username, $from_name); //设置邮件来源  //发件人
            $mail->Subject = "Verification code"; //邮件标题
            $code = $this->createSmsCode(4);
            $mail->MsgHTML('Your verification code is' . '【' . $code . '】');   //邮件内容
            $mail->addAddress($email);  //收件人（用户输入的邮箱）
            $res = $mail->send();
            
            if ($res) {
                Cache::put('code@' .$country_code. $email,$code,300);
                return $this->success(trans('sms.fscg'));
            } else {
                return $this->error(trans('sms.czcw'));
            }
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage());
        }
    }
    
    /**
     * 普通单发，明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名
     * @param int $type 邮件类型，0事务投递，其他值的为商业投递量，默认是0
     * @param string $toEmail 邮件接收地址
     * @param string $fromEmail 发送邮件地址，管理控制台中配置的发信地址(登陆后台查看发信地址)
     * @param boolean $needToReply 是否显示回复邮件地址，如果为true是的时候，replyEmail必填，false的时候replyEmail可以为空
     * @param string $replyEmail 回复邮件地址
     * @param string $fromAlias 发信人昵称，可填空串
     * @param string $htmlBody 邮件正文
     * @param string $subject 邮件主题
     * @param string $ext 扩展字段,可填空串
     * @return string json string { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */

	function sendSingleEmail($type,$toEmail,$fromEmail,$needToReply,$replyEmail,$fromAlias,$htmlBody,$subject,$ext){
	/*
	请求包体
	{
		"sig": "D9544A3D290571425C8C5094542A76C2A19A84745F1DDFFA72F112A58E563517",
		"ext": "",
		"replyEmail": "xxxx@qq.com",
		"fromAlias": "张三",
		"htmlBody": "test email",
		"needToReply": true,
		"subject": "测试邮件",
		"clickTrace": "0",
		"time": 1519378109,
		"type": 0,
		"toEmail": "xxxxx@qq.com",
		"fromEmail": "service@xxx.com"
	}
	应答包体
	{
		"result": 0,
		"errmsg": "OK",
		"surplus": 19,
		"sequenceId": "5aa1deb00cf2deb46f411ee4"
	}
	*/	
	    $accesskey = "60311125b3ac081b7590a464";
        $secretkey = "432a335d66bb4535b5876bc1a4295645";
	    $random = rand(100000, 999999);
		$curTime = time();
        $wholeUrl =  "https://live.moduyun.com/directmail/v1/singleSendMail?accesskey=" . $accesskey . "&random=" . $random;

        // 按照协议组织 post 包体
        $data = new \stdClass();
		$data->sig = hash("sha256",
            "secretkey=".$secretkey."&random=".$random."&time=".$curTime."&fromEmail=".$fromEmail, FALSE);
		$data->ext = "";
		$data->replyEmail = $replyEmail;
		$data->fromAlias = $fromAlias;
		$data->htmlBody = $htmlBody;
		$data->needToReply = $needToReply;
		$data->subject = $subject;
		$data->clickTrace = "0";
		$data->time = $curTime;
		$data->type = $type;
		$data->toEmail = $toEmail;
		$data->fromEmail = $fromEmail;

		return $this->sendCurlPost($wholeUrl, $data);
	}
	function sendCurlPost($url, $dataObj) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");  
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen(json_encode($dataObj)))); 
        $ret = curl_exec($curl);
        if (false == $ret) {
            // curl_exec failed
            $result = "{ \"result\":" . -2 . ",\"errmsg\":\"" . curl_error($curl) . "\"}";
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "{ \"result\":" . -1 . ",\"errmsg\":\"". $rsp . " " . curl_error($curl) ."\"}";
            } else {
                $result = $ret;
            }
        }
        curl_close($curl);
        return $result;
    }
}
