<?php

namespace Polaris\listener;

use pocketmine\block\VanillaBlocks;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Server;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\games\GameLoader;
use Polaris\games\types\Jump;
use Polaris\item\Specialitem;
use Polaris\player\PolarisPlayer;

class PlayerListener implements Listener{

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        if($player instanceof PolarisPlayer){
            $player->sendMessage("§l§b[§aPolaris§b] §aBienvenue sur Polaris !");
            $player->teleportToSpawn();
        }
    }

    public function onDamage(EntityDamageEvent $event)
    {
        $player = $event->getEntity();
        if($player instanceof PolarisPlayer){
            if(!$player->canDie()){
                $event->cancel();
            }
        }
    }

    public function onExhaust(PlayerExhaustEvent $event){
        $event->cancel();
    }

    public function onMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();
        if($player instanceof PolarisPlayer){
            if($player->getPosition()->y <=  0){
                $player->teleportToSpawn();
            }
            $block = $player->getWorld()->getBlock($player->getPosition(), false, false);
            $game = $player->getActualGame();
            if($block instanceof CustomPlate){
                if(!$game instanceof Jump){
                    foreach (GameLoader::getGameList() as $game){
                        if($game instanceof Jump && $game->pos->distance($player->getPosition()) <= 1.5){
                            $game->join($player);
                        }
                    }
                    return;
                }
                $game->nextCheckpoint($player);
            }elseif ($block instanceof EndPlate){
                var_dump('a');
                $game->finish($player);
            }
        }
    }

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        if (Server::getInstance()->isOp($player->getName())){
            if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite") {
                $event->cancel();
            }
        }
    }

    public function onCreate(PlayerCreationEvent $event){
        $event->setPlayerClass(PolarisPlayer::class);
    }

    public function onReceive(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();

        if($packet instanceof ModalFormResponsePacket){
            $player = $event->getOrigin()->getPlayer();
            if($player instanceof PolarisPlayer){
                $player->getPlayerProperties()->setProperties("cleanScreen", true);
            }
        }elseif ($packet instanceof ModalFormRequestPacket){
        }
    }

    public function onPlace(BlockPlaceEvent $event){
        $player = $event->getPlayer();
        if (Server::getInstance()->isOp($player->getName())){
            if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite") {
                $event->cancel();
            }
        }
    }

    public function onUse(PlayerItemUseEvent $event){
        $item = $event->getItem();
        $player = $event->getPlayer();
        if($item instanceof Specialitem){
            $item->clickListener->__invoke($player);
        }
    }

    public function onHit(EntityDamageByEntityEvent $event){
        $player = $event->getEntity();
        if ($player instanceof PolarisPlayer){
            if (Server::getInstance()->isOp($player->getName())){
                if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite") {
                    $event->cancel();
                }
            }
        }
    }
}