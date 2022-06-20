<?php

namespace UnknowL\Task;

use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\scheduler\Task;
use pocketmine\Server;
use UnknowL\Player\PolarisPlayer;

class ScoreboardTask extends Task
{
    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player){
            if($player instanceof PolarisPlayer){
                $player->getScoreboard()->resetScoreboard($player);
                $player->getScoreboard()->sendScoreboard($player);
            }
        }
    }
}
