<?php

namespace UnknowL;

use pocketmine\data\bedrock\EntityLegacyIds as LegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\world\World;
use UnknowL\Command\Groups\GroupsCommand;
use UnknowL\Entity\PearlEntity;
use UnknowL\Groups\Team;
use UnknowL\Item\EnderPearl;
use UnknowL\Listener\PlayerListener;

class Polaris extends PluginBase{

    public static array $teams = [];

    public function onEnable(): void{
        EntityFactory::getInstance()->register(PearlEntity::class, function (World $world, CompoundTag $nbt): PearlEntity{
            return new PearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], LegacyIds::ENDER_PEARL);

        ItemFactory::getInstance()->register(new EnderPearl(), true);

        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener() , $this);
        $this->getServer()->getCommandMap()->register('', new GroupsCommand());
    }

    public static function getTeam(string $name): ?Team{
        return self::$teams[$name] ?? null;
    }

}