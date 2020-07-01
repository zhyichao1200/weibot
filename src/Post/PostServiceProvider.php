<?php


namespace Momo\Weibot\Post;

use Momo\Weibot\Weibot;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PostServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['post'] = function (Weibot $pimple) {
            return new Post();
        };
    }
}