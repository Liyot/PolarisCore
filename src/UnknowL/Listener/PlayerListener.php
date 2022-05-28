<?php

namespace UnknowL\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\Server;
use UnknowL\Player\PolarisPlayer;

class PlayerListener implements Listener{

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite"){
            $event->cancel();
        }
    }
}
