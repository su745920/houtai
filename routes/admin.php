<?php

Route::namespace('Admin')->middleware(['admin_logger'])->group(function () {
    Route::get('/admin/login', 'DefaultController@login');
    
    Route::get('/admin/getCount', 'AdminController@getRechargeCount');
    Route::get('/admin/getCounts', 'AdminController@getRechargeCounts');
    Route::post('/admin/login', 'DefaultController@postLogin');
    Route::get('/admin/cuetone', 'DefaultController@cuetone');
    Route::get('/admin/docuetone', 'DefaultController@docuetone');
    Route::get('/admin/desktopmenu', 'MenuController@desktopmenu');
    
    
    Route::any('/admin/main', 'DefaultController@main');
    Route::any('/logout', 'DefaultController@logout');
    Route::post('/admin/isLogin', 'DefaultController@isLogin');
    
    Route::any('/admin/hqControl/index', 'AdminHqController@index');


    Route::any('/admin/hqControl/saveAjax', 'AdminHqController@saveAjax');
    Route::any('/admin/hqControl/previewKLine', 'AdminHqController@previewKLine');

    Route::any('/admin/hqControl/page', 'AdminHqHistoryController@page');
    Route::any('/admin/hqControl/historyListAjax', 'AdminHqHistoryController@historyListAjax');
    Route::post('/admin/hqControl/historyDelete', 'AdminHqHistoryController@historyDelete');
    
    Route::get('/admin/hqControl/price', 'AdminHqController@priceIndex');
    Route::post('/admin/hqControl/savePrice', 'AdminHqController@savePrice');
    
    Route::get('/admin/u_google_auth/getBindQrcode', 'GoogleAuthController@getBindQrcode');
    Route::post('/admin/u_google_auth/bind', 'GoogleAuthController@bind');

});

//管理后台
Route::namespace('Admin')->prefix('admin')->middleware(['admin_auth','admin_logger'])->group(function () {
    Route::get('/user_level', 'SettingsController@user_level');
    Route::post('/user_level', 'SettingsController@postUserLevel');

    
    Route::get('/bigdata_v2024', 'DefaultController@bigdata_v2024');
    Route::get('/getAssetsDistribution', 'AdminBigDataController@getAssetsDistribution');
    Route::get('/microyingkuiTj', 'AdminBigDataController@microyingkuiTj');
    
    
    
    Route::get('/c2c_merchant/merchant_page', 'C2CMerchantController@merchant_page');
    Route::any('/c2c_merchant/merchantPageAjax', 'C2CMerchantController@merchantPageAjax');

    Route::any('/c2c_merchant/merchant_success', 'C2CMerchantController@merchant_success');
    Route::any('/c2c_merchant/merchant_reject', 'C2CMerchantController@merchant_reject');


    Route::get('/c2c_merchant/apply_page', 'C2CMerchantApplyController@apply_page');
    Route::any('/c2c_merchant/applyPageAjax', 'C2CMerchantApplyController@applyPageAjax');
    
    
    Route::any('/homeBigData', 'AdminBigDataController@home');
    Route::any('/czTipFn', 'AdminBigDataController@czTipFn');


    Route::any('/set/update_pwd', 'AdminSetController@update_pwd');
    Route::any('/set/updatePwdAjax', 'AdminSetController@updatePwdAjax');


    Route::get('/index', 'DefaultController@indexnew');
    
    Route::any('/copyTrade/page', 'CopyTradeController@page');//交易员列表
    Route::any('/copyTrade/tradePageData', 'CopyTradeController@tradePageData');

    Route::any('/copyTrade/edit', 'CopyTradeController@edit');//交易员列表


    Route::any('/copy_trade_order/page', 'CopyTradeOrderController@page');//交易员列表
    Route::any('/copy_trade_order/listDataAjax', 'CopyTradeOrderController@Leverdeals');//杠杆交易 团队所有订单



    Route::any('/miningRewardGroup/page', 'MiningRewardGroupController@page');
    Route::any('/miningRewardGroup/index', 'MiningRewardGroupController@index');
    Route::any('/miningRewardGroup/tg', 'MiningRewardGroupController@tg');
    Route::any('/miningRewardGroup/reject', 'MiningRewardGroupController@reject');





    Route::any('/czRewardGroup/page', 'CzRewardGroupController@page');
    Route::any('/czRewardGroup/index', 'CzRewardGroupController@index');

    Route::any('/czRewardGroup/tg', 'CzRewardGroupController@tg');
    Route::any('/czRewardGroup/reject', 'CzRewardGroupController@reject');
    
    Route::any('/tranFeeReward/page', 'AdminTranFeeRewardController@page');
    Route::any('/tranFeeReward/index', 'AdminTranFeeRewardController@index');







    Route::get('/safe/verificationcode', 'DefaultController@getVerificationCode'); //获取链上操作安全验证码
    Route::any('/ueditor/uploader', 'UeditorController@ueditor');
    Route::any('chain/balance_collect', 'ChainController@collectBalance');
    Route::any('chain/send_fee', 'ChainController@sendFee');
    Route::get('logs/index', 'AdminLogsController@index');
    Route::get('logs/list', 'AdminLogsController@lists');

    Route::get('/google_auth/getBindQrcode', 'GoogleAuthController@getBindQrcode');
    Route::post('/google_auth/bind', 'GoogleAuthController@bind');
    Route::post('/google_auth/checkGoogle', 'GoogleAuthController@checkGoogle');
    
    Route::get('/a_google_auth/getBindQrcode', 'GoogleAuthController@getAgentBindQrcode');
    Route::post('/a_google_auth/bind', 'GoogleAuthController@agentBind');
    Route::post('/a_google_auth/checkGoogle', 'GoogleAuthController@CheckAgentGoogle');
    

    Route::prefix('Leverdeals')->group(function () {
        Route::any('Leverdeals_show', 'TransactionController@Leverdeals_show');
        Route::any('close', 'TransactionController@close');
        Route::any('list', 'TransactionController@Leverdeals');//杠杆交易 团队所有订单
        Route::get('csv', 'TransactionController@csv');//导出杠杆交易 团队所有订单
        Route::any('caution','TransactionController@cautionView');
        Route::any('caution_confirm','TransactionController@cautionConfirm');
    });
    Route::get('/agent', 'AdminController@agent');//代理
    
    
    
    Route::any('/currency/project/view', 'CurrencyProjectController@projectView');
        Route::any('/currency/project/detail/view', 'CurrencyProjectController@projectDetailView');

        Route::any('/currency/project/del', 'CurrencyProjectController@del');


        Route::any('/currency/project/list', 'CurrencyProjectController@projectList');
        Route::any('/currency/project/add', 'CurrencyProjectController@newProject');
        Route::any('/currency/project/edit', 'CurrencyProjectController@editProject');
        Route::any('/currency/project/detail', 'CurrencyProjectController@projectDetail');
        
        Route::any('/currency/project/order/view','CurrencyProjectController@projectOrderView');
        Route::any('/currency/project/order/list','CurrencyProjectController@projectOrderList');
 
        Route::any('/currency/project/sell/view','CurrencyProjectController@confirmSellView');
        Route::any('/currency/project/sell/confirm','CurrencyProjectController@confirmSell');
    
    // 法币
    Route::group([], function () {
        Route::get('/legal', 'LegalDealSendController@index')->middleware(['demo_limit']);
        Route::get('/legal/list', 'LegalDealSendController@list');
        Route::get('/legal_deal', 'LegalDealController@index')->middleware(['demo_limit']);
        Route::get('/legal_deal/list', 'LegalDealController@list');
        Route::get('/legal/all', 'LegalDealSendController@all');
        Route::any('admin_legal_pay_cancel', 'LegalDealController@adminLegalDealCancel'); // 管理员后台取消交易
        Route::any('legal_deal_admin_sure', 'LegalDealController@adminDoSure'); // 管理员后台确认收款
        //商家
        Route::get('seller', 'SellerController@index');//商家首页
        Route::get('seller_list', 'SellerController@lists');
        Route::get('seller_add', 'SellerController@add')->middleware(['demo_limit']);
        Route::post('seller_add', 'SellerController@postAdd')->middleware(['demo_limit']);
        Route::get('seller/adjust_balance', 'SellerController@adjustBalance')->middleware(['demo_limit']);
        Route::post('seller/adjust_balance', 'SellerController@postAdjustBalance')->middleware(['demo_limit']);
        Route::post('seller_del', 'SellerController@delete')->middleware(['demo_limit']);
        Route::post('send/back', 'SellerController@sendBack');//撤回发布
        Route::post('send/is_shelves', 'SellerController@is_shelves');//下架
        Route::get('seller/logs', 'SellerController@logs');
        Route::get('seller/logs_list', 'SellerController@logsList');
    });

    //C2C
    Route::group([], function () {
        Route::get('/c2c', 'C2cDealSendController@index')->middleware(['demo_limit']);
        Route::get('/c2c/list', 'C2cDealSendController@list');
        Route::get('/c2c_deal', 'C2cDealController@index')->middleware(['demo_limit']);
        Route::get('/c2c/all', 'C2cDealController@all');
        Route::get('/c2c_deal/list', 'C2cDealController@list');
        Route::post('c2c/send/back', 'C2cDealSendController@sendBack');//撤回发布
        Route::post('c2c/send/del', 'C2cDealSendController@sendDel');//删除

        Route::get('/c2c/appeal', 'AdminC2cController@appeal');
        Route::any('/c2c/appealPageData', 'AdminC2cController@appealPageData');
    });

    //投诉建议

    Route::prefix('feedback')->group(function () {
        Route::get('detail', 'FeedBackController@feedBackDetail');
        Route::get('del', 'FeedBackController@feedBackDel');
        Route::post('reply', 'FeedBackController@reply');
        Route::get('index', 'FeedBackController@index');
        Route::get('list', 'FeedBackController@feedbackList');
        Route::get('/feedback/csv', 'FeedBackController@csv')->middleware(['demo_limit']);
    });

    //系统设置
    Route::prefix('setting')->group(function () {
        Route::get('index', 'SettingController@index');//设置首页
        Route::get('list', 'SettingController@list');//设置首页
        Route::get('add', 'SettingController@add');//设置奖金
        Route::post('postadd', 'SettingController@postAdd');//设置奖金
        Route::get('set_base', 'SettingController@base');//基础设置
        Route::post('basesite', 'SettingController@setBase');//提交基础设置
        Route::get('data/index', 'SettingController@dataSetting');//提交基础设置
        Route::get('walletconfig', 'SettingController@walletconfig');//提交基础设置
        
        Route::any('walletconfig_v2', 'WalletSettingV2Controller@walletconfig');//钱包设置
        Route::any('save_v2', 'WalletSettingV2Controller@save');//提交钱包设置
        
        Route::any('walletconfig_en', 'WalletSettingEnController@walletconfig');//钱包设置
        Route::any('save_en', 'WalletSettingEnController@save');//提交钱包设置
        
    });

    //提币
    Route::group([], function () {
        Route::get('cashb', 'CashbController@index')->middleware(['demo_limit']);
        Route::get('cashb_list', 'CashbController@cashbList');
        Route::get('cashb_show', 'CashbController@show')->middleware(['demo_limit']);//提币详情页面
        Route::post('cashb_done', 'CashbController@done')->middleware(['demo_limit']);//确认提币成功
        Route::get('cashb_back', 'CashbController@back')->middleware(['demo_limit']);//执行退回申请
        
        Route::post('cashb_reset', 'CashbController@reset')->middleware(['demo_limit']);//重置提币
        //导出数据到excel文件
        Route::get('/cashb/csv', 'CashbController@csv')->middleware(['demo_limit']);//导出提币记录
        Route::post('cashb_edit_address', 'CashbController@editAddress')->middleware(['demo_limit']);//提币编辑页面
    });


    Route::prefix('wallet')->group(function () {
        Route::get('index', 'WalletController@index'); //钱包管理页面
        Route::get('list', 'WalletController@lists'); //钱包列表搜索
        Route::get('make', 'WalletController@makeWallet'); //生成钱包
        Route::get('update_balance', 'WalletController@updateBalance'); //更新链上余额
        Route::get('transfer_poundage', 'WalletController@transferPoundage'); //打入手续费
        Route::get('collect', 'WalletController@collect'); //余额归拢
    });
    
     Route::prefix('wallet_address')->group(function () {
        Route::get('index', 'WalletAddressController@index'); //钱包地址管理页面
        Route::get('lists', 'WalletAddressController@lists'); //钱包地址列表搜索
        Route::get('add', 'WalletAddressController@add'); //钱包地址添加
        Route::post('postAdd', 'WalletAddressController@postAdd'); // 钱包地址添加提交
        Route::post('del', 'WalletAddressController@del'); // 删掉
        Route::post('setVoucher', 'WalletAddressController@setVoucher'); 
        Route::post('setShow', 'WalletAddressController@setShow');
        Route::post('setDefault', 'WalletAddressController@setDefault');
    });
    
    Route::prefix('wallet_address_list')->group(function () {
        Route::get('index', 'WalletAddressListController@index'); // 钱包地址列表
        Route::get('lists', 'WalletAddressListController@lists'); // 钱包地址列表
        Route::get('add', 'WalletAddressListController@add'); //钱包地址添加
        Route::post('postAdd', 'WalletAddressListController@postAdd'); // 钱包地址添加提交
        Route::post('del', 'WalletAddressListController@del'); // 删掉
        Route::post('setVoucher', 'WalletAddressListController@setVoucher'); 
        Route::post('setShow', 'WalletAddressListController@setShow');
    });

    Route::prefix('user')->group(function () {


        Route::get('editFKRatio', 'UserController@editFKRatio');
        Route::post('updateFKRatio', 'UserController@updateFKRatio');
        
        Route::get('send_mail_index', 'UserController@sendMailIndex');
        Route::post('send_mail', 'UserController@sendMail');

        Route::post('real_change', 'UserRealController@real_change');//账号锁定

        Route::get('false_data', 'UserController@falseData');
        Route::get('chart_data', 'UserController@chartData');
        Route::post('chart_data', 'UserController@dochartData');
        Route::get('count_index', 'UserController@countData');
        //实名认证管理
        Route::get('real_index', 'UserRealController@index');
        Route::get('real_list', 'UserRealController@list');
        Route::get('real_info', 'UserRealController@detail');
        Route::post('real_del', 'UserRealController@del');
        Route::post('real_auth', 'UserRealController@auth');
        Route::post('pass_auth', 'UserRealController@passauth');
        Route::post('refuse_auth', 'UserRealController@refuseauth');

        Route::get('editltc', 'UserController@editltc');
        Route::post('editltc', 'UserController@doeditltc');

        Route::get('edit', 'UserController@edit');
        Route::post('edit', 'UserController@doedit');
        Route::get('edit_address', 'UserController@editAddress');
        Route::post('edit_address', 'UserController@doEditAddress');

        Route::get('address', 'UserController@address');//提币地址信息
        Route::post('address_edit', 'UserController@addressEdit');//修改地址信息
        
        Route::get('address_list', 'UserController@addressList');//提币地址信息

        Route::get('user_index', 'UserController@index');
        Route::get('list', 'UserController@lists');
        Route::get('users_wallet', 'UserController@wallet');
        Route::get('users_usdt', 'UserController@usersUsdt');
        Route::get('recharge_usdt', 'UserController@rechargeUsdt');
        Route::get('withdraw_usdt', 'UserController@withdrawUsdt');
        Route::get('walletList', 'UserController@walletList');
        Route::post('wallet_lock', 'UserController@walletLock');//钱包锁定

        Route::get('conf', 'UserController@conf');
        Route::post('conf', 'UserController@postConf')->middleware(['demo_limit']);//调节钱包账户
        Route::post('del', 'UserController@del')->middleware(['demo_limit']); //删除用户
        Route::post('delw', 'UserController@delw')->middleware(['demo_limit']); //删除指定id钱包
        Route::post('lock', 'UserController@lock')->middleware(['demo_limit']);//账号锁定
        Route::post('allow_exchange', 'UserController@allowExchange'); //允许积分兑换
        Route::post('blacklist', 'UserController@blacklist')->middleware(['demo_limit']);//加入黑名单
        Route::get('candy_conf/{id}', 'UserController@candyConf'); //
        Route::post('candy_conf/{id}', 'UserController@postCandyConf'); //
        Route::get('csv', 'UserController@csv')->middleware(['demo_limit']);//导出会员
         Route::post('google_cancel', 'UserController@googleCancel');//取消谷歌绑定
        
        Route::post('batch_risk', 'UserController@batchRisk');
    });

    Route::prefix('account')->group(function () {
        Route::get('account_index', 'AccountLogController@index');
        Route::get('list', 'AccountLogController@lists');
        Route::get('viewDetail', 'AccountLogController@view');
        //Route::get('recharge', 'AccountLogController@recharge');
        //Route::get('recharge/lists', 'AccountLogController@rechargeList');

        Route::get('recharge','AccountLogController@record');
        Route::get('recharge/lists','AccountLogController@recordList');
        Route::get('record','AccountLogController@record');
        Route::get('record/lists','AccountLogController@recordList');
        Route::post('change_state', 'AccountLogController@change_state'); //充币列表
        
        Route::get('recharge_v2', 'AccountLogController@recharge_v2');
        Route::get('recharge_v2/page', 'AdminRechargeV2Controller@rechargePage');
        
        Route::get('recharge_v2_success', 'AccountLogController@recharge_v2_success');
        Route::get('recharge_v2_success/page', 'AdminRechargeV2Controller@rechargeSuccessPage');

        Route::get('recharge_v2_fail', 'AccountLogController@recharge_v2_fail');
        Route::get('recharge_v2_fail/page', 'AdminRechargeV2Controller@rechargeFailPage');
        
        
        
        Route::any('v2_tj', 'AdminRechargeV2Controller@v2_tj');
        Route::any('v2_jj', 'AdminRechargeV2Controller@v2_jj');
        Route::any('v2_reset', 'AdminRechargeV2Controller@v2_reset');
        
        Route::any('delWithdrawAddress01', 'UserController@delWithdrawAddress01');
        
    });

    //邀请返佣
    Route::prefix('invite')->group(function () {
        Route::any('saveConf', 'AdminRewardController@saveConf');//邀请返佣
        Route::any('configEdit', 'AdminRewardController@configEdit');//邀请返佣
        Route::any('reward_conf_page', 'AdminRewardController@reward_conf_page');//邀请返佣
        Route::any('reward_conf_list', 'AdminRewardController@reward_conf_list');//邀请返佣
        
        
        Route::get('account_return', 'InviteController@return');//邀请返佣
        Route::get('return_list', 'InviteController@returnList');//邀请返佣列表
        Route::get('childs', 'InviteController@childs');//会员邀请关系图
        Route::get('share', 'InviteController@share');//邀请分享设置
        Route::post('share', 'InviteController@postShare');//邀请分享设置提交

        Route::get('getTree', 'InviteController@getTree');//
        Route::post('del', 'InviteController@del');

        Route::get('edit', 'InviteController@edit');
        Route::post('edit', 'InviteController@doedit');
        Route::post('bgdel', 'InviteController@bgdel');

        Route::post('change_parant', 'InviteController@changeParant');//更改上级
        
    });

    Route::get('/transaction/tran_index', 'TransactionController@index');
    Route::get('/transaction/list', 'TransactionController@lists');

    //后台管理员、角色管理、代理管理
    Route::prefix('manager')->group(function () {

        Route::get('google', 'AdminController@google');//添加管理员

        Route::post('delGoogle', 'AdminController@delGoogle');//删除管理员
        
        Route::post('delAgentGoogle', 'AdminController@delAgentGoogle');//删除代理绑定

        Route::get('manager_index', 'AdminController@managerIndex');
        Route::get('users', 'AdminController@users');
        Route::get('add', 'AdminController@add');//添加管理员
        Route::post('add', 'AdminController@postAdd');//添加管理员
        Route::post('delete', 'AdminController@del');//删除管理员
        Route::get('manager_roles', 'AdminController@adminRoles');
        Route::get('manager_roles_api', 'AdminRoleController@users');
        Route::get('role_add', 'AdminRoleController@add');
        Route::post('role_add', 'AdminRoleController@postAdd');
        Route::post('role_delete', 'AdminRoleController@del');
        Route::get('role_permission', 'AdminRolePermissionController@update');
        Route::post('role_permission', 'AdminRolePermissionController@postUpdate');
        
        Route::post('role_permission_v2', 'AdminRolePermissionController@postUpdateV2');
    });
    
    // 代理管理
    Route::prefix('agent')->group(function () {
        Route::get('index', 'AdminController@agentIndex'); // 代理管理
         Route::get('users', 'AdminController@agentUsers'); // 代理管理列表
        Route::get('add', 'AdminController@agentAdd');// 添加代理
        Route::post('add', 'AdminController@postAgentAdd');// 添加代理
        Route::post('delete', 'AdminController@agentDel');// 删除代理
        Route::post('searchuser', 'AdminController@searchuser');//查询用户
        Route::get('salesmen/add', 'AdminController@salesmenAdd');//添加代理商页面
        Route::get('salesmen/edit', 'AdminController@salesmenEdit');//编辑代理商页面
        Route::post('addagent', 'AdminController@addAgent');//添加代理商
        Route::post('update', 'AdminController@updateAgent');//添加代理商
        Route::post('addsonagent', 'AdminController@addSonAgent');//给代理商添加代理商
    });

    //新闻
    Route::group([], function () {
        
        Route::get('prize_index', 'AdminPrizeController@index');
        Route::get('prize_add', 'AdminPrizeController@add');
        Route::post('prize_add', 'AdminPrizeController@postAdd');
        Route::get('prize_edit/{id}', 'AdminPrizeController@edit');
        Route::post('prize_edit/{id}', 'AdminPrizeController@postEdit');

        Route::get('dzp_config_index', 'AdminDzpController@index');
        Route::post('dzp_config_index', 'AdminDzpController@postIndex');

        Route::get('dzp_config_count', 'AdminDzpController@dzp_config_count');
        Route::post('dzp_config_count', 'AdminDzpController@postDzpConfigCount');
        
        
         Route::get('rebate_rules_index', 'RebateRulesController@index');
        Route::get('rebate_rules_edit/{id}', 'RebateRulesController@edit');

        Route::post('rebate_rules_edit', 'RebateRulesController@postEdit');
        
        
        //新闻路由
        Route::get('news_index', 'NewsController@index');
        Route::get('news_add', 'NewsController@add');
        Route::post('news_add', 'NewsController@postAdd');
        Route::get('news_edit/{id}', 'NewsController@edit');
        Route::post('news_edit/{id}', 'NewsController@postEdit');
        Route::get('news_del/{id}/{togetherDel?}', 'NewsController@del');
        //新闻分类路由
        Route::get('news_cate_index', 'NewsController@cateIndex');
        Route::get('news_cate_add', 'NewsController@cateAdd');
        Route::get('news_cate_list', 'NewsController@getCateList');
        Route::post('news_cate_add', 'NewsController@postCateAdd');
        Route::get('news_cate_edit/{id}', 'NewsController@cateEdit');
        Route::post('news_cate_edit/{id}', 'NewsController@postCateEdit');
        Route::get('news_cate_del/{id}', 'NewsController@cateDel');
    });

    //交易
    Route::group([], function () {
        Route::get('complete', 'TransactionController@completeIndex');
        Route::get('in', 'TransactionController@inIndex');
        Route::get('out', 'TransactionController@outIndex');
        Route::get('cny', 'TransactionController@cnyIndex');
        Route::get('complete_list', 'TransactionController@completeList');
        Route::get('in_list', 'TransactionController@inList');
        Route::get('out_list', 'TransactionController@outList');
        Route::get('cny_list', 'TransactionController@cnyList');
        Route::get('trade', 'TransactionController@trade'); //撮合交易
        Route::get('exchange_cancel', 'TransactionController@cancel'); //后台撤单
        Route::get('exchange_del', 'TransactionController@del'); //后台撤单
        Route::get('trading','TransactionController@trading');//强制买入
        Route::get('tradingout','TransactionController@tradingout');//强制卖出
    });
    
      //贷款
    Route::group([], function () {
        // 贷款订单
        Route::get('loan_order_index', 'LoanOrderController@index');
        Route::get('loan_order_list', 'LoanOrderController@lists');
        Route::post('loan_order_pass', 'LoanOrderController@postPass');
        Route::post('loan_order_reject', 'LoanOrderController@postReject');
        Route::post('loan_order_del', 'LoanOrderController@postDel');
        Route::post('loan_order_reset', 'LoanOrderController@postReset');
        // 还款订单
        Route::get('repayment_record_index', 'RepaymentRecordController@index');
        Route::get('repayment_record_list', 'RepaymentRecordController@lists');
        Route::post('repayment_record_pass', 'RepaymentRecordController@postPass');
        Route::post('repayment_record_reject', 'RepaymentRecordController@postReject');
        Route::post('repayment_record_del', 'RepaymentRecordController@postDel');
        // 贷款设置
        Route::get('loan_setting_index', 'LoanSettingController@index');
        Route::get('loan_setting_list', 'LoanSettingController@lists');
        Route::post('/loan_setting_status', 'LoanSettingController@showStatus');
        Route::get('loan_setting_add', 'LoanSettingController@loanSettingAdd');
        Route::post('loan_setting_add', 'LoanSettingController@postLoanSettingAdd');
        Route::get('loan_setting_edit/{id}', 'LoanSettingController@loanSettingEdit');
        Route::post('loan_setting_edit/{id}', 'LoanSettingController@postLoanSettingEdit');
        Route::get('loan_setting_del/{id}', 'LoanSettingController@loanSettingDel');
    });

    //币种
    Route::group([], function () {
        Route::get('currency/match_index', 'CurrencyController@match'); //交易对
        
        
        Route::get('currency', 'CurrencyController@index');//首页
        Route::get('currency_add', 'CurrencyController@add')->middleware(['demo_limit']);//添加币种
        Route::post('currency_add', 'CurrencyController@postAdd')->middleware(['demo_limit']);//添加币种
        Route::get('currency/set_in_address/{id}', 'CurrencyController@setInAddress')->middleware(['demo_limit']);
        Route::get('currency/set_out_address/{id}', 'CurrencyController@setOutAddress')->middleware(['demo_limit']);
        Route::post('currency/set_in_address', 'CurrencyController@postSetInAddress')->middleware(['demo_limit']);
        Route::post('currency/set_out_address', 'CurrencyController@postSetOutAddress')->middleware(['demo_limit']);
        Route::get('currency_list', 'CurrencyController@lists');//币种
        Route::post('currency_del', 'CurrencyController@delete')->middleware(['demo_limit']);//删除币种
        Route::post('currency_display', 'CurrencyController@isDisplay');//币种显示
        Route::post('currency_execute', 'CurrencyController@executeCurrency');//币种显示
        Route::get('currency/match/{legal_id}', 'CurrencyController@match'); //交易对
        Route::get('currency/match_list/{legal_id}', 'CurrencyController@matchList'); //交易对列表
        Route::get('currency/match_add/{legal_id}', 'CurrencyController@addMatch'); //添加交易对页
        Route::post('currency/match_add/{legal_id}', 'CurrencyController@postAddMatch')->middleware(['demo_limit']); //添加交易对
        Route::get('currency/match_edit/{id}', 'CurrencyController@editMatch'); //编辑交易对页
        Route::post('currency/match_edit/{id}', 'CurrencyController@postEditMatch'); //编辑交易对
        Route::any('currency/match_del/{id}', 'CurrencyController@delMatch')->middleware(['demo_limit']); //删除交易对
        //板块管理
        Route::get('/currency_plates/index', 'CurrencyPlatesController@index');
        Route::get('/currency_plates/list', 'CurrencyPlatesController@list');
        Route::get('/currency_plates/add', 'CurrencyPlatesController@add');
        Route::post('/currency_plates/postadd', 'CurrencyPlatesController@postadd');
        Route::post('/currency_plates/is_show', 'CurrencyPlatesController@showStatus');
        Route::post('/currency_plates/del', 'CurrencyPlatesController@delete');
    });

    //APP版本管理
    Route::group([], function () {
        Route::get('app_version', 'AppVersionController@index');//首页
        Route::get('app_version_add', 'AppVersionController@add');//添加版本
        Route::post('app_version_add', 'AppVersionController@postAdd');//添加版本
        Route::get('app_version_list', 'AppVersionController@lists');//版本列表
        Route::post('app_version_del', 'AppVersionController@delete');//删除版本
    });

    //杠杆交易风险率
    Route::prefix('hazard')->group(function () {
        Route::get('index', 'HazardRateController@index');
        Route::get('lists', 'HazardRateController@lists');
        Route::get('total', 'HazardRateController@total');
        Route::get('total_lists', 'HazardRateController@totalLists');
        Route::get('handle', 'HazardRateController@handle');
        Route::post('handle', 'HazardRateController@postHandle');
    });

    //杠杆做单列表
    Route::prefix('lever')->group(function () {
        Route::get('index', 'LeverTransactionController@index');
        Route::get('lists', 'LeverTransactionController@lists');
    });

    //杠杆交易倍数手数设置
    Route::prefix('levermultiple')->group(function () {
        Route::get('index', 'LeverMultipleController@index');
        Route::get('list', 'LeverMultipleController@lists');
        Route::post('del', 'LeverMultipleController@del');
        Route::any('edit', 'LeverMultipleController@edit');
        Route::any('doedit', 'LeverMultipleController@doedit');
        Route::any('add', 'LeverMultipleController@add');
        Route::any('doadd', 'LeverMultipleController@doadd');
    });

    //短信模板管理
    Route::prefix('sms_project')->group(function () {
        Route::get('index', 'SmsProjectController@index');//首页
        Route::get('add', 'SmsProjectController@add');//添加模板
        Route::post('add', 'SmsProjectController@postAdd');//保存模板
        Route::get('edit', 'SmsProjectController@edit');//编辑模板
        Route::post('del', 'SmsProjectController@del');//删除模板
        Route::get('lists', 'SmsProjectController@lists');//模板列表数据
        Route::get('send_test', 'SmsProjectController@send_test');//测试模板界面
        Route::post('send_sms', 'SmsProjectController@send_sms');//测试模板
    });
    //机器人
    Route::prefix('robot')->group(function () {
        Route::get('auto_robot', 'AutoController@robot');
        Route::get('robot_list', 'AutoController@robotList');
        Route::get('robot_add', 'AutoController@robotAdd');
        Route::post('robot_add', 'AutoController@postRobotAdd');
        Route::post('newauto_start', 'AutoController@newPostStart'); //打开关闭机器人
        Route::post('auto_robot_del', 'AutoController@autoRobotDel'); //删除
    });
    
    //JAVA机器人
    Route::prefix('javarobot')->group(function () {
        Route::get('index', 'JavaRobotController@index');
        Route::get('lists', 'JavaRobotController@lists');
        Route::get('add', 'JavaRobotController@add');
        Route::post('add', 'JavaRobotController@postAdd');
        Route::post('change_start', 'JavaRobotController@changeStart'); //打开关闭机器人
        Route::post('del', 'JavaRobotController@del'); //删除
        Route::post('cancel', 'JavaRobotController@cancel'); //撤单机器人所有单
    });

    //手续费
    Route::get('poundage_index', 'PoundageController@index');//首页
    Route::get('poundage/lists', 'PoundageController@lists');//列表
    Route::get('poundage/sum', 'PoundageController@sum');//列表

    //好用户统计
    Route::group([], function () {
        Route::get('/good_user/index', 'GoodUserController@index');
        Route::post('/good_user/data', 'GoodUserController@data');
    });
    
    //秒合约数量设置
        Route::get('/micro_number_index', function () {
            return view('admin.micro.index');
        });
        Route::get('/micro_number_add', 'MicroController@add');//添加设置
        Route::post('/micro_number_add', 'MicroController@postAdd');//添加设置
        Route::get('/micro_numbers_list', 'MicroController@lists');
        Route::post('/micro_number_del', 'MicroController@del');
        Route::get('/micro_order_edit', 'Admin\MicroController@edit');
        Route::post('/micro_order_edit', 'Admin\MicroController@editPost');
        //秒合约秒数设置
        Route::get('/micro_seconds_index', function () {
            return view('admin.micro.seconds_index');
        });

        Route::get('/micro_seconds_add', 'MicroController@secondsAdd');//添加设置
        Route::post('/micro_seconds_add', 'MicroController@secondsPostAdd');//添加设置
        Route::get('/micro_seconds_list', 'MicroController@secondsLists');
        Route::post('/micro_seconds_status', 'MicroController@secondsStatus');
        Route::post('/micro_seconds_del', 'MicroController@secondsDel');
        //秒合约日志
        Route::get('/micro_order', 'MicroController@order');
        Route::get('/micro_order_list', 'MicroController@orderList');
        Route::get('/micro_order_edit', 'MicroController@edit');
        Route::post('/micro_order_edit', 'MicroController@editPost');
        Route::post('/micro/batch_risk', 'MicroController@batchRisk');
        Route::get('/micro_config', 'MicroController@config');//秒合约设置
        Route::get('/micro_config_index', 'MicroController@configIndex');//秒合约设置
        Route::get('/micro_config_edit', 'MicroController@configEdit');//秒合约设置
        Route::post('/micro_config_doedit', 'MicroController@configdoEdit');//秒合约设置
        Route::post('/micro_config_status', 'MicroController@microconfigStatus');//秒合约设置

        Route::get('/micro_amount', 'AdminMicroAmountController@index');//秒合约快捷数量
        Route::any('/amountListAjax', 'AdminMicroAmountController@amountListAjax');//秒合约快捷数量

    Route::get('/micro_amount', 'AdminMicroAmountController@index');//秒合约快捷数量
    Route::get('/amount_add', 'AdminMicroAmountController@amount_add');//秒合约快捷数量





        Route::get('/lockmining', 'LockController@config');//锁仓挖矿
        Route::get('/lock_config_index', 'LockController@configIndex');//锁仓挖矿
        Route::post('/lock_config_status', 'LockController@lcokconfigStatus');
        Route::get('/lock_config_edit', 'LockController@configEdit');//秒合约设置
        Route::get('/lock_config_add', 'LockController@configAdd');//秒合约设置
        Route::post('/lock_config_doedit', 'LockController@configdoEdit');//编辑锁仓挖矿
        Route::post('/lock_config_doadd', 'LockController@configdoAdd');//新增编辑锁仓挖矿
        Route::get('/lockminingorder', 'LockController@order');//秒合约设置
        Route::get('/lock_orderlist', 'LockController@orderList');
        Route::get('/lock_del', 'LockController@del');
        
        Route::get('/blindbox', 'BlindController@config');//盲盒
        Route::get('/blind_config_index', 'BlindController@configIndex');//锁仓挖矿
        Route::get('/blind_config_add', 'BlindController@configAdd');//添加盲盒
        Route::post('/blind_config_doadd', 'BlindController@configdoAdd');//
        Route::get('/blind_config_edit', 'BlindController@configEdit');//
        Route::post('/blind_config_doedit', 'BlindController@configdoEdit');
        Route::get('/blindboxorder', 'BlindController@order');
        Route::get('/blind_orderlist', 'BlindController@orderList');
        Route::post('/blind_config_status', 'BlindController@blindconfigStatus');
        Route::get('/blind_del', 'BlindController@del');
     
        
        
        
        
        
        
        
        
                //保险设置
        Route::get('/insurance_rules_index', function () {
            return view('admin.insurancerule.index');
        });
        Route::get('/insurance_rules_add', 'InsuranceRuleController@add');//添加设置
        Route::post('/insurance_rules_add', 'InsuranceRuleController@postAdd');//添加设置
        Route::get('/insurance_rules_list', 'InsuranceRuleController@lists');
        Route::post('/insurance_rules_del', 'InsuranceRuleController@del');
        
        Route::group(['prefix' => 'insurance'], function () {
            Route::get('index', 'InsuranceController@index');//险种管理
            Route::get('lists', 'InsuranceController@lists');
            Route::get('add', 'InsuranceController@add');
            Route::post('add', 'InsuranceController@postAdd');
            Route::post('del', 'InsuranceController@del');
            Route::post('change_auto_claim', 'InsuranceController@changeAutoClaim');
            Route::post('change_status', 'InsuranceController@changeStatus');
            Route::post('change_t_add_1', 'InsuranceController@changeTAdd1');

            Route::get('order_index', 'InsuranceController@orderIndex');
            Route::get('order_lists', 'InsuranceController@orderLists');
        });
});
