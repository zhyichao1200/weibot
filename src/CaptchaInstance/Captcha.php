<?php


namespace Momo\Weibot\CaptchaInstance;


interface Captcha
{
    public function run(string $path);
}