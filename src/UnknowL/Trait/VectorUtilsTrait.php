<?php

namespace UnknowL\Trait;

use JetBrains\PhpStorm\Pure;
use pocketmine\entity\Entity;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use UnknowL\Player\PolarisPlayer;

trait VectorUtilsTrait{

    public PolarisPlayer|null $player;

    #[Pure] public function getMatchedVector(int $y, int $multiplier): Vector3{
        $vector = match ($this->player->getHorizontalFacing()){
            Facing::NORTH => [1 * $multiplier, $y, 0],
            Facing::SOUTH => [-1 * $multiplier, $y, 0],
            Facing::EAST  => [0, $y, -1 * $multiplier],
            Facing::WEST  => [0, $y, 1 * $multiplier],
        };
        return new Vector3($vector[0], $vector[1], $vector[2]);
    }

    /**
     * @param Entity $entity
     * @param Vector3[] $pos
     * @return bool
     */
    public function inZone(Entity $entity, array $pos): bool{
        $playerPos = $entity->getPosition();
        return $pos[0]->getX() <= $playerPos->getX() && $pos[1]->getX() >= $playerPos->getX() && $pos[0]->getY() <= $playerPos->getY()
            && $pos[1]->getY() >= $playerPos->getY() && $pos[0]->getZ() <= $playerPos->getZ() && $pos[1]->getZ() >= $playerPos->getZ();
    }

}