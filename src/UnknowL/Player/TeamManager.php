<?php

namespace UnknowL\Player;

use JetBrains\PhpStorm\Pure;
use UnknowL\ToneriaPlayer;

class TeamManager{


    private array $team = [];

    private ToneriaPlayer $player;

    private int $maxTeamSize = 4;

    #[Pure] public function __construct(PolarisPlayer $player){
        if($player->isPremium()){
            $this->maxTeamSize = 8;
        }else{
            $this->maxTeamSize = 4;
        }
        $this->player = $player;
    }

    public function addPlayerToTeam(){}

    public function hasTeam(): bool{
        return empty($this->team);
    }


}