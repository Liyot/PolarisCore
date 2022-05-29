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

    public function getLimit(): int
    {
        return $this->maxTeamSize;
    }

    public function setLimit(int $limit): void
    {
        $this->maxTeamSize = $limit;
    }

    public function setTeam(Team $team): void
    {
        $this->team = $team;
    }

    public function createTeam(): Team {
        if($this->hasTeam() !== null){
            $this->player->sendMessage("§cVous êtes déjà dans une team !");
        }
        $team = new Team($this->player);
        $this->setTeam($team);
        return $team;
    }

    public function sendInvite(PolarisPlayer $from, Team $team): void{
        if(!$this->hasTeam()){
            if(!$team->isFull()){
                $this->player->sendMessage("Vous avez été invités à rejoindre l'équipe {$team->getName()} de la part de {$from->getName()}");
                PlayerUtils::sendVerification($this->player, function (PolarisPlayer $player) use ($team) {
                    $this->addPlayerToTeam($team);
                }, "de vouloir rejoindre cette équipe");
                $this->addPlayerToTeam($team);
            }
        }
    }

    public function addPlayerToTeam(Team $team): void
    {
        if($this->team === null){
            $this->team = $team;
            $team->addMember($this->player);
        }else{
            PlayerUtils::sendVerification($this->player, function (PolarisPlayer $player) use ($team) {
                $this->team->removeMember($player);
                $this->setTeam($team);
                $team->addMember($player);
            });
        }
    }

    public function hasTeam(): bool{
        return empty($this->team);
    }


}