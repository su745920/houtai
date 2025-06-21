<?php
	// 生成随机字符
function GetRandStr($length){
	 //字符组合
	 $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	 $len = strlen($str)-1;
	 $randstr = '';
	 for($i=0;$i<$length;$i++) {
	  	$num=mt_rand(0,$len);
	  	$randstr .=" ".$str[$num];
	 }

	 return $randstr;
}



$code =  $_REQUEST["code"];
session_start();
$_SESSION['code'] = str_replace(" ", "", $code);

$code2="";
for ($i=0;$i<strlen($code);$i++){
    $c=substr($code,$i,1);
    $c=$c." ";
    $code2=$code2.$c;
}



// var_dump($_SESSION);die();
//var_dump(str_replace(" ", "", GetRandStr(4)));die();

//创建新的true-color图像。此函数返回给定尺寸的空白图像
$im = imageCreateTrueColor(100, 50);
// magecolorallocate — 为一幅图像分配颜色
$white = imageColorAllocate($im, 255, 255, 255);
$black = imageColorAllocate($im, 0, 0, 64);
//区域填充
imageFill($im, 0, 0, $white);
// 两个给定点之间绘制一条线段,用来干扰验证码识别
//imageLine($im, 0, rand(20,50), 200, rand(20,50), $black);
//水平绘制字符串
imageString($im, 12, 6, 20, $code2, $black);
header('Content-type: image/png');
imagePng($im);
imageDestroy($im);
?>
