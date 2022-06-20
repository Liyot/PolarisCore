<?php

namespace UnknowL\Games;

use UnknowL\Trait\PropertiesTrait;

class GameProperties{
    use PropertiesTrait;

    public function setBaseProperties(): void{
     $this->properties  = [
         "starting" => false,
         "ending" => false,
         "running" => false,
         "acceptplayerwhenrunning" => false,
         "runningfor" => 0,
         "timeleft" => 0,
     ];
    }
}