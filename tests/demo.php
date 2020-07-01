<?php
include_once __DIR__.'/../vendor/autoload.php';
use Momo\Weibot\Auth;
use Momo\Weibot\Weibot;
use Momo\Weibot\Captcha\JianJiaoCheck;
$username = "";
$password = "";
$weibot = new Weibot([
    "username"=>$username,
    "password"=>$password,
    'cookie_path' => __DIR__.'/cookie',
]);
$appcode = "";
$appkey = "";
$appsecret = "";
$model = new JianJiaoCheck($appcode,$appkey,$appsecret);
$weibot->auth->setCaptcha($model)->setCaptchaPath(__DIR__);
$weibot->login();