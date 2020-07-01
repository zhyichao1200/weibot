<?php


namespace Momo\Weibot\Captcha;

use Momo\Weibot\CaptchaInstance\Captcha;
class JianJiaoCheck implements Captcha
{
    private $appcode;
    private $appkey;
    private $appsecret;
    public function __construct($appcode,$appkey,$appsecret)
    {
        $this->appcode = $appcode;
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;
    }

    public function run($image){
        $host = "http://apigateway.jianjiaoshuju.com";
        $path = "/api/v_1/yzmCustomized.html";
        $method = "POST";
        $appcode = $this->appcode;
        $appKey = $this->appkey;
        $appSecret = $this->appsecret;
        $headers = array();
        array_push($headers, "appcode:" . $appcode);
        array_push($headers, "appKey:" . $appKey);
        array_push($headers, "appSecret:" . $appSecret);
        //根据API的要求，定义相对应的Content-Type
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
        $querys = "";
        $bodys = "v_pic=".base64_encode(file_get_contents($image))."&pri_id=ne";
        $url = $host . $path;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $bodys);
        $res = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($res,true);
        return empty($res["v_code"]) ? null : $res["v_code"];
    }
}