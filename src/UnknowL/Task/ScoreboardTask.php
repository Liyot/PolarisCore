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
    public array $lines = [];

    public function onRun(): void
    {
        foreach (Server::getInstance()->getOnlinePlayers() as $player){
            if($player instanceof PolarisPlayer){
                $this->sendScoreboard($player);
            }
        }
    }

    public function sendScoreboard(PolarisPlayer $player): void
    {
        $pk = new SetDisplayObjectivePacket();
        $pk->objectiveName = $player->getName();
        $pk->displayName = '§l§aPolaris';
        $pk->sortOrder = 0;
        $pk->displaySlot = 'sidebar';
        $pk->criteriaName = 'dummy';
        $ip = Server::getInstance()->getIp();
        $grade =
        $niveau =
        $name = $player->getName();
        $this->addLine(0, $player, $ip);
        $this->addLine(2, $player, $name);
        $this->addLine(1, $player, "Lobby");
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function addLine(int $id,PolarisPlayer $player, string $line): void{

        if(isset($this->lines[$id])){
            $pk = new SetScorePacket();
            $pk->type = SetScorePacket::TYPE_REMOVE;
            $pk->entries[] = $this->lines[$id];
            $player->getNetworkSession()->sendDataPacket($pk);
            unset($this->lines[$id]);
        }
        $packet = new ScorePacketEntry();
        $packet->score = $id;
        $packet->objectiveName = $player->getName();
        $packet->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $packet->customName = $line;
        $packet->scoreboardId = $id;
        $packet->actorUniqueId = $player->getId();
        $this->lines[$id] = $packet;
        $scoreboard = SetScorePacket::create(SetScorePacket::TYPE_CHANGE, [$packet]);
        $player->getNetworkSession()->sendDataPacket($scoreboard);
    }
}
