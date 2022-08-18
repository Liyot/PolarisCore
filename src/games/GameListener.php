<?php

namespace Polaris\games;

use pocketmine\entity\Location;
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

    private array $cooldown = [];

    public function onUse(PlayerItemUseEvent $event)
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($player instanceof PolarisPlayer) {
            if ($player->getActualGame() instanceof ShootCraft) {
                if ($item->getId() === ItemIds::STICK) {
                    $cooldown = $this->cooldown[$player->getName()] ?? 0;
                    if ($cooldown < time()) {
                        $location = Location::fromObject($player->getLocation()->add(0, 1, 0), $player->getWorld(), $player->getLocation()->yaw);
                        $entity = new ShulkerEntity($location, [false, "game" => $player->getActualGame()]);
                        $entity->spawnToAll();
                        $this->cooldown[$player->getName()] = time() + 5;
                        $entity->setMotion($player->getDirectionVector());
                    } else {
                        $event->cancel();
                        $player->sendMessage("§cVous ne pouvez pas utiliser cette arme pour le moment");
                    }
                }
            }
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof PolarisPlayer) {
            $game = $entity->getActualGame();
            $game?->processCallback($event->getEventName(), $event);
        }
    }

    public function onMove(PlayerMoveEvent $event)
    {
        $player = $event->getPlayer();
        if ($player instanceof PolarisPlayer) {
            foreach (GameLoader::getGameList() as $game) {
                /* if($game instanceof ZoneGame && !$game instanceof RoundedGames){
                     if($player->inZone($player, [$game->getZone()->min, $game->getZone()->max])){
                         if(!$game->properties->getProperties('Running') || ($game->properties->getProperties("Running") && $game->properties->getProperties('AcceptPlayerWhenRunning'))){
                             if(!$player->isInGame()){
                                 $player->joinGame($game);
                                 return;
                             }
                         }
                     }elseif (($actualgame = $player->getActualGame()) instanceof $game){
                         $actualgame->leave($player, $game);
                     }*/
                $game->processCallback($event->getEventName());

            }
            $properties = $player->getPlayerProperties();
            if (!$properties->getProperties("cleanScreen")) {
                $properties->setProperties("cleanScreen", true);
            }
        }
    }
}