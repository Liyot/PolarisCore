<?php

namespace Polaris\games;

use Polaris\trait\PropertiesTrait;

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