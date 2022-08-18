<?php

namespace Polaris\trait;

trait callBackTrait
{
    /**
     * @var callable[]
     */
    protected array $callBack = [];

    public function addCallback(string $name, callable $callback): void{
        if(!isset($this->callBack[$name])){
            $this->callBack[$name] = $callback;
        }
    }

    public function processCallBack(string $name, ...$args): void{
        if(isset($this->callBack[$name])){
            $this->callBack[$name](...$args);
        }
    }

    public function removeCallBack(string $name): void
    {
        if(isset($this->callBack[$name]))
        {
            unset($this->callBack[$name]);
        }
    }
}