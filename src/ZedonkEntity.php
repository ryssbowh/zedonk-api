<?php

namespace ZedonkAPI;

class ZedonkEntity
{
    /**
     * fields from zedonk
     * @var array
     */
    public $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    public function __get($name)
    {
        if($this->has($name)){
            return $this->attributes[$name];
        }
        return null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public function has($key)
    {
        return isset($this->attributes[$key]);
    }
}