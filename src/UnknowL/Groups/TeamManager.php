<?php

namespace UnknowL\Groups;

use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\PlayerUtils;

class TeamManager{

    private Team $team;

    private PolarisPlayer $player;

    private int $maxTeamSize = 4;

    public function __construct(PolarisPlayer $player){
        $player->isPremium() ? $this->setLimit(8) : $this->setLimit(4);
        $this->player = $player;
    }

    public function getTeam(): Team
    {
        return $this->team;
    }

    public function setLimit(int $limit): void
    {
        $this->maxTeamSize = $limit;
    }



    public function addPlayerToTeam(Team $team){
        if($this->team === null){
            $this->team = $team;
            $team->addMember($this->player);
        }else{
            PlayerUtils::sendVerification($this->player, function (PolarisPlayer $player) use ($team) {
                $this->team->removeMember($player);
                $this->team = $team;
                $team->addMember($player);
            });
        }
    }

    public function hasTeam(): bool{
        return empty($this->team);
    }


}