<?php

namespace Polaris\item;

use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\utils\Utils;
use Polaris\player\PolarisPlayer;


class Specialitem extends Item{

    public function __construct(ItemIdentifier $id, string $name, public \Closure $clickListener){
        Utils::validateCallableSignature(function (PolarisPlayer $player){}, $clickListener);
        parent::__construct($id, $name);
    }

}