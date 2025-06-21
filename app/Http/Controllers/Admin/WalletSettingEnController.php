<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\WalletSetting;

class WalletSettingEnController extends Controller
{
    public function walletconfig(){
        $authorityList=session()->get("authorityList");
        $walletTypeList = [
            'usd' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],
            'eur' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],

            'gbp' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ]
        ];
        $setting = [];
        foreach($walletTypeList as $walletType => $items){
            foreach($items as $item){
                $setting[$walletType][$item] = '';
                $res = WalletSetting::where('wallet_type',$walletType)
                    ->where('parameter',$item)
                    ->first();
                if($res){
                    $setting[$walletType][$item] = $res->value;
                }
            }
        }
        return view('admin.setting_en.walletbase', ['setting' => $setting,'authorityList' => $authorityList]);
    }
    public function save(Request $request){
        $dataType = $request->input('data_type');
        $walletTypeList = [
            'usd' => [
                'usd_bankname' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'bankname'
                ],
                'usd_account_no' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'account_no'
                ],
                'usd_account_name' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'account_name'
                ],
                'usd_bank_address' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'bank_address'
                ],
                'usd_swiftcode' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'swiftcode'
                ],
                'usd_company_address' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'company_address'
                ],
                'usd_erc_min' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'erc_min'
                ],
                'usd_erc_max' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'erc_max'
                ],
                'usd_oucher_switch' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'voucher_switch'
                ],
                'usd_switch' => [
                    'wallet_type' => 'usd',
                    'parameter' => 'switch'
                ],
            ],

            'eur' => [
                'eur_bankname' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'bankname'
                ],
                'eur_account_no' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'account_no'
                ],
                'eur_account_name' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'account_name'
                ],
                'eur_bank_address' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'bank_address'
                ],
                'eur_swiftcode' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'swiftcode'
                ],
                'eur_company_address' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'company_address'
                ],
                'eur_erc_min' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'erc_min'
                ],
                'eur_erc_max' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'erc_max'
                ],
                'eur_oucher_switch' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'voucher_switch'
                ],
                'eur_switch' => [
                    'wallet_type' => 'eur',
                    'parameter' => 'switch'
                ],
            ],

            'gbp' => [
                'gbp_bankname' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'bankname'
                ],
                'gbp_account_no' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'account_no'
                ],
                'gbp_account_name' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'account_name'
                ],
                'gbp_bank_address' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'bank_address'
                ],
                'gbp_swiftcode' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'swiftcode'
                ],
                'gbp_company_address' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'company_address'
                ],
                'gbp_erc_min' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'erc_min'
                ],
                'gbp_erc_max' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'erc_max'
                ],
                'gbp_oucher_switch' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'voucher_switch'
                ],
                'gbp_switch' => [
                    'wallet_type' => 'gbp',
                    'parameter' => 'switch'
                ],
            ]
        ];
        foreach($walletTypeList[$dataType] as $name => $item){
            $value = $request->input($name);
            if(in_array($item['parameter'],['voucher_switch','switch']) && empty($value)){
                $value = "off";
            }
            $res = WalletSetting::where('wallet_type',$item['wallet_type'])
                ->where('parameter',$item['parameter'])
                ->first();
            if($res){
                $res->value = $value;
                $res->save();
            }
            else{
                $model = new WalletSetting();
                $model->wallet_type = $item['wallet_type'];
                $model->parameter = $item['parameter'];
                $model->value = $value;
                $model->save();
            }
        }
        return $this->success('操作成功');
    }
}
