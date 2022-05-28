<?php

namespace UnknowL\Listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;

class PlayerListener implements Listener{

    public function onBreak(BlockBreakEvent $event){
        $player = $event->getPlayer();
        if ($player->getPosition()->getWorld()->getDisplayName() === "Ma bite"){
            $event->cancel();
        }
    }
}
