<?php

namespace UnknowL\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\Server;
use UnknowL\Player\PolarisPlayer;

class PlayerListener implements Listener{

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
        if($packet instanceof BlockEventPacket){
            var_dump($packet);
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
