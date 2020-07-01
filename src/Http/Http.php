<?php


namespace Momo\Weibot\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
class Http
{
    static $client;

    public static function setClient($cookiePath)
    {
        $cookie = new FileCookieJar($cookiePath, true);
        static::$client = new Client([
            'headers' => [
                'Referer' => 'https://weibo.com',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36'
            ],
            'cookies' => $cookie,
            "verify"=>false,
            "http_errors"=>false
        ]);
    }
    public static function getClient()
    {
        return static::$client;
    }
}