<?php

namespace UnknowL\Games;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use UnknowL\Player\PolarisPlayer;
use UnknowL\Trait\VectorUtilsTrait;

class Zone{

    use VectorUtilsTrait;

    public string $name;
    public GameInterface $gameId;
    public Vector3 $min;
    public Vector3 $max;
    public World $world;

    public function __construct(string $name, GameInterface $gameId, Position $min, Position $max){
        $this->name = $name;
        $this->gameId = $gameId;
        $this->min = $min;
        $this->max = $max;
        $this->world = $min->getWorld();
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
        return $this->gameId;
    }


}