<?php

namespace Polaris\task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use Polaris\player\PolarisPlayer;

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
