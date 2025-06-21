<?php
// 允许所有域进行跨域请求
header("Access-Control-Allow-Origin: *");
// 允许的方法
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// 允许的头信息
header("Access-Control-Allow-Headers: X-Requested-With");


require_once('TigerpayClient.php');

use TigerpaySDK\TigerpayClient;

use TigerpaySDK\TigerpayTradeWapObj;
use TigerpaySDK\TigerpayTradeWapReq;

use TigerpaySDK\TigerpayTradeWappayObj;
use TigerpaySDK\TigerpayTradeWappayReq;

/////////////////////////////////////////////////////////////////////////////////////////////以下数据为服务商提供////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//TODO  以下数据为示例数据，实际数据由管理员提供
$serverUrl = "https://api.in.dvbfservice.com/trade";
$appId="1712884112930";
$APPPrivateKEY='MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBALwM33cgp7BdRcnHyLXSnFyT1RVvhUSENs34sLOnQlBmXGAsZu7AskNDrr9SUZPmeR3aPHClfyBjnixrYst0XcY1JkDM0It0J0n4FU9QmzBVdi+KEX5fyICBtCafrU/WHFCP+A5mUTb8LLWmVNKEVApr40zy/7or8FWg9E8W8JvFAgMBAAECgYEAne7tyv4Y960OTK5GqjXs7m+WGT+lHGsyIACKXwfNUPr3ACqqdEBQNt/kJni7XMzG0cSU2EKWJxyjHkk+GwNrMPZo8JB1CEhE+REpfNFiXa2CfULctTD1ReJ3ScIY2QSLw1WcLtz6Jv9k76VKTw9z7caMTBRUomS4Fnj80KXb80ECQQD1G9OKU/FZeFd6ZnOuOOTTOXmD06DdcgrUWfos+vv8ghEv8qNP/EwtTUwZRGO551n4Lo4EtqHfJmJ/1Q0doeMRAkEAxGf+Hs9oBR/cIfFNp/WyVwq0JfsTVm3s0zYSJ1ineq4AUZTxmH33vq6/dcWU0t3Hd8tDb+YBRoWJP0rUqTyFdQJBAOMs5UYCQ2GPflS9/F3v8XYsgQnD7gcRGiRzOMLT5RXGX7O3CdKqntgmGnAYyO2XFfZG/+4yb5lp9EVS7BMY5mECQFCanR/n3ri7qowDi+syWk5+hhBQSk9eLaNAvZKIP7OJVXgluEDs8Y/AB7M9syYW/pWbRcHWkw9uHjVrcHtD60ECQFQa/QIOvQ+np7LXcIBi8kfc93YM9rgXRyy31gyJi3NjxLi9z9CE2Ulr9XGowz+9Fi1g85+CGNn3eYSbqR6tIls=';
$ServerPublicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCzykwv0WC7wBIFFX5ewkiuViWgvHeLTAjERFgFBF+wqXEbGC42dxOIgD4bQD5kYmhiPc1mgd1ghSLzPaHQROvuxhRc2kjI3fEvtg1jwc37sa+nDPxgj8HlJONvERtIniqWdrUjv3tc9b38SIYilt4d4LGMJpfdcAd76nyyJKX1twIDAQAB';
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



///////////////////////////////////////支付方式选择及信息输入接口////////////////////////////////////////////////////
//////////////////////////////////////////WAP支付接口/////////////////////////////
$client = new TigerpayClient($serverUrl, $appId, $APPPrivateKEY, $ServerPublicKey);


$amount=$_REQUEST["amount"];
$userName=$_REQUEST["userName"];
$userId=$_REQUEST["userId"];
$tradeNo=$_REQUEST["tradeNo"];


$wapObj = new TigerpayTradeWapObj();
$wapObj->payType = 1;
$wapObj->price = $amount;
$wapObj->userName = $userName;
$wapObj->userId = $userId;
$wapObj->tradeNo = $tradeNo;
$wapObj->symbol = 3;
//其它参数设置
//$wapObj->......
$wapObj->returnUrl="https://demo.ucoin.site";
$request = new TigerpayTradeWapReq($wapObj);

$url = $client->sdkExecute($request);

//
$result = file_get_contents($url);
$result = json_decode($result, true);

$body=$result["body"];
$url=$body["url"];



$jo["pay_url"]=$url;
echo json_encode($jo);






//echo $url;

//echo "\n";

///////////////////////////////////直接支付接口//////////////////////
// $wappayObj = new TigerpayTradeWappayObj();
// $wappayObj->payType = 1;        // 支付方式  1：银行卡  2：支付宝
// $wappayObj->price = 1;
// $wappayObj->userName = "";
// $wappayObj->userId = "";
// $wappayObj->tradeNo = "商户订单号";
// //其它参数设置
// //$wapObj->......
// $wappayObj->returnUrl="支付成功跳转的URL";
// $request = new TigerpayTradeWappayReq($wappayObj);

// $url = $client->sdkExecute($request);
// echo $url;

// //////////////////////////////回调//////////////////////////////////////////////////////
// // 回调和响应数据的body字符串验证和解析
// $responseBody = '{"code":0,"message":"success","body":{"charset":"utf-8","sign":"J%2Bo1Dg5e56GZd1IMdibJv4veIkU39lmfZCBnIetR8bWhI%2Bzh0RX%2BvJWGcvkUd8gX04nwGu15gFOtTYIA%2BtnnwqbDx%2FCUy7M87F1LNBapQzDx1GUrJSN0jDKQUWDdQIngTo4ZbCFy3hjDfnDlaWCourX%2BdGy1J0134gOBcMXUvAw%3D","method":"tigerpay.trade.wap","version":"1.0.0","data":"GJwA%2Fizj0dI8D5x1RdC6yI4hi4F6CUZTOJSI98b%2F1cu2UWRqKbr2WfFQ7Dv2Vz0yR7FqZZGpganMvOAT2reesYkN58bsQCPzqp30t8mNeDquq02vstNCAdoPWxBF2gRQ2ymiCl0hz06m0nG0cXovOrPFbWon6eHOCLisCWrB9XLTQLWyNnSgBsPVeJTftEf4E%2FESHIYIzzxrX4Yb7fQoBFHjhYW7pP8yM%2FsoGBzhNV6Tpo1q2ipPmpy9RNf6iwr%2F2SxqQ22AZcfwUnJTP%2B59iRcOlxP7Kh00bfVxZld%2Fno4UFZPkEkc1zvBmTJjXOUjMgvjfHeXfNTWf5tkGp6rIJkM3ywjG3dxz0VZ6luw7mkNC4zwJFsJgpMJhfJ8Jx0kv7KXl0RQo3kaxvyWdb1WYOoeZplZ27M6hA0%2B2tccX9G9EPwOjtimuqfg%2F0qrognsN1R01Wb6tXT9Lh37tdR0DP4UEs2KeauSTsOh0tPfhQRhxY4YNLyPWvOdGAL3hdMAX"}}';
// $body = json_decode($responseBody)->body;
// echo "\n";
// echo json_encode($client->checkResponse($body));