<?php

namespace UnknowL\Groups;


use UnknowL\Player\PolarisPlayer;

class Team{

    private array $members = [];

    private int $maxMembers = 0;

    public function __construct(int $max){
        $this->maxMembers = $max;
    }

    public function setInGame(){}

    public function sendMessage(string $message): void
    {
        foreach($this->members as $member){
            $member->sendMessage($message);
        }
    }

    public function addMember(PolarisPlayer $player): void
    {
        $this->members[$player->getUniqueId()->toString()] = $player;
    }

    public function removeMember(PolarisPlayer $player): void
    {
        unset($this->members[$player->getUniqueId()->toString()]);
    }

}