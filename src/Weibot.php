<?php


namespace Momo\Weibot;

use Momo\Weibot\Auth\AuthServiceProvider;
use Momo\Weibot\Http\HttpServiceProvider;
use Momo\Weibot\Post\PostServiceProvider;
class Weibot extends Foundation
{
    protected $providers = [
        HttpServiceProvider::class,
        AuthServiceProvider::class,
        PostServiceProvider::class,
    ];
    protected $config;

    public function login(){
        return $this->auth->login();
    }
}