<?php


namespace Momo\Weibot;

use Momo\Weibot\Auth\AuthServiceProvider;
use Momo\Weibot\Http\HttpServiceProvider;
class Weibot extends Foundation
{
    protected $providers = [
        HttpServiceProvider::class,
        AuthServiceProvider::class,
    ];
    protected $config;

    public function login(){
        $this->auth->login();
    }
}