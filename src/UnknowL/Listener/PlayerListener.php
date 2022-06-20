<?php

namespace UnknowL\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\Server;
use UnknowL\Player\PolarisPlayer;

class PlayerListener implements Listener{

    public function onJoin(PlayerJoinEvent $event)
    {

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
                var_dump($packet->formData, $packet->formId);
                $player->getPlayerProperties()->setProperties("cleanScreen", true);
            }
        }elseif ($packet instanceof ModalFormRequestPacket){
            var_dump($packet->formData, $packet->formId);
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
