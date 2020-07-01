<?php


namespace Momo\Weibot;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;
use Pimple\Container;
class Foundation extends Container
{
    protected $providers = [];

    protected $config;

    public function __construct(array $config)
    {
        parent::__construct();
        $this->setConfig($config);
        $this->registerProviders();

        $this->registerBase();
    }

    protected function registerProviders()
    {
        foreach ($this->providers as $provider) {
            $this->register(new $provider());
        }
    }

    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    public function getConfig($key = null)
    {
        return $key ? ($this->config[$key] ?? null) : $this->config;
    }
    public function __get($id)
    {
        return $this->offsetGet($id);
    }

    public function __set($id, $value)
    {
        $this->offsetSet($id, $value);
    }

    private function registerBase()
    {
        if ($cache = $this->getConfig()['cache'] ?? null AND $cache instanceof Cache) {
            $this['cache'] = $this->getConfig()['cache'];
        } else {
            $this['cache'] = function () {
                return new FilesystemCache(sys_get_temp_dir());
            };
        }
    }
}