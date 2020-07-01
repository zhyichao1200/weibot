<?php


namespace Momo\Weibot\Http;

use Momo\Weibot\Auth\Auth;
use Momo\Weibot\Weibot;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
class HttpServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        Http::setClient($pimple->getConfig('cookie_path'));
    }
}