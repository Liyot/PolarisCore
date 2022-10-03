<?php

namespace Polaris;

use pocketmine\item\ItemFactory;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Polaris\cosmetics\CosmeticsManager;
use Polaris\games\GameLoader;
use Polaris\groups\Group;
use Polaris\item\EnderPearl;
use Polaris\player\PolarisPlayer;
use Polaris\task\ScoreboardTask;
use Polaris\trait\ConversionTrait;
use Polaris\trait\LoaderTrait;
use Polaris\utils\GameUtils;

class Polaris extends PluginBase
{

    use LoaderTrait;
    use SingletonTrait;
    use ConversionTrait;

    public static array $groups = [];
    private int $time = 0;

    public function onEnable(): void
    {
        self::setInstance($this);
        $this->getServer()->getWorldManager()->loadWorld('PolarisSpawn');
        ItemFactory::getInstance()->register(new EnderPearl(), true);
        GameLoader::getInstance()->init();
        $this->getScheduler()->scheduleRepeatingTask(new ScoreboardTask(), 20);
        $this->loadAll();

        CosmeticsManager::getInstance()->getCosmetic("crown");
    }

    public function onDisable(): void
    {
        foreach (GameLoader::getGameList() as $game) {
            $game->onStop();
        }

        foreach (GameUtils::getSpawnWorld()->getEntities() as $entity) {
            if(!$entity instanceof PolarisPlayer) $entity->close();

            $this->getLogger()->notice("Closing entity of type {$entity->getId()}");
        }
    }

    public static function getGroup(string $name): ?Group
    {
        return self::$groups[$name] ?? null;
    }

}