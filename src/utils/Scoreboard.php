<?php

namespace Polaris\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use Polaris\player\PolarisPlayer;

class Scoreboard
{

    private string $name;
    /**
     * @var $lines ScorePacketEntry[]
     */
    private array $entries = [], $lines = [];

    public function __construct(string $name, array $entries = [])
    {

        $this->name = $name;
        $this->entries = $entries;
    }

    public function resetScoreboard(PolarisPlayer $player): void
    {
        $pk = new RemoveObjectivePacket();
        $pk->objectiveName = $player->getName();
        $player->getNetworkSession()->sendDataPacket($pk);
    }

    public function sendScoreboard(PolarisPlayer $player): void
    {
        $pk = new SetDisplayObjectivePacket();
        $pk->objectiveName = $player->getName();
        $pk->displayName = $this->name;
        $pk->sortOrder = 0;
        $pk->displaySlot = 'sidebar';
        $pk->criteriaName = 'dummy';
        $count = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        foreach ($this->entries as $entry){
            $this->addLine($count, $player, $entry);
            $count++;
        }
    }

    public function addLine(int $line,PolarisPlayer $player, string $text): void{

        if(isset($this->lines[$line])){
            $pk = new  SetScorePacket();
            $pk->type = SetScorePacket::TYPE_REMOVE;
            $pk->entries[] = $this->lines[$line];
            $player->getNetworkSession()->sendDataPacket($pk);
            unset($this->lines[$line]);
        }
        $packet = new ScorePacketEntry();
        $packet->score = $line;
        $packet->objectiveName = $player->getName();
        $packet->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $packet->customName = $text;
        $packet->scoreboardId = $line;
        $packet->actorUniqueId = $player->getId();
        $this->lines[$line] = $packet;
        $scoreboard = SetScorePacket::create(SetScorePacket::TYPE_CHANGE, [$packet]);
        $player->getNetworkSession()->sendDataPacket($scoreboard);
    }

    public function getText(int $line): string
    {
        return $this->lines[$line]->customName ?? "";
    }
}