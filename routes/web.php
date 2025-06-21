<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
//Route::get('/', function () {
  //  return redirect('/dist');
//});

// 无api前缀无需登录的
Route::namespace('Api')->group(function () {
    Route::get('/setCNYFn', 'DefaultController@setCNYFn'); // 首页跳转到pc
    Route::get('/getCNYFn', 'DefaultController@getCNYFn'); // 首页跳转到pc

    Route::get('/testRedis', 'DefaultController@testRedis'); // 首页跳转到pc
 
    Route::get('/hb_index', 'HbController@index'); // 首页跳转到pc

    Route::get('/test', 'DefaultController@jumpDist'); // 首页跳转到pc
    Route::get('/syncnotice', 'NoticesController@syncnotice'); // 异步通知
    Route::any('update', 'DefaultController@checkUpdate')->middleware(['cross']);
    Route::any('user/walletaddress', 'UserController@walletaddress'); //钱包地址

    Route::get('/getBinancePrice', 'TestController@getBinancePrice'); //获取币安最新价格
    
    
    Route::post('/payNotify', 'PayController@payNotify'); // 支付回调
    
    
});

// api前缀无需登录的
Route::prefix('api')->namespace('Api')->group(function () {
    
    Route::get('/getHbList', 'HbController@getHbList'); // 获取币种列表
    Route::get('/getHbDetail', 'HbController@getHbDetail'); // 获取币种详情
    Route::get('/getHbHistoryKline', 'HbController@getHbHistoryKline'); // 获取币种k线图历史记录
    Route::get('/getHbDepth', 'HbController@getHbDepth'); // 获取币种行情深度数据
    Route::get('/getHbHistoryTrade', 'HbController@getHbHistoryTrade'); // 获取市场最近成交记录
    Route::get('/getHbFloatPriceList', 'HbController@getHbFloatPriceList'); // 获取浮动价格列表
    
    Route::post('/resetData', 'UserController@resetData'); // 测试
    
    
    
    Route::any('updateOrderFn', 'DefaultController@updateOrderFn');
    
     Route::any('updateOrderFn2', 'DefaultController@updateOrderFn2');
    
    
   Route::any('/v2/forexList', 'StockController@forexList'); // 首页跳转到pc
    Route::any('/v2/stockList', 'StockController@stockList'); // 首页跳转到pc

    Route::any('/v2/kline', 'StockController@kline'); // 首页跳转到pc
    
    Route::any('/stock/overview', 'StockCompanyController@overview');
    Route::any('/stock/news', 'StockCompanyController@news');
    
    
    Route::get('/v1/common/pledgeCoinList', 'DappController@pledgeCoinList'); // 首页跳转到pc
    
      
    Route::get('/getAppConfig', 'DefaultController@getAppConfig'); //获取任意配置
    
    Route::get('/getRegConfig', 'DefaultController@getRegConfig'); //获取任意配置

    Route::get('/handleAction', 'HqPushController@handleAction'); // 首页跳转到pc

     Route::any('/contractFee', 'LeverController@contractFee');
    
   Route::any('/saveHq', 'Hq5Controller@saveHq');
    Route::any('/updateHq', 'Hq5Controller@updateHq');
    Route::any('/findKline', 'Hq5Controller@findKline');
    
    
    //双币理财
        Route::get('/dual/index', 'DualController@index');
        Route::get('/dual/detail', 'DualController@getDetail');
        Route::post('/dual/buyDual', 'DualController@buyDual');
        Route::post('/dual/dual_list', 'DualController@dual_list');
        
        
    Route::get('follow/index', 'FollowController@index');   //跟单中心
    Route::post('follow/follow', 'FollowController@follow');   //跟随
    Route::post('follow/cancel', 'FollowController@cancel');   //取消跟随
    Route::get('follow/traderDetail', 'FollowController@traderDetail'); //交易员详情
    Route::post('follow/selfHolding', 'FollowController@selfHolding');   //转自持
    Route::get('follow/historyTrade', 'FollowController@historyTrade');   //历史交易
    
    
    Route::any('/lh/deposit/config', 'LockOrderController@getV3LockRecommendList');
    Route::get('/menu', 'DefaultController@getMenu');
    
    Route::get('/menu2', 'DefaultController@getMenu2');

    Route::any('/currency/new_timeshar_v5',
        'CurrencyController@klineMarketV5')->middleware(['cross']); //K线分时数据，对接tradeingview

    Route::any('/currency/huobiKlineV6',
        'CurrencyController@huobiKlineV6')->middleware(['cross']); //K线分时数据，对接tradeingview
    
    
    Route::any('/currency/new_timeshar_v2',
        'CurrencyController@klineMarketV2')->middleware(['cross']); //K线分时数据，对接tradeingview
        
        
    
    Route::any('getCZDataV2', 'RechargeDataV2Controller@index');
    Route::any('/hb/hq', 'HuobiController@hq');
    
    Route::any('/shouyiV2', 'LockOrderController@shouyiFn');

    Route::any('/saveGroupMiningRewardV2023', 'LockOrderController@saveGroupMiningReward');
    
    Route::any('/saveCollection', 'UserCollectionController@saveCollection');
    Route::any('/delCollection', 'UserCollectionController@delCollection');
    
    Route::any('/isCollect', 'UserCollectionController@isCollect');
    Route::any('/myhqCollect', 'UserCollectionController@myhqCollect');
    
    
    Route::get('/project', 'CurrencyProjectController@projectList');
    Route::get('/project/detail', 'CurrencyProjectController@projectDetail');
    
    Route::get('/lock_mining/detail', 'LockOrderController@getDetail');
    Route::get('/lock_mining/bigData', 'LockOrderController@bigData');
    
    Route::get('/getCount', 'DefaultController@getRechargeCount');
    Route::get('/getCounts', 'DefaultController@getRechargeCounts');
    Route::get('/getReal', 'DefaultController@getRelCount');
    Route::get('/getCashb', 'DefaultController@getCashbCount');
  
    Route::get('env.json', 'DefaultController@env')->middleware(['cross']); //取env.json
    Route::post('lang/get', 'DefaultController@getlang')->middleware(['cross']);
    Route::post('lang/set', 'DefaultController@setLang')->middleware(['cross']);
    Route::get('get_version', 'DefaultController@getVersion'); //获取版本号
    Route::any('block', 'CommonController@block')->middleware(['valid_chain_push']); // 区块交易推送接口

    Route::any('upload', 'DefaultController@upload'); //上传图片接口
    Route::any('base64_upload', 'DefaultController@base64ImageUpload')->middleware(['cross']);; //base64上传图片接口

    Route::any('market/get_current', 'CurrencyController@getCurrentMarket')->middleware(['cross']);
    Route::post('exchange/shift_to', 'UserController@shiftToByExchange')->middleware(['cross']);

    Route::prefix('user')->group(function () {

        Route::post('updatePasswordV2', 'LoginController@updatePasswordV2'); //登录

        Route::post('login', 'LoginController@login'); //登录
        Route::post('register', 'LoginController@register'); //注册
        Route::post('forget', 'LoginController@forgetPassword'); //忘记密码
        Route::post('check_mobile', 'LoginController@checkMobileCode'); //验证短信验证码
        Route::post('check_email', 'LoginController@checkEmailCode'); //验证邮件验证码
        Route::post('walletRegister', 'LoginController@walletRegister'); //钱包注册
        Route::post('test', 'LoginController@test'); //钱包注册
        
        Route::post('updatePayPassword', 'UserController@updatePayPassword'); //设置提现密码
        Route::post('updateNickname', 'LoginController@updateNickname'); //设置提现密码
        
        Route::post('savePaymentMethod', 'PaymentMethodController@savePaymentMethod'); //设置提现密码
        Route::post('getList', 'PaymentMethodController@getList'); //设置提现密码
        Route::get('getPaymentMethod', 'PaymentMethodController@getPaymentMethod'); //提现密码详情
        Route::post('editPaymentMethod', 'PaymentMethodController@editPaymentMethod'); //编辑提现密码
        Route::post('delPaymentMethod', 'PaymentMethodController@delPaymentMethod'); //删除提现密码
        
        Route::post('saveBankcard', 'BankcardController@saveBankcard'); //设置提现密码
        Route::post('bankcard/getList', 'BankcardController@getList'); //设置提现密码
        
        Route::post('saveBankcard2', 'BankcardController@saveBankcard2'); //设置提现密码
        
        Route::get('mailList', 'UserController@mailList'); //获取站内信列表
        Route::get('mailDetail', 'UserController@mailDetail'); //获取站内信详情
        Route::post('mailRead', 'UserController@mailRead'); //获取站内信已读
        
        Route::get('recharge_address_list', 'UserController@rechargeAddressList'); //获取充值地址
        
    });

    Route::prefix('news')->group(function () {
        Route::post('list', 'NewsController@getArticle'); //获取文章列表
        Route::post('detail', 'NewsController@get'); //获取文章详情
        Route::post('help', 'NewsController@getCategory'); //帮助中心分类
        Route::post('recommend', 'NewsController@recommend'); //推荐文章
        Route::post('get_invite_return_news', 'NewsController@getInviteReturn'); //获取邀请规则详情
        Route::post('online', 'NewsController@onlineService'); //获取在线客服
        Route::post('partner','NewsController@partnerService');//合作网站
        Route::any('popupNews', 'NewsController@popupNews');
        Route::get('level_list', 'NewsController@levelList'); // vip介绍列表
    });

    Route::post('sms_send', 'SmsController@smsSend')->middleware('throttle:30:1'); //获取短信验证码
    Route::post('sms_mail', 'SmsController@send_mail_message'); //获取邮箱验证码
    
    Route::post('sendMail4Login', 'SmsController@sendMail4Login'); //登录之前发送验证 需要先检查email是否存在

    Route::post('transaction/legal_list', 'TransactionController@legalList'); //法币交易市场
    Route::get('seller_list', 'SellerController@lists'); //商家列表

    Route::get('legal_deal_platform', 'LegalDealController@legalDealPlatform')->middleware(['demo_limit']); //商家发布法币交易信息列表
    Route::get('c2c_deal_platform', 'C2cDealController@legalDealPlatform')->middleware(['demo_limit']); //用户发布c2c法币交易信息列表
    Route::post('deal/info', 'CurrencyController@dealInfo'); //行情详情

    Route::prefix('currency')->group(function () {
        Route::any('new_tradehistory',
            'CurrencyController@tradeMarket')->middleware(['cross']);
            
            
        Route::get('list', 'CurrencyController@lists'); //币种列表
        Route::get('quotation', 'CurrencyController@quotation'); //币种列表带行情
        Route::any('quotation_new', 'CurrencyController@newQuotation'); //币种列表带行情(支持交易对)
        
        Route::any('quotation_new_home', 'CurrencyController@newQuotationHome'); //币种列表带行情(支持交易对) 2023-04-27
        
        Route::any('plates', 'CurrencyController@plates'); //币种版块
        Route::any('new_timeshar', 'CurrencyController@klineMarket')->middleware(['cross']); //K线分时数据，对接tradeingview
        Route::any('kline_market', 'CurrencyController@klineMarket')->middleware(['cross']); //K线分时数据，对接tradeingview
        Route::any('timeshar', 'CurrencyController@timeshar'); //分时
        Route::any('write_kline', 'CurrencyController@writeEsearchKline')->middleware(['cross']); //JAVA写入K线
        Route::any('java_data', 'CurrencyController@javaData')->middleware(['cross']); //对接JAVA撮合引擎的数据转发
        Route::get('lever', 'CurrencyController@lever'); //行情详情
    });

    Route::get('getLtcKMB', 'WalletController@getLtcKMB');
    Route::post('getNode', 'DefaultController@getNode'); //节点关系
    Route::get('ltcGet', 'WalletController@ltcGet'); //钱包获取交易所的转账
    Route::get('logo','DefaultController@getlogo');
    
    Route::get('getWebSiteConfig', 'DefaultController@getWebSiteConfig');
    
});

//******************************api接口需要登录的**********************
Route::prefix('api')->namespace('Api')->middleware('check_api')->group(function () {
    
    Route::post('/createTransaction', 'PayController@createTransaction'); // 支付
      
    Route::get('/walletList2024', 'Wallet2024Controller@walletList');
    
    Route::any('/setOnline', 'Wallet2024Controller@setOnline');
    Route::any('/setOffline', 'Wallet2024Controller@setOffline');
    
    
    Route::any('rebateRulesList', 'RebateRulesController@index');
    
    Route::any('czRewardList', 'ApiCzRewardGroupController@czRewardList');
    
    Route::any('/c2c/saveMerchantApply', 'SellerController@saveMerchantApply')->middleware(['demo_limit']);//申请成为商家


    Route::any('getGInfo', 'GAuthController@getGInfo');
     Route::any('bindGoogle', 'GAuthController@bindGoogle');
     
    
    Route::post('/user/getTransferList', 'UserController@getTransferList');//获取币币转换类型
    
    Route::post('/user/Transfer', 'UserController@Transfer');//币币转换
    
    Route::get('/currency/user_currency_list', 'CurrencyController@userCurrencyList');
    
    Route::post('wallet/change2', 'WalletController@changeWallet2');
    Route::post('wallet/getBalanceByCoin', 'WalletController@getBalanceByCoin');
    
    Route::any('wallet/legalConvert2CNY', 'LegalController@legalConvert2CNY');
    
    Route::any('wallet/legalConvert2USDT', 'LegalController@legalConvert2USDT');
    Route::any('wallet/getLegalVal', 'LegalController@getLegalVal');
    
    Route::any('wallet/getLegalAssets', 'WalletController@getLegalAssets');
    
     Route::any('wallet/getEnLegalVal', 'LegalController@getEnLegalVal');
     Route::any('wallet/legalConvertEn2USDT', 'LegalController@legalConvertEn2USDT');
      Route::any('wallet/legalConvertEn2GBP', 'LegalController@legalConvertEn2GBP');
    
    
    // 自选列表
        Route::get('optional/list', 'OptionalController@list');
        // 添加自选
        Route::post('optional/add', 'OptionalController@add');
        // 移除自选
        Route::post('optional/del', 'OptionalController@del');
        
        Route::any('getCZAddressEn', 'AccountController@getCZAddressEn');
        
        Route::any('getCZAddressV2', 'AccountController@getCZAddressV2');
        Route::any('rechargeRecordV2', 'AccountController@rechargeRecordV2');
        
        Route::any('todayProfitLoss', 'AccountController@todayProfitLoss');
        
    
    Route::any('getCZAddress', 'AccountController@getCZAddress');
    Route::any('getRepaymentAddress', 'AccountController@getRepaymentAddress');

    Route::any('incomeLogs', 'LockOrderIncomeController@logs');
    
     Route::get('/team/index', 'TeamController@index');
     
     Route::any('/financial/earlyRedemption', 'FinancialController@earlyRedemption');
     
     Route::any('/hasSetPayPwd', 'UserController@hasSetPayPwd');
     Route::any('/updatePayPwdFirst', 'UserController@updatePayPwdFirst');
     
    Route::any('/ex_v2', 'ExchangeController@ex_v2');
    
    Route::any('/doExchange', 'AssetsExchangeController@doExchange');
    Route::any('/initUserData', 'AssetsExchangeController@initUserData');
    
    //退出登录
    Route::any('logout', 'UserController@logout');
    Route::any('/profits/show_profits', 'AccountController@show_profits'); //盈亏返还记录
    Route::post('/checkpassword', 'UserController@checkPayPassword')->middleware(['lang_check']); //验证法币交易密码
    Route::post('lang/set1', 'DefaultController@setLang1');
    Route::get('index', 'DefaultController@index');
    //发送短信
    Route::post('vip', 'UserController@vip');

    Route::any('/user_match/list', 'UserMatchController@lists');
    Route::any('/user_match/add', 'UserMatchController@add');
    Route::any('/user_match/del', 'UserMatchController@del');
    
    Route::get('user/project/order', 'CurrencyProjectController@userOrder');
    
    Route::post('project/order', 'CurrencyProjectController@postOrder');

    Route::post('/historical_data', 'DefaultController@historicalData');
    Route::post('/quotation', 'DefaultController@quotation');
    Route::post('/quotation/info', 'DefaultController@quotationInfo');

    //反馈建议
    Route::prefix('feedback')->group(function () {
        Route::post('list', 'FeedBackController@myFeedBackList'); //反馈信息列表
        Route::post('detail', 'FeedBackController@feedBackDetail'); //反馈信息内容，包括回复信息
        Route::post('add', 'FeedBackController@feedBackAdd'); //添加反馈信息
    });

    //安全中心
    Route::prefix('safe')->group(function () {
        
        Route::post('safe_center', 'UserController@safeCenter'); //安全中心绑定信息
        Route::post('gesture_add', 'UserController@gesturePassAdd'); //添加手势密码
        Route::post('gesture_del', 'UserController@gesturePassDel'); //删除手势密码
        Route::post('update_password', 'UserController@updatePayPassword')->middleware(['lang_check']); //修改交易密码
        Route::post('mobile', 'UserController@setMobile'); //绑定电话
        Route::post('email', 'UserController@setEmail'); //绑定邮箱
    });

    //钱包相关
    Route::prefix('wallet')->group(function () {
        
         Route::post('legalWalletList', 'WalletV2Controller@legalWalletList'); //用户账户资产信息
        Route::post('spotWalletList', 'WalletV2Controller@spotWalletList'); //用户账户资产信息
        Route::post('contractsWalletList', 'WalletV2Controller@contractsWalletList'); //用户账户资产信息
        Route::post('optionWalletList', 'WalletV2Controller@optionWalletList'); //用户账户资产信息
        Route::post('earnWalletList', 'WalletV2Controller@earnWalletList'); //用户账户资产信息
         
         
        //资产
        Route::any('get_currency','WalletController@getCurrency');
        Route::post('list', 'WalletController@walletList'); //用户账户资产信息
        Route::post('detail', 'WalletController@getWalletDetail'); //用户账户资产详情
        Route::post('change', 'WalletController@changeWallet')->middleware(['demo_limit']); //账户划转
        Route::post('transfer', 'WalletController@accountTransfer')->middleware(['demo_limit','lang_check']); //账户划转
        Route::any('hzhistory', 'WalletController@hzhistory'); //账户历史记录
        Route::post('get_info', 'WalletController@getCurrencyInfo'); //获取提币信息
        Route::post('get_address', 'WalletController@getAddressByCurrency')->middleware(['lang_check']); //获取提币地址
        Route::post('getaddress', 'WalletController@getAddress'); //获取提币地址
        //Route::post('out', 'WalletController@postWalletOut')->middleware(['demo_limit', 'validate_user_locked', 'check_pay_password:withdraw','lang_check','check_user']); //提交提币信息
        
        Route::post('out', 'WalletController@postWalletOut')->middleware(['demo_limit', 'validate_user_locked','lang_check','check_user']); //提交提币信息
        
        
        Route::post('get_in_address', 'WalletController@getWalletAddressIn')->middleware(['demo_limit']); //充币地址
        Route::any('legal_log', 'WalletController@legalLog'); //财务记录
        Route::any('out_log', 'WalletController@walletOutLog'); //提币记录
        Route::get('currencylist', 'WalletController@currencyList'); //币种列表
        Route::post('addaddress', 'WalletController@addAddress')->middleware(['lang_check']); //添加提币地址
        Route::post('deladdress', 'WalletController@addressDel')->middleware(['lang_check']); //删除提币地址
        Route::post('ltcSend', 'WalletController@ltcSend')->middleware(['demo_limit']);
        Route::post('real_name', 'UserController@walletRealName'); //钱包身份认证
        Route::any('coin_wallet', 'WalletController@coinWallet'); //个人钱包
        
        Route::any('withdrawLogList', 'WalletController@withdrawLogList'); //提币明细列表
        Route::post('withdrawLogDetail', 'WalletController@withdrawLogDetail'); //提币明细列表
    });

    // 站内转账
    Route::prefix('transfer')->group(function () {
        Route::get('currencies', 'TransferController@getAllowTransferFee');
        Route::post('submit', 'TransferController@submit');
        Route::get('logs', 'TransferController@logs');
    });
    
     //锁仓资产
    Route::prefix('lockorder')->group(function () {
        Route::get('getorder', 'LockOrderController@getOrder');
        Route::post('submit', 'LockOrderController@submit')->middleware(['lang_check']);
        Route::get('lists', 'LockOrderController@lists');
        Route::get('wallet', 'LockOrderController@wallet');
    });
    
    // 贷款 
     Route::prefix('loan')->group(function () {
         Route::get('fee', 'LoanController@getLoanFee'); // 获取贷款费率
        Route::get('setting_list', 'LoanController@getSettingList'); // 获取贷款设置列表
        Route::post('submit', 'LoanController@submit'); // 提交贷款
        Route::get('list', 'LoanController@getList'); // 贷款列表
        Route::get('detail', 'LoanController@getDetail'); // 贷款详情
        Route::post('repayment', 'LoanController@repayment'); // 提交还款
    });

     //盲盒
    Route::prefix('blindbox')->group(function () {
        Route::get('getorder', 'BlindBoxController@getOrder');
        Route::post('submit', 'BlindBoxController@submit')->middleware(['lang_check']);
        Route::get('lists', 'BlindBoxController@lists');
        Route::get('wallet', 'BlindBoxController@wallet');
    });

    // 用户相关
    Route::prefix('user')->group(function () {
        Route::post('update_address', 'UserController@updateAddress'); //更新地址
        Route::post('getuserbyaddress', 'UserController@getUserByAddress'); //根据地址获取用户信息
        Route::post('chat', 'UserController@sendchat'); //发送聊天
        Route::post('chatlist', 'UserController@chatlist'); //发送聊天
        Route::post('invite_list', 'UserController@inviteList')->middleware(['demo_limit']); //邀请返佣榜单
        Route::get('invite', 'UserController@invite')->middleware(['demo_limit']); //我的邀请
        Route::post('my_invite_list', 'UserController@myInviteList')->middleware(['demo_limit']); //我的邀请会员列表
        Route::post('my_account_return', 'UserController@myAccountReturn')->middleware(['demo_limit']); //我的邀请返佣列表
        Route::get('my_poster', 'UserController@posterBg')->middleware(['demo_limit']); //我的专属海报
        Route::get('my_share', 'UserController@share')->middleware(['demo_limit']); //邀请好友分享
        Route::get('info', 'UserController@info'); //我的
        Route::get('usdt', 'UserController@getUsdt'); //我的USDT
        Route::get('center', 'UserController@userCenter'); //个人中心
        Route::post('real_name', 'UserController@realName')->middleware(['demo_limit','lang_check']); //身份认证
        Route::get('logout', 'UserController@logout'); //退出登录
        Route::post('setaccount', 'UserController@setAccount')->middleware(['demo_limit','lang_check']); //设置法币交易账号
        Route::get('into_tra_log', 'UserController@into_tra_log'); //用户转入记录
        Route::get('authorization_code', 'UserController@authCode'); //添加代理商时用户的授权码
        Route::post('cash_info', 'UserController@cashInfo')->middleware(['demo_limit']); //个人收款信息
        Route::post('cash_save', 'UserController@saveCashInfo')->middleware(['demo_limit','lang_check']); //添加修改收款方式
        Route::post('cash_save2', 'UserController@saveCash')->middleware(['demo_limit']); //添加修改收款方式
        Route::post('languagetypes', 'UserController@languagetypes')->middleware(['demo_limit']); //添加修改收款方式
        
        Route::post('batch_risk', 'UserController@batchRisk');
        
        Route::post('primary_certification', 'UserController@realName2');

        Route::get('real/state', 'UserController@realState');
        
        Route::post('uploadHeadPortrait', 'UserController@uploadHeadPortrait');
           
    });

    //C2C相关
    Route::prefix('c2c')->group(function () {
        Route::post('saveAppeal', 'C2cController@saveAppeal')->middleware(['demo_limit','check_api']);
        Route::post('myUserCashInfo', 'C2cController@myUserCashInfo')->middleware(['demo_limit','check_api']);

        Route::post('legalWalletList', 'C2cController@legalWalletList')->middleware(['demo_limit']);

        Route::post('msgList', 'C2cMsgController@msgList')->middleware(['demo_limit']);

        Route::post('userOrderDetail', 'C2cController@userOrderDetail')->middleware(['demo_limit']);

        //商家发布的出售或者求购列表  2023-07-27 21:03
        Route::post('merchantPublishList', 'C2cController@merchantPublishList')->middleware(['demo_limit']);
        //判断用户是否商家
        Route::post('getCurrentUserInfo', 'C2cController@getCurrentUserInfo')->middleware(['demo_limit']);


        Route::get('seller_info', 'C2COrderController@sellerInfo')->middleware(['demo_limit']); //用户c2c店铺详情信息

        Route::get('seller_trade', 'C2COrderController@tradeList')->middleware(['demo_limit']); //我的发布交易列表

        Route::post('buyCoin', 'C2COrderController@buyCoin')->middleware(['demo_limit', 'validate_user_locked']); //购买币不需要交易密码

        Route::post('sellCoin', 'C2COrderController@sellCoin')->middleware(['demo_limit', 'validate_user_locked']); //法币交易信息详情

        Route::post('user_legal_pay_cancel', 'C2COrderController@userLegalDealCancel')->middleware(['demo_limit', 'check_pay_password:c2c']); //C2C交易用户取消订单
        //Route::post('user_legal_pay', 'C2cDealController@userLegalDealPay')->middleware(['demo_limit', 'check_pay_password:c2c']); //C2c交易用户确认付款
        Route::post('user_legal_pay', 'C2COrderController@userLegalDealPay')->middleware(['demo_limit']); //C2c交易用户确认付款RMB

        Route::post('legal_deal_sure', 'C2COrderController@doSure')->middleware(['demo_limit']); //C2C发布者确认收款


        Route::post('legal_deal_user_sure', 'C2COrderController@userDoSure')->middleware(['demo_limit', 'check_pay_password:c2c']); //C2C用户确认收款
        Route::post('back_send', 'C2COrderController@backSend')->middleware(['demo_limit', 'check_pay_password:c2c']); //C2C撤回发布
        Route::get('legal_send_deal_list', 'C2COrderController@legalDealSellerList')->middleware(['demo_limit']); //发布交易列表
    });

    //c2c交易
    Route::post('savePublishInfo', 'C2cDealController@savePublishInfo')->middleware(['validate_user_locked', 'demo_limit']); //用户发布交易信息

    //c2c交易
    Route::post('c2c_send', 'C2cDealController@postSend')->middleware(['validate_user_locked', 'demo_limit', 'check_pay_password:c2c']); //用户发布交易信息


    Route::get('c2c_deal_info', 'C2cDealController@legalDealSendInfo')->middleware(['demo_limit']); //c2c法币交易信息详情
    Route::get('c2c_seller_deal', 'C2cDealController@sellerLegalDealList')->middleware(['demo_limit']); //法币交易商家端交易列表

    Route::get('c2c/userOrderList', 'C2cDealController@userOrderList')->middleware(['demo_limit']); //法币交易用户端交易列表

    Route::get('c2c_deal', 'C2cDealController@legalDealInfo')->middleware(['demo_limit']); //交易详情信息
    //check_pay_password: match
    Route::prefix('transaction')->group(function () {
        Route::post('deal', 'TransactionController@deal'); //deal
        Route::post('in', 'TransactionController@in')->middleware(['validate_user_locked','lang_check','check_user']); //买入
        Route::post('out', 'TransactionController@out')->middleware(['validate_user_locked','lang_check','check_user']); //卖出
        
       Route::post('in_v2024', 'TransactionController@in_v2024')->middleware(['validate_user_locked','lang_check','check_user']); //买入
        Route::post('out_2024', 'TransactionController@out_2024')->middleware(['validate_user_locked','lang_check','check_user']); //卖出
          
          
        Route::post('add', 'TransactionController@add'); //提交交易
        Route::post('list', 'TransactionController@list'); //交易列表
        Route::post('info', 'TransactionController@info'); //交易详情
        Route::get('checkinout', 'TransactionController@checkInOut'); //验证法币交易购买 出售按钮
    });
    
    Route::post('all_transaction_in', 'TransactionController@AllTransactionInList');
    Route::post('all_transaction_out', 'TransactionController@AllTransactionOutList');
    
    
    //交易记录
    Route::post('transaction_in', 'TransactionController@TransactionInList');
    Route::post('transaction_out', 'TransactionController@TransactionOutList');
    Route::post('transaction_complete', 'TransactionController@TransactionCompleteList');
    //Route::post('transaction_del', 'TransactionController@TransactionDel'); //取消交易
    Route::post('transaction_del', 'TransactionController@TransactionDel')->middleware(['lang_check']); //取消交易
    //杠杆交易
    Route::prefix('lever')->group(function () {
        
        Route::any('getLeverBalance', 'LeverController@getLeverBalance'); //杠杆deal
        
        Route::post('deal', 'LeverController@deal')->middleware(['lang_check']); //杠杆deal
        Route::post('dealall', 'LeverController@dealAll')->middleware(['lang_check']); //杠杆全部
        //Route::post('submit', 'LeverController@submit')->middleware(['validate_user_locked','lang_check','check_user']); //杠杆下单

        Route::post('submit', 'LeverV2Controller@submit')->middleware(['validate_user_locked','lang_check','check_user']); //杠杆下单

        Route::any('getMatchInfo', 'LeverV2Controller@getMatchInfo');

        Route::post('close', 'LeverController@close')->middleware(['lang_check']); //杠杆平仓
        Route::post('cancel', 'LeverController@cancelTrade'); //撤销委托(取消)
        Route::post('batch_close', 'LeverController@batchCloseByType')->middleware(['lang_check']); //一键平仓
        Route::post('setstop', 'LeverController@setStopPrice')->middleware(['lang_check']); //设置止盈止损价
        Route::post('my_trade', 'LeverController@myTrade'); //我的交易记录
        
        Route::post('myTradePage', 'LeverController@myTradePage'); //2023-05-09 我的-》合约订单列表
        
        Route::get('getList', 'LeverController@getList'); // 测试
        
    });

    Route::post('/data/graph', 'DefaultController@dataGraph'); //数据图
    Route::post('/account/list', 'AccountController@list')->middleware(['lang_check']); //账目明细
    // Route::post('/account/record', 'AccountController@record'); //提交充币
    Route::post('/account/record', 'AccountController@rechargeRecord')->middleware(['lang_check']); //提交充币
    Route::post('/account/recordlist', 'AccountController@recordList'); //充币列表
    Route::post('/account/record_detail', 'AccountController@recordDetail'); //充币详情
    Route::post('legal_send', 'LegalDealController@postSend')->middleware(['demo_limit', 'validate_user_locked', 'check_pay_password:otc','lang_check']); //商家发布法币交易信息
    Route::get('legal_deal_info', 'LegalDealController@legalDealSendInfo')->middleware(['demo_limit']); //法币交易信息详情
    Route::post('do_legal_deal', 'LegalDealController@doDeal')->middleware(['demo_limit', 'validate_user_locked', 'check_pay_password:otc']); //法币交易信息详情
    Route::get('legal_seller_deal', 'LegalDealController@sellerLegalDealList')->middleware(['demo_limit']); //法币交易商家端交易列表
    Route::get('legal_user_deal', 'LegalDealController@userLegalDealList')->middleware(['demo_limit']); //法币交易用户端交易列表
    Route::get('seller_info', 'LegalDealController@sellerInfo')->middleware(['demo_limit']); //商家详情信息
    Route::get('seller_trade', 'LegalDealController@tradeList')->middleware(['demo_limit']); //商家交易

    Route::get('legal_deal', 'LegalDealController@legalDealInfo')->middleware(['demo_limit']); //交易详情信息
    Route::post('user_legal_pay', 'LegalDealController@userLegalDealPay')->middleware(['demo_limit', 'check_pay_password:otc']); //法币交易用户确认付款
    Route::post('user_legal_pay_cancel', 'LegalDealController@userLegalDealCancel')->middleware(['demo_limit', 'check_pay_password:otc']); //法币交易用户取消订单
    Route::get('my_seller', 'LegalDealController@mySellerList')->middleware(['demo_limit']); //我的商铺
    Route::get('legal_send_deal_list', 'LegalDealController@legalDealSellerList')->middleware(['demo_limit']); //发布交易列表
    Route::post('legal_deal_sure', 'LegalDealController@doSure')->middleware(['demo_limit', 'check_pay_password:otc']); //商家确认收款
    Route::post('legal_deal_user_sure', 'LegalDealController@userDoSure')->middleware(['demo_limit', 'check_pay_password:otc']); //用户确认收款

    Route::post('back_send', 'LegalDealController@backSend')->middleware(['demo_limit', 'check_pay_password:otc']); //商家撤回发布

    Route::post('error_send', 'LegalDealController@errorSend')->middleware(['demo_limit', 'check_pay_password:otc']); //商家撤回异常发布
    Route::post('down_send', 'LegalDealController@down')->middleware(['demo_limit', 'check_pay_password:otc']); //商家下架发布
    Route::any('legal/arbitrate', 'LegalDealController@submitArbitrate'); // 卖方提交维权
    Route::post('seller/transfer', 'LegalDealController@transfer')->middleware(['demo_limit','lang_check']); // 商家余额 用户余额划转
    Route::get('seller/balance_log', 'LegalDealController@balanceLog')->middleware(['demo_limit']); // 商家余额日志
    
    //秒合约路由
    Route::prefix('microtrade')->middleware('lang_check')->group(function () {
        Route::post('micro_deal', 'MicroOrderController@deal'); //杠杆deal
        Route::get('payable_currencies', 'MicroOrderController@getPayableCurrencies'); //可支付的币种列表
        Route::get('seconds', 'MicroOrderController@getSeconds'); //到期时间
        Route::post('submit', 'MicroOrderController@submit')->middleware('validate_locked','check_user'); //提交下单
        Route::get('listsV2', 'MicroOrderV2Controller@listsV2'); //获取秒合约订单
        
        Route::any('lists', 'MicroOrderController@lists')->middleware('validate_locked'); //下单记录
        Route::get('getOrder', 'MicroOrderController@getOrder'); //获取秒合约订单
        

        Route::any('getAmountList', 'MicroOrderController@getAmountList'); //下单数量快捷方式
        
          Route::post('submitV2', 'MicroOrderV2Controller@submit')->middleware('validate_locked','check_user'); //提交下单


    });
});
