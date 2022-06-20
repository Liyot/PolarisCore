<?php

namespace UnknowL;

use pocketmine\data\bedrock\EntityLegacyIds as LegacyIds;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\world\World;
use UnknowL\Command\Groups\GroupsCommand;
use UnknowL\Entity\PearlEntity;
use UnknowL\Entity\ShulkerEntity;
use UnknowL\forms\CustomForm;
use UnknowL\forms\menu\Button;
use UnknowL\Games\GameListener;
use UnknowL\Games\GameLoader;
use UnknowL\Groups\Team;
use UnknowL\Item\EnderPearl;
use UnknowL\Listener\PlayerListener;
use UnknowL\Task\ScoreboardTask;

class Polaris extends PluginBase{

    public static array $teams = [];

    public function onEnable(): void{
        EntityFactory::getInstance()->register(PearlEntity::class, function (World $world, CompoundTag $nbt): PearlEntity{
            return new PearlEntity(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
        }, ['ThrownEnderpearl', 'minecraft:ender_pearl'], LegacyIds::ENDER_PEARL);

        EntityFactory::getInstance()->register(ShulkerEntity::class, function (World $world, CompoundTag $nbt): ShulkerEntity{
            return new ShulkerEntity(EntityDataHelper::parseLocation($nbt, $world), [false], null);
        }, ['ShulkerEntity'], LegacyIds::SHULKER_BULLET);

        ItemFactory::getInstance()->register(new EnderPearl(), true);

        GameLoader::init();

        $form = new CustomForm('eee', "eee", function (){},  );
        $form->appendElements(new Button("eee", ));

        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener() , $this);
        $this->getServer()->getPluginManager()->registerEvents(new GameListener(), $this);
        $this->getServer()->getCommandMap()->register('', new GroupsCommand());

        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(), 20);
    }

    public function onDisable(): void{
        foreach(GameLoader::getGameList() as $game){
            $game->onStop();
        }
    }

    public static function getTeam(string $name): ?Team{
        return self::$teams[$name] ?? null;
    }

}