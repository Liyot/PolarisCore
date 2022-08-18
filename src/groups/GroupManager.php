<?php

namespace Polaris\groups;

use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\utils\PlayerUtils;

class GroupManager{

    private Group|null $group;

    private PolarisPlayer $player;

    private int $maxGroupSize = 4;

    public function __construct(PolarisPlayer $player){
        $player->isPremium() ? $this->setLimit(8) : $this->setLimit(4);
        $this->player = $player;
        $this->group = null;
    }

    public function getGroup(): ?Group
    {
        return $this->group;
    }

    public function getLimit(): int
    {
        return $this->maxGroupSize;
    }

    public function setLimit(int $limit): void
    {
        $this->maxGroupSize = $limit;
    }

    public function setGroup(Group|null $group): void
    {
        $this->group = $group;
    }

    public function createGroup(string $name = null): Group {
        if($this->hasGroup()){
            $this->player->sendMessage("§cVous êtes déjà dans le groupe ". $this->player->getGroupManager()->getGroup()->getName(). " !");
        }
        $group = new Group($this->player, $name);
        Polaris::$groups[$group->getName()] = $group;
        $this->setGroup($group);
        return $group;
    }

    public function sendInvite(PolarisPlayer $from, Group $group): void{
        if(!$this->hasGroup()){
            if(!$group->isFull()){
                $this->player->addResquest("team", $group->getName());
                $this->player->sendMessage("Vous avez été invités à rejoindre l'équipe {$group->getName()} de la part de {$from->getName()}");
                PlayerUtils::sendVerification($this->player, function (PolarisPlayer $player) use ($group) {
                    $this->addPlayerToGroup($group);
                }, " de vouloir rejoindre cette équipe");
                $this->addPlayerToGroup($group);
            }
        }
    }

    public function addPlayerToGroup(Group $group): void
    {
        if($this->group === null){
            $this->group = $group;
            $group->addMember($this->player);
        }else{
            PlayerUtils::sendVerification($this->player, function (PolarisPlayer $player) use ($group) {
                $this->group->removeMember($player);
                $this->setGroup($group);
                $group->addMember($player);
            });
        }
    }

    public function hasGroup(): bool{
        return !is_null($this->group);
    }


}