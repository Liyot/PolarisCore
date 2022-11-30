<?php

namespace Polaris\games;

use pocketmine\entity\Location;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\ItemIds;
use Polaris\entity\ShulkerEntity;
use Polaris\games\Types\RoundedGames;
use Polaris\games\Types\ShootCraft;
use Polaris\games\types\ZoneGame;
use Polaris\player\PolarisPlayer;

class GameListener implements Listener
{

    public function onUse(PlayerItemUseEvent $event): void
    {
        $player = $event->getPlayer();
        if($player instanceof PolarisPlayer)
        {
            $player->getActualGame()?->processCallBack($event::class, $event);
        }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        foreach (GameLoader::getGameList() as $game)
        {
            $game->processCallBack($event::class, $event);
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof PolarisPlayer) {
            $game = $entity->getActualGame();
            $game?->processCallback($event::class, $event);
        }
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof PolarisPlayer) {
            $player->getActualGame()?->processCallback($event::class);
            $properties = $player->getPlayerProperties();
            if (!$properties->getProperties("cleanScreen")) {
                $properties->setProperties("cleanScreen", true);
            }
        }
    }
}