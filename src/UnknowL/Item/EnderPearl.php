<?php

namespace UnknowL\Item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Throwable;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\ProjectileItem;
use pocketmine\player\Player;
use UnknowL\Entity\PearlEntity;
use UnknowL\Player\PolarisPlayer;

class EnderPearl extends ProjectileItem{

    public function __construct(){
        parent::__construct(new ItemIdentifier(ItemIds::ENDER_PEARL, 0),  "Ender Pearl");
    }

    public function getThrowForce(): float
    {
        return 1.5;
    }

    /**
     * @param Location $location
     * @param PolarisPlayer $thrower
     * @return Throwable
     */
    protected function createEntity(Location $location, Player $thrower): Throwable
    {
        $entity = new PearlEntity($location, $thrower);
        if ($thrower->isRiding()){
            $entity->flagForDespawn();
        }
        return $entity;
    }

}