<?php

namespace Polaris\player;

use pocketmine\nbt\tag\CompoundTag;
use Polaris\trait\PropertiesTrait;
use Polaris\utils\PlayerUtils;

class PlayerProperties{
    use PropertiesTrait;

    public function __construct(public PolarisPlayer $player)
    {
        if(!($nbt = $this->player->saveNBT())->getCompoundTag('properties') || empty($nbt->getCompoundTag("properties")->getValue())){
            $this->setBaseProperties([
                "games" => [
                    "jumpdata" => [
                        "try" => 0,
                        "besttime" => 0.0,
                    ]
                ]
            ]);
        }else{
            $this->setBaseProperties(PlayerUtils::TagtoArray($nbt->getCompoundTag("properties")));
        }
    }

    public function save(CompoundTag $tag)
    {
        $tag->setTag("properties", PlayerUtils::arraytoTag($this->getPropertiesList()));
    }

}