<?php

namespace Polaris\task;

use Exception;
use pocketmine\scheduler\CancelTaskException;
use pocketmine\scheduler\Task;

class CooldownTask extends Task
{
    private $executable;
    private array $args;

    public function __construct(protected int $time, callable $executable, ...$args)
    {
        $this->executable = $executable;
        $this->args = $args;
    }

    public function executable(): callable
    {
        return $this->executable;
    }

    public function onRun(): void
    {
        if($this->time > 0)
        {
            try{
                $this->executable()($this->args);
                throw new CancelTaskException();
            }
            catch(Exception $exception)
            {
                throw new CancelTaskException();
            }
        }
        $this->time--;
    }
}