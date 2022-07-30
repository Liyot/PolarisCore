<?php

namespace Polaris\games;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\utils\ObjectSet;
use pocketmine\world\Position;
use pocketmine\world\World;
use Polaris\player\PolarisPlayer;
use Polaris\trait\VectorUtilsTrait;

class Zone{

    use VectorUtilsTrait;

    public World $world;

    public function __construct(public string $name, public Position $min, public GameInterface $game, public Position $max, public Position $main){
        $this->world = $min->getWorld();
    }

    public function getMainPosition(): Position{
        return $this->main;
    }

    public function isInZone(PolarisPlayer $player): bool{
        return $player->inZone($player, [$this->min, $this->max]);
    }

    /**
     * @return Entity[]
     */
    public function getEntities(): array{
        $entity = [];
        foreach ($this->world->getEntities() as $entities){
            if ($this->inZone($entities, [$this->min->asVector3(), $this->max->asVector3()])){
                $entity[] = $entities;
            }
        }
        return $entity;
    }

    public function getGame(): GameInterface{
        return $this->game;
    }

}