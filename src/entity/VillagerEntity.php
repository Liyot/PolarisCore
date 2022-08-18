<?php

namespace Polaris\entity;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\player\Player;
use Polaris\trait\callBackTrait;

final class VillagerEntity extends Living
{
    use callBackTrait;

    const INTERACTION = "interaction";

    public static function getNetworkTypeId(): string
    {
        return EntityIds::VILLAGER;
    }

    public function __construct(Location $location, protected string $name, protected EntitySizeInfo $sizeInfo, callable $interactionCallBack)
    {
        $this->addCallback("interaction", $interactionCallBack);
        parent::__construct($location, null);
    }

    public function attack(EntityDamageEvent $source): void
    {
        $source->cancel();
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $this->processCallBack(self::INTERACTION, $player);
        return parent::onInteract($player, $clickPos);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return $this->sizeInfo;
    }

    public function getName(): string
    {
        return $this->name;
    }
}