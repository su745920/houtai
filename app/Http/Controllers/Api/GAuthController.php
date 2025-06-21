<?php

namespace App\Http\Controllers\Api;

use App\Models\{Admin, Users};
use Earnp\GoogleAuthenticator\GoogleAuthenticator;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App;

/**
 * 2023-05-11
 */
class GAuthController extends Controller
{
    public function getGInfo(Request $request)
    {
        $adminId=$request->input("id");
        $admin_user=Users::getById($adminId);
        $createSecret = GoogleAuthenticator::CreateSecret();
        // $createSecret['qrcode'] = QrCode::encoding('UTF-8')->size(180)->margin(1)->generate($createSecret['codeurl']);
        $image = QrCode::format('png')
                         ->size(200) // 根据需要设置尺寸
                         ->margin(1)
                         ->generate($createSecret['codeurl']);
         $createSecret['qrcode'] = 'data:image/png;base64,'.base64_encode($image);

        //print_r( $createSecret['qrcode']);


        return $this->success($createSecret);
    }

    public function bindGoogle(Request $request)
    {
        $lang = request()->input('lang','en');
        if($lang){
            if($lang == 'zh'){
                $lang = 'zh_cn';
            }
            App::setLocale($lang);
        }
        $secret = $request->input('secret', '');
        $code = $request->input('code','');
        if (!GoogleAuthenticator::CheckCode($secret, $code)){
            return $this->error(trans('login.yzmcw'));
        }
        $user_id = Users::getUserId();
        $user = Users::where('id', $user_id)->first();
        $user->google_secret = $secret;
        $user->save();
        return $this->success(trans('blind.nycgglggyzq'));
    }
}
