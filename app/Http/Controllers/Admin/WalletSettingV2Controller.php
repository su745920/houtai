<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\WalletSetting;

class WalletSettingV2Controller extends Controller
{
    public function walletconfig(){
        $authorityList=session()->get("authorityList");
        $walletTypeList = [
            'usdt_trc' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'usdt_erc' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'usdc' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'eth' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'bit' => [
                'address','erc_min','erc_max','voucher_switch','switch'
            ],
            'usdt_trc_repayment' => [
                'address','switch'
            ],
            'usdt_erc_repayment' => [
               'address','switch'
            ],
            'usdc_repayment' => [
                'address','switch'
            ],
            'eth_repayment' => [
                'address','switch'
            ],
            'bit_repayment' => [
               'address','switch'
            ],

            'th' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],
            'id' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],

            'vi' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],
             'kor' => [
                'bankname','account_no','account_name','bank_address','swiftcode','company_address','erc_min','erc_max','voucher_switch','switch'
            ],
            'china_eft' => [
                'bankname','account_no','account_name','erc_min','erc_max','voucher_switch','switch'
            ],
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
        return view('admin.setting_v2.walletbase', ['setting' => $setting,'authorityList' => $authorityList]);
    }
    public function save(Request $request){
        $admin_username=session()->get("admin_username");
        if (empty($admin_username)){

        }else {
            
        $dataType = $request->input('data_type');
        $walletTypeList = [
            'usdt_trc' => [
                'usdt_trc_address' => [
                    'wallet_type' => 'usdt_trc',
                    'parameter' => 'address'
                ],
                'usdt_trc_erc_min' => [
                    'wallet_type' => 'usdt_trc',
                    'parameter' => 'erc_min'
                ],
                'usdt_trc_erc_max' => [
                    'wallet_type' => 'usdt_trc',
                    'parameter' => 'erc_max'
                ],
                'usdt_trc_voucher_switch' => [
                    'wallet_type' => 'usdt_trc',
                    'parameter' => 'voucher_switch'
                ],
                'usdt_trc_switch' => [
                    'wallet_type' => 'usdt_trc',
                    'parameter' => 'switch'
                ],
            ],
            'usdt_erc' => [
                'usdt_erc_address' => [
                    'wallet_type' => 'usdt_erc',
                    'parameter' => 'address'
                ],
                'usdt_erc_erc_min' => [
                    'wallet_type' => 'usdt_erc',
                    'parameter' => 'erc_min'
                ],
                'usdt_erc_erc_max' => [
                    'wallet_type' => 'usdt_erc',
                    'parameter' => 'erc_max'
                ],
                'usdt_erc_voucher_switch' => [
                    'wallet_type' => 'usdt_erc',
                    'parameter' => 'voucher_switch'
                ],
                'usdt_erc_switch' => [
                    'wallet_type' => 'usdt_erc',
                    'parameter' => 'switch'
                ],
            ],
            'usdc' => [
                'usdc_address' => [
                    'wallet_type' => 'usdc',
                    'parameter' => 'address'
                ],
                'usdc_erc_min' => [
                    'wallet_type' => 'usdc',
                    'parameter' => 'erc_min'
                ],
                'usdc_erc_max' => [
                    'wallet_type' => 'usdc',
                    'parameter' => 'erc_max'
                ],       
                'usdc_voucher_switch' => [
                    'wallet_type' => 'usdc',
                    'parameter' => 'voucher_switch'
                ],
                'usdc_switch' => [
                    'wallet_type' => 'usdc',
                    'parameter' => 'switch'
                ],
            ],
            'eth' => [
                'eth_address' => [
                    'wallet_type' => 'eth',
                    'parameter' => 'address'
                ],
                'eth_erc_min' => [
                    'wallet_type' => 'eth',
                    'parameter' => 'erc_min'
                ],
                'eth_erc_max' => [
                    'wallet_type' => 'eth',
                    'parameter' => 'erc_max'
                ],
                'eth_voucher_switch' => [
                    'wallet_type' => 'eth',
                    'parameter' => 'voucher_switch'
                ],
                'eth_switch' => [
                    'wallet_type' => 'eth',
                    'parameter' => 'switch'
                ],
            ],
            'bit' => [
                'bit_address' => [
                    'wallet_type' => 'bit',
                    'parameter' => 'address'
                ],
                'bit_erc_min' => [
                    'wallet_type' => 'bit',
                    'parameter' => 'erc_min'
                ],
                'bit_erc_max' => [
                    'wallet_type' => 'bit',
                    'parameter' => 'erc_max'
                ],
                'bit_voucher_switch' => [
                    'wallet_type' => 'bit',
                    'parameter' => 'voucher_switch'
                ],
                'bit_switch' => [
                    'wallet_type' => 'bit',
                    'parameter' => 'switch'
                ],
            ],
            'th' => [
                'th_bankname' => [
                    'wallet_type' => 'th',
                    'parameter' => 'bankname'
                ],
                'th_account_no' => [
                    'wallet_type' => 'th',
                    'parameter' => 'account_no'
                ],
                'th_account_name' => [
                    'wallet_type' => 'th',
                    'parameter' => 'account_name'
                ],
                'th_bank_address' => [
                    'wallet_type' => 'th',
                    'parameter' => 'bank_address'
                ],
                'th_swiftcode' => [
                    'wallet_type' => 'th',
                    'parameter' => 'swiftcode'
                ],
                'th_company_address' => [
                    'wallet_type' => 'th',
                    'parameter' => 'company_address'
                ],
                'th_erc_min' => [
                    'wallet_type' => 'th',
                    'parameter' => 'erc_min'
                ],
                'th_erc_max' => [
                    'wallet_type' => 'th',
                    'parameter' => 'erc_max'
                ],
                'th_oucher_switch' => [
                    'wallet_type' => 'th',
                    'parameter' => 'voucher_switch'
                ],
                'th_switch' => [
                    'wallet_type' => 'th',
                    'parameter' => 'switch'
                ],
            ],
            'eft' => [
                'eft_bankname' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'bankname'
                ],
                'eft_account_no' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'account_no'
                ],
                'eft_account_name' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'account_name'
                ],
                'eft_bank_address' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'bank_address'
                ],
                'eft_swiftcode' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'swiftcode'
                ],
                'eft_company_address' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'company_address'
                ],
                'eft_erc_min' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'erc_min'
                ],
                'eft_erc_max' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'erc_max'
                ],
                'eft_oucher_switch' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'voucher_switch'
                ],
                'eft_switch' => [
                    'wallet_type' => 'eft',
                    'parameter' => 'switch'
                ],
            ],


            'id' => [
                'id_bankname' => [
                    'wallet_type' => 'id',
                    'parameter' => 'bankname'
                ],
                'id_account_no' => [
                    'wallet_type' => 'id',
                    'parameter' => 'account_no'
                ],
                'id_account_name' => [
                    'wallet_type' => 'id',
                    'parameter' => 'account_name'
                ],
                'id_bank_address' => [
                    'wallet_type' => 'id',
                    'parameter' => 'bank_address'
                ],
                'id_swiftcode' => [
                    'wallet_type' => 'id',
                    'parameter' => 'swiftcode'
                ],
                'id_company_address' => [
                    'wallet_type' => 'id',
                    'parameter' => 'company_address'
                ],
                'id_erc_min' => [
                    'wallet_type' => 'id',
                    'parameter' => 'erc_min'
                ],
                'id_erc_max' => [
                    'wallet_type' => 'id',
                    'parameter' => 'erc_max'
                ],
                'id_oucher_switch' => [
                    'wallet_type' => 'id',
                    'parameter' => 'voucher_switch'
                ],
                'id_switch' => [
                    'wallet_type' => 'id',
                    'parameter' => 'switch'
                ],
            ],

            'vi' => [
                'vi_bankname' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'bankname'
                ],
                'vi_account_no' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'account_no'
                ],
                'vi_account_name' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'account_name'
                ],
                'vi_bank_address' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'bank_address'
                ],
                'vi_swiftcode' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'swiftcode'
                ],
                'vi_company_address' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'company_address'
                ],
                'vi_erc_min' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'erc_min'
                ],
                'vi_erc_max' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'erc_max'
                ],
                'vi_oucher_switch' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'voucher_switch'
                ],
                'vi_switch' => [
                    'wallet_type' => 'vi',
                    'parameter' => 'switch'
                ],
            ],
            
                'kor' => [
                'kor_bankname' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'bankname'
                ],
                'kor_account_no' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'account_no'
                ],
                'kor_account_name' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'account_name'
                ],
                'kor_bank_address' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'bank_address'
                ],
                'kor_swiftcode' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'swiftcode'
                ],
                'kor_company_address' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'company_address'
                ],
                'kor_erc_min' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'erc_min'
                ],
                'kor_erc_max' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'erc_max'
                ],
                'kor_oucher_switch' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'voucher_switch'
                ],
                'kor_switch' => [
                    'wallet_type' => 'kor',
                    'parameter' => 'switch'
                ],
            ],




            'china_eft' => [
                'china_eft_bankname' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'bankname'
                ],
                'china_eft_account_no' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'account_no'
                ],
                'china_eft_account_name' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'account_name'
                ],
                'china_eft_erc_min' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'erc_min'
                ],
                'china_eft_erc_max' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'erc_max'
                ],
                'china_eft_oucher_switch' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'voucher_switch'
                ],
                'china_eft_switch' => [
                    'wallet_type' => 'china_eft',
                    'parameter' => 'switch'
                ],
            ],
            
             'usdt_trc_repayment' => [
                'usdt_trc_address_repayment' => [
                    'wallet_type' => 'usdt_trc_repayment',
                    'parameter' => 'address'
                ],
                'usdt_trc_switch_repayment' => [
                    'wallet_type' => 'usdt_trc_repayment',
                    'parameter' => 'switch'
                ],
            ],
             'usdt_erc_repayment' => [
                'usdt_erc_address_repayment' => [
                    'wallet_type' => 'usdt_erc_repayment',
                    'parameter' => 'address'
                ],
                'usdt_erc_switch_repayment' => [
                    'wallet_type' => 'usdt_erc_repayment',
                    'parameter' => 'switch'
                ],
            ],
             'usdc_repayment' => [
                'usdc_address_repayment' => [
                    'wallet_type' => 'usdc_repayment',
                    'parameter' => 'address'
                ],
                'usdc_switch_repayment' => [
                    'wallet_type' => 'usdc_repayment',
                    'parameter' => 'switch'
                ],
            ],
             'eth_repayment' => [
                'eth_address_repayment' => [
                    'wallet_type' => 'eth_repayment',
                    'parameter' => 'address'
                ],
                'eth_switch_repayment' => [
                    'wallet_type' => 'eth_repayment',
                    'parameter' => 'switch'
                ],
            ],
            'bit_repayment' => [
                'bit_address_repayment' => [
                    'wallet_type' => 'bit_repayment',
                    'parameter' => 'address'
                ],
                'bit_switch_repayment' => [
                    'wallet_type' => 'bit_repayment',
                    'parameter' => 'switch'
                ],
            ],
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
}
