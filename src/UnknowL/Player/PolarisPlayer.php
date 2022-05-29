<?php

namespace UnknowL\Player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use UnknowL\Groups\TeamManager;
use UnknowL\Rank\PremiumRank;

class PolarisPlayer extends Player{

    private TeamManager $teamManager;

    public array $request = [];

    private object $rank;

    public function initEntity(CompoundTag $nbt): void
    {

        parent::initEntity($nbt);
        $this->teamManager = new TeamManager($this);
    }

    public function addResquest(string $name, mixed $value = null): void
    {
        $this->request[$name] = $value;
    }

    public function getRequest(string $name): mixed
    {
        return $this->request[$name];
    }

    public function isPremium(): bool
    {
        return $this->rank instanceof PremiumRank;
    }

    public function getTeamManager(): TeamManager{
        return $this->teamManager;
    }

}