<?php

namespace Polaris\groups;

use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\utils\PlayerUtils;

class TeamManager{

    private Team|null $team;

    private PolarisPlayer $player;

    private int $maxTeamSize = 4;

    public function __construct(PolarisPlayer $player){
        $player->isPremium() ? $this->setLimit(8) : $this->setLimit(4);
        $this->player = $player;
        $this->team = null;
    }

    public function getTeam(): ?Team
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

    public function setTeam(Team|null $team): void
    {
        $this->team = $team;
    }

    public function createTeam(string $name = null): Team {
        if($this->hasTeam()){
            $this->player->sendMessage("§cVous êtes déjà dans une team !");
        }
        $team = new Team($this->player, $name);
        Polaris::$teams[$team->getName()] = $team;
        $this->setTeam($team);
        return $team;
    }

    public function sendInvite(PolarisPlayer $from, Team $team): void{
        if(!$this->hasTeam()){
            if(!$team->isFull()){
                $this->player->addResquest("team", $team->getName());
                $this->player->sendMessage("Vous avez été invités à rejoindre l'équipe {$team->getName()} de la part de {$from->getName()}");
                PlayerUtils::sendVerification($this->player, function (PolarisPlayer $player) use ($team) {
                    $this->addPlayerToTeam($team);
                }, " de vouloir rejoindre cette équipe");
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
        return !is_null($this->team);
    }


}