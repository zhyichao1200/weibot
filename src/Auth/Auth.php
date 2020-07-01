<?php


namespace Momo\Weibot\Auth;

use Momo\Weibot\Http\Http;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use Momo\Weibot\Models\PreLoadModel;
use Momo\Weibot\Models\LoginDataModel;
use Momo\Weibot\Models\LoginURIModel;
use Momo\Weibot\Models\AuthModel;
use Momo\Weibot\CaptchaInstance\Captcha;

/**
 * Class Auth 登录
 * @package Momo\Weibot\Auth
 *
 */
class Auth
{
    /**
     * @var string 登录用户名
     */
    public $username;

    /**
     * @var string 登录密码
     */
    public $password;

    /**
     * @var Captcha 破解验证码引擎
     */
    protected $captcha;

    /**
     * @var string 验证码路径
     */
    protected $captchaPath;

    /**
     * Auth constructor.
     * @param $username string 登录用户名
     * @param $password string 登录密码
     */
    public function __construct(string $username,string $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * 设置验证码破解
     * @param Captcha $captcha 验证码破解引擎
     * @return $this
     */
    public function setCaptcha(Captcha $captcha) :Auth{
        $this->captcha = $captcha;
        return $this;
    }

    /**
     * 设置验证码路径
     * @param string $path 验证码储存路径
     * @return $this
     */
    public function setCaptchaPath(string $path) :Auth{
        $this->captchaPath = $path;
        return $this;
    }

    /**
     * 获取验证码路径
     * @return string
     */
    protected function getCaptchaPath():string{
        return !empty($this->captchaPath) ? $this->captchaPath : __DIR__;
    }

    /**
     * 获取验证码名称
     * @return string
     */
    protected function getCaptchaName():string{
        return $this->username.".png";
    }

    /**
     * 微博预登录
     * @return PreLoadModel
     */
    public function preload() :PreLoadModel{
        $time = time().rand(1000,9999);
        $url = "https://login.sina.com.cn/sso/prelogin.php?entry=weibo&callback=sinaSSOController.preloginCallBack&su=&rsakt=mod&client=ssologin.js(v1.4.19)&_=".$time;
        $result = Http::getClient()->get($url)->getBody()->getContents();
        $json = str_replace(['sinaSSOController.preloginCallBack', '(', ')'], '', $result);
        $json = json_decode($json, true);
        $model = new PreLoadModel($json ? true : false,$json ? "获取成功" : "获取失败");
        if(!is_array($json)) return $model;
        $model->nonce       = $json["nonce"];
        $model->publicKey   = $json["pubkey"];
        $model->serverTime  = $json["servertime"];
        $model->rsakv       = $json["rsakv"];
        $model->pcid        = $json["pcid"];
        return $model;
    }

    /**
     * rsa加密
     * @param $password
     * @param $serverTime
     * @param $nonce
     * @param $key
     * @return string
     */
    public function rsa($password, $serverTime, $nonce,$key){
        $rsa = new RSA();
        $rsa->loadKey([
            'n' => new BigInteger($key, 16),
            'e' => new BigInteger('10001', 16),
        ]);
        $message = $serverTime."\t".$nonce."\n".$password;
        $rsa->setEncryptionMode(2);
        return bin2hex($rsa->encrypt($message));
    }

    /**
     * 验证码资源URL
     * @param $pcid
     * @return string
     */
    protected function getLoginCaptcha($pcid):string{
        return "https://login.sina.com.cn/cgi/pin.php?r=87227255&s=0&p=".$pcid;
    }

    /**
     * 提交登录参数
     * @param string $pcid
     * @param string $su
     * @param string $serverTime
     * @param string $nonce
     * @param string $sp
     * @param string $code
     * @return LoginDataModel
     */
    protected function loginDataPost(string $pcid,string $su,string $serverTime,string $nonce,string $sp,string $code) :LoginDataModel{
        $data = [
            "entry"=>"weibo",
            "gateway"=>"1",
            "from"=>"",
            "savestate"=>"7",
            "qrcode_flag"=>"false",
            "useticket"=>"1",
            "pagerefer"=>"",
            "pcid"=>$pcid,
            "vsnf"=>"1",
            "su"=>$su,
            "service"=>"miniblog",
            "servertime"=>$serverTime,
            "nonce"=>$nonce,
            "pwencode"=>"rsa2",
            "rsakv"=>"1330428213",
            "sp"=>$sp,
            "sr"=>"1920*1080",
            "encoding"=>"UTF-8",
            "prelt"=>"326",
            "url"=>"https://weibo.com/ajaxlogin.php?framelogin=1&callback=parent.sinaSSOController.feedBackUrlCallBack",
            "returntype"=>"META",
            "door"=>$code
        ];
        $result = Http::getClient()->post("https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.19)",[
            'form_params'=>$data,
            "headers"=>[
                "Host"=>"login.sina.com.cn",
                "Origin"=>"https://weibo.com",
                "Referer"=> "https://weibo.com/",

            ]
        ]);

        preg_match('/location.replace\(\"(.*?)\"/',$result->getBody()->getContents(),$outer);
        $model = new LoginDataModel(!empty($outer[1]) ? true : false,!empty($outer[1]) ? "获取成功" : "获取失败");
        if(empty($outer[1])) return $model;
        $model->ajaxlogin = $outer[1];
        return $model;
    }

    /**
     * 获取sso登录URL
     * @param string $ajaxlogin
     * @return LoginURIModel
     */
    protected function loginURIs(string $ajaxlogin) :LoginURIModel{
        $result = Http::getClient()->get($ajaxlogin);
        $body = $result->getBody()->getContents();
        preg_match('/"arrURL":(.*?)}/is',$body,$arrURL);
        preg_match('/location.replace\(\'(.+?)\'/is',$body,$location);
        $model = new LoginURIModel(empty($arrURL[1]) || empty($location[1]) ? false : true,
            empty($arrURL[1]) || empty($location[1])  ? "获取失败" : "获取成功");
        if (!$model->ok()) return $model;
        $model->arrURL = json_decode($arrURL[1],true);
        $model->arrURL[] = $location[1];
        return $model;
    }

    /**
     * sso登录
     * @param array $urls
     */
    protected function ssoLogin(array $urls){
        foreach ($urls as $value){
            try{Http::getClient()->get($value);}catch (\Exception $e){}
        }
    }

    /**
     * 登录
     * @return LoginDataModel|LoginURIModel|PreLoadModel|AuthModel
     */
    public function login(){
        $preloadData = $this->preload();
        if (!$preloadData->ok()) return $preloadData;
        $sp = $this->rsa($this->password, $preloadData->serverTime, $preloadData->nonce,$preloadData->publicKey);
        $captchaPath = $this->getCaptchaPath()."/".$this->getCaptchaName();
        file_put_contents($captchaPath,file_get_contents($this->getLoginCaptcha($preloadData->pcid)));
        $code = $this->captcha->run($captchaPath);
        $loginData = $this->loginDataPost($preloadData->pcid,base64_encode($this->username),$preloadData->serverTime,$preloadData->nonce,$sp,$code);
        if (!$loginData->ok()) return $loginData;
        $ssoURI = $this->loginURIs($loginData->ajaxlogin);
        if (!$ssoURI->ok()) return $ssoURI;
        $this->ssoLogin($ssoURI->arrURL);
        $model = new AuthModel(true,"登录成功");
        $model->username = $this->username;
        return $model;
    }
}