<?php


namespace Momo\Weibot\Auth;

use Momo\Weibot\Weibot;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
class AuthServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['auth'] = function (Weibot $pimple) {
            $config = $pimple->getConfig();
            return new Auth($config['username'], $config['password']);
        };
    }
}