<?php


namespace Momo\Weibot\Models;


class Model
{
    protected $ok,$message;

    public function __construct($ok,$message)
    {
        $this->ok = $ok;
        $this->message = $message;
    }

    public function __call($name, $arguments)
    {
        return $this->$name;
    }
}