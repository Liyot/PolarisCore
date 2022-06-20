<?php

namespace UnknowL\Player;

use UnknowL\Trait\PropertiesTrait;

class PlayerProperties{
    use PropertiesTrait;

    public function __construct(){
        $this->setBaseProperties(["cleanscreen" => true, "hastouched" => false]);
    }
}