<?php

//代理商管理员操作后台
Route::namespace('Agent')->group(function () {
    Route::get('agent/index', 'AgentController@index')->name('agent');
    Route::get('agent', 'AgentController@agent');
    Route::post('agent/login', 'MemberController@login');//登录
    Route::any('order/order_excel', 'OrderController@order_excel');//导出订单记录Excel
    Route::any('agent/users_excel', 'OrderController@user_excel');//导出用户记录Excel
    Route::any('agent/dojie', 'ReportController@dojie');//阶段订单图表
});

//管理后台
Route::prefix('agent')->namespace('Agent')->middleware(['agent_auth'])->group(function () {
    Route::get('home', 'ReportController@home');//主页
    Route::get('user/index', 'UserController@index');//用户管理列表
    Route::get('salesmen/index', 'UserController@salesmenIndex');//代理商管理列表
    Route::get('salesmen/add', 'UserController@salesmenAdd');//添加代理商页面

    Route::get('transfer/index', 'UserController@transferIndex');//出入金列表页
    Route::get('set_password', 'MemberController@setPas');//修改密码
    Route::get('set_info', 'MemberController@setInfo');//基本信息

    Route::get('order_statistics', 'ReportController@orderSt');//订单统计
    Route::get('user_statistics', 'ReportController@userSt');//用户统计
    Route::get('money_statistics', 'ReportController@moneySt');//收益统计
    //首页
    Route::any('get_statistics', 'AgentIndexController@getStatistics');//首页获取统计信息

    Route::post('change_password', 'MemberController@changePWD');//修改密码

    Route::get('user_info', 'MemberController@getUserInfo');//获取用户信息
    Route::post('save_user_info', 'MemberController@saveUserInfo');//保存用户信息
    Route::any('lists', 'MemberController@lists');//代理商列表
    Route::post('addagent', 'MemberController@addAgent');//添加代理商
    Route::post('addsonagent', 'MemberController@addSonAgent');//给代理商添加代理商
    Route::post('update', 'MemberController@updateAgent');//添加代理商
    Route::post('searchuser', 'MemberController@searchuser');//查询用户
    Route::post('search_agent_son', 'MemberController@search_agent_son');//查询用户

    Route::any('logout', 'MemberController@logout');//退出登录
    Route::any('menu', 'MemberController@getMenu');//获取指定身份的菜单

    Route::post('jie', 'ReportController@jie');//阶段订单图表

    Route::post('day', 'ReportController@day');//阶段订单图表

    Route::post('order', 'ReportController@order');//阶段订单图表
    Route::post('order_num', 'ReportController@order_num');//阶段订单图表
    Route::post('order_money', 'ReportController@order_money');//阶段订单图表

    Route::post('user', 'ReportController@user');//阶段用户图表
    Route::post('user_num', 'ReportController@user_num');//阶段订单图表
    Route::post('user_money', 'ReportController@user_money');//阶段订单图表

    Route::post('agental', 'ReportController@agental');//阶段订单图表
    Route::post('agental_t', 'ReportController@agental_t');//阶段订单图表
    Route::post('agental_s', 'ReportController@agental_s');//阶段订单图表
    
    Route::get('order/micro_index', 'OrderController@microIndex');//秒合约订单页面
    Route::get('order/micro_order_list', 'OrderController@micro_order_list');//秒合约所有订单

    Route::get('order/lever_index', 'OrderController@leverIndex');//杠杆订单页面
    Route::post('order/list', 'OrderController@order_list');//团队所有订单
    Route::get('order/info', 'OrderController@order_info');//订单详情

    //撮合订单
    Route::get('order/transaction_index', 'OrderController@transactionIndex');
    Route::get('order/transaction_list', 'OrderController@transactionList');
    Route::get('order/jie_index', 'OrderController@jieIndex');


    Route::post('jie/list', 'OrderController@jie_list');//团队所有结算
    Route::post('jie/info', 'OrderController@jie_info');//结算详情

    Route::post('get_order_account' , 'OrderController@get_order_account');
    Route::post('get_user_num' , 'UserController@get_user_num');
    Route::post('get_my_invite_code' , 'UserController@get_my_invite_code');

    Route::any('user/lists', 'UserController@lists');//用户列表
    Route::any('lever_transaction/lists', 'LeverTransactionController@lists');//用户的订单
    Route::any('account/money_log', 'AccountController@moneyLog');//结算
    Route::any('agent/info', 'AgentController@info');//代理商信息

    //划转出入列表
    Route::any('user/huazhuan_lists', 'UserController@huazhuan_lists');//用recharge户列表

    //出入金（充币、提币)
    Route::any('recharge/index', 'CapitalController@rechargeIndex');
    Route::any('withdraw/index', 'CapitalController@withdrawIndex');
    Route::get('capital/recharge', 'CapitalController@rechargeList');
    Route::get('capital/withdraw', 'CapitalController@withdrawList');
    Route::any('recharge/v2_tj', 'CapitalController@v2_tj');
    Route::any('recharge/v2_jj', 'CapitalController@v2_jj');
    
    Route::get('capital/cashb_show', 'CapitalController@show');//提币详情页面
    Route::post('capital/cashb_done', 'CapitalController@done');//确认提币成功
    Route::get('capital/cashb_back', 'CapitalController@back');//执行退回申请
    Route::post('capital/cashb_edit_address', 'CapitalController@editAddress');//提币编辑页面

    //用户资金
    Route::get('user/users_wallet', 'CapitalController@wallet');
    Route::get('users_wallet_total', 'CapitalController@wallettotalList');

    //用户订单
    Route::get('user/lever_order', 'OrderController@userLeverIndex');
    Route::get('user/lever_order_list', 'OrderController@userLeverList');
     Route::get('user/micro_order', 'OrderController@userMicroIndex');
    Route::get('user/micro_order_list', 'OrderController@userMicroList');

    //结算 提现到账
    Route::post('wallet_out/done', 'CapitalController@walletOut');
    
    //实名认证管理
    Route::get('user/real_index', 'UserRealController@index');
    Route::get('user/real_list', 'UserRealController@list');
    Route::get('user/real_info', 'UserRealController@detail');
    Route::post('user/real_del', 'UserRealController@del');
    Route::post('user/real_auth', 'UserRealController@auth');
    Route::post('user/pass_auth', 'UserRealController@passauth');
    Route::post('user/refuse_auth', 'UserRealController@refuseauth');
    
    // 贷款
    // 贷款订单
    Route::get('loan_order_index', 'LoanOrderController@index');
    Route::get('loan_order_list', 'LoanOrderController@lists');
    Route::post('loan_order_pass', 'LoanOrderController@postPass');
    Route::post('loan_order_reject', 'LoanOrderController@postReject');
    Route::post('loan_order_del', 'LoanOrderController@postDel');
    // 还款订单
    Route::get('repayment_record_index', 'RepaymentRecordController@index');
    Route::get('repayment_record_list', 'RepaymentRecordController@lists');
    Route::post('repayment_record_pass', 'RepaymentRecordController@postPass');
    Route::post('repayment_record_reject', 'RepaymentRecordController@postReject');
    Route::post('repayment_record_del', 'RepaymentRecordController@postDel');
    
    Route::get('user/edit', 'UserController@edit');
    Route::post('user/edit', 'UserController@doedit');
    
    Route::get('users_wallet', 'UserController@wallet'); // 用户钱包
    Route::get('user/walletList', 'UserController@walletList');
    Route::post('user/wallet_lock', 'UserController@walletLock');//钱包锁定
    Route::post('user/delw', 'UserController@delw')->middleware(['demo_limit']); //删除指定id钱包
    
    Route::get('user/conf', 'UserController@conf');
    Route::post('user/conf', 'UserController@postConf')->middleware(['demo_limit']);//调节钱包账户
    Route::get('user/address', 'UserController@address');//提币地址信息
    Route::post('user/address_edit', 'UserController@addressEdit');//修改地址信息
    Route::get('user/address_list', 'UserController@addressList');//提币地址信息
    Route::get('user/send_mail_index', 'UserController@sendMailIndex');
    Route::post('user/send_mail', 'UserController@sendMail');
    
    Route::get('user/editFKRatio', 'UserController@editFKRatio');
    Route::post('user/updateFKRatio', 'UserController@updateFKRatio');
    
    Route::get('user/recharge_usdt', 'UserController@rechargeUsdt');
    Route::get('user/withdraw_usdt', 'UserController@withdrawUsdt');
    
    Route::prefix('wallet')->group(function () {
        Route::get('index', 'WalletController@index'); //钱包管理页面
        Route::get('list', 'WalletController@lists'); //钱包列表搜索
        Route::get('make', 'WalletController@makeWallet'); //生成钱包
        Route::get('update_balance', 'WalletController@updateBalance'); //更新链上余额
        Route::get('transfer_poundage', 'WalletController@transferPoundage'); //打入手续费
        Route::get('collect', 'WalletController@collect'); //余额归拢
    });
    
    Route::get('/lockminingorder', 'LockController@order');//秒合约设置
    Route::get('/lock_orderlist', 'LockController@orderList');
    Route::get('/lock_del', 'LockController@del');
    
    Route::prefix('account')->group(function () {
        Route::get('account_index', 'AccountLogController@index');
        Route::get('list', 'AccountLogController@lists');
        Route::get('viewDetail', 'AccountLogController@view');
     });
    
});

 
