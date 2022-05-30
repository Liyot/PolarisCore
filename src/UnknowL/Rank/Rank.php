<?php

namespace UnknowL\Rank;

use pocketmine\item\Item;

interface Rank{

     public function getName(): string;
     
    public function getColor(): string;
     
    public function getPrefix(): string;
          
    public function isPremium(): bool;

}