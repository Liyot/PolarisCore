<?php

namespace Polaris\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use Polaris\player\PolarisPlayer;

class PacketListener implements Listener{

    public function onDataPacketReceive(DataPacketReceiveEvent $event){
        $packet = $event->getPacket();
        $player = $event->getOrigin()->getPlayer();
        if($player instanceof PolarisPlayer){
            if($packet instanceof PlayerAuthInputPacket){
                if(($actions = $packet->getBlockActions()) !== null){
                    if ($player->getActualGame() === null){
                        foreach ($actions as $action){
                            /*if($action->getActionType() === BlockAct){
                            }*/
                        }
                       // $event->cancel();
                        /*$packet = PlayerAuthInputPacket::create($packet->getPosition(), $packet->getPitch(), $packet->getYaw(), $packet->getBlockActions(),
                            $packet->getMoveVecX(), $packet->getMoveVecZ(), $packet->getInputFlags(), $packet->getInputMode(), $packet->getPlayMode(), $packet->getInteractionMode(),
                        $packet->getVrGazeDirection(), $packet->getTick(), $packet->getDelta(),$packet->getItemInteractionData() ,$packet->getItemStackRequest(), []);*/
                        //$packet
                    }
                }
            }
        }
    }
}