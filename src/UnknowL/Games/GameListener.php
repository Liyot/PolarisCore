<?php

namespace UnknowL\Games;

use pocketmine\entity\Location;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\ItemIds;
use UnknowL\Entity\ShulkerEntity;
use UnknowL\Games\Types\ShootCraft;
use UnknowL\Player\PolarisPlayer;

class GameListener implements Listener{

    private array $cooldown = [];

    public function onUse(PlayerItemUseEvent $event){
        $player = $event->getPlayer();
        $item = $event->getItem();
        if ($player instanceof PolarisPlayer){
            if($player->getActualGame() instanceof ShootCraft){
                if($item->getId() === ItemIds::STICK){
                    $cooldown = $this->cooldown[$player->getName()] ?? 0;
                    if($cooldown < time()){
                        $location =  Location::fromObject($player->getLocation()->add(0, 1, 0), $player->getWorld(), $player->getLocation()->yaw);
                        $entity = new ShulkerEntity($location, [false, "game" => $player->getActualGame()]);
                        $entity->spawnToAll();
                        $this->cooldown[$player->getName()] = time() + 5;
                        $entity->setMotion($player->getDirectionVector());
                    }else{
                        $event->cancel();
                        $player->sendMessage("§cVous ne pouvez pas utiliser cette arme pour le moment");
                    }
                }
            }
        }
    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        if ($player instanceof PolarisPlayer){
            foreach (GameLoader::getGameList() as $game){
                if($player->inZone($player, [$game->getZone()->min, $game->getZone()->max])){
                    if(!$game->properties->getProperties('Running') || ($game->properties->getProperties("Running") && $game->properties->getProperties('AcceptPlayerWhenRunning'))){
                        if(!$player->isInGame()){
                            $player->joinGame($game);
                        }
                    }
                }elseif (($actualgame = $player->getActualGame()) instanceof $game){
                    $actualgame->leave($player);
                }
            }
            $properties = $player->getPlayerProperties();
            if(!$properties->getProperties("cleanScreen")){
                $properties->setProperties("cleanScreen", true);
            }
        }
    }
}