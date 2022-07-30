<?php

namespace Polaris;

use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use Polaris\command\Groups\GroupsCommand;
use Polaris\entity\FloatingText;
use Polaris\games\GameListener;
use Polaris\games\GameLoader;
use Polaris\groups\Team;
use Polaris\item\EnderPearl;
use Polaris\listener\PacketListener;
use Polaris\listener\PlayerListener;
use Polaris\task\ScoreboardTask;
use Polaris\trait\LoaderTrait;
use Polaris\utils\GameUtils;

class Polaris extends PluginBase{

    use LoaderTrait;
    use SingletonTrait;

    public static array $teams = [];

    public function onEnable(): void{

        self::setInstance($this);


        $this->getServer()->getWorldManager()->loadWorld('PolarisSpawn');

        ItemFactory::getInstance()->register(new EnderPearl(), true);

        GameLoader::getInstance()->init();

        $this->getServer()->getPluginManager()->registerEvents(new PacketListener() , $this);
        $this->getServer()->getPluginManager()->registerEvents(new PlayerListener() , $this);
        $this->getServer()->getPluginManager()->registerEvents(new GameListener(), $this);
        $this->getServer()->getCommandMap()->register('', new GroupsCommand());

        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(), 20);

        //$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function() {

        $this->loadAll();

    }


    public function onDisable(): void{
        foreach(GameLoader::getGameList() as $game){
            $game->onStop();
        }

        foreach (GameUtils::getSpawnWorld()->getEntities() as $entity){
            $entity->close();
        }
    }

    public static function getTeam(string $name): ?Team{
        return self::$teams[$name] ?? null;
    }

}