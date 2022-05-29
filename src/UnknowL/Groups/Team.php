<?php

namespace UnknowL\Groups;


use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\ChatUtils;

class Team{

    /**
     * @var array PolarisPlayer[]
     */
    private array $members = [];

    private string $name;

    private bool $premium;

    private PolarisPlayer $owner;

    private int $maxMembers = 0;

    public function __construct( PolarisPlayer $owner){
        $this->owner = $owner;
        $this->addMember($owner);
        $this->name = $this->owner->getName()." Team";
        $this->premium = $this->owner->isPremium();
        $this->maxMembers = $this->isPremium()? 8 : 4;
    }

    public function setInGame(){}

    public function sendMessage(string $message, string $from): void
    {
        foreach($this->members as $member){
            $member->sendMessage($message);
        }
    }

    public function isFull(): bool
    {
        return count($this->members) === $this->maxMembers;
    }

    public function setName(string $name): void
    {
        if(ChatUtils::containBadWord($name)){
            $this->owner->sendMessage("§cLe nom de votre team ne peut pas contenir de mots interdits");
            return;
        }
        $this->name = $name;
    }

    public function delete(): void
    {
        $this->sendMessage("§cVotre team a été supprimée");

        foreach($this->members as $member){
            $member->setTeam(null);
        }
        $this->members = [];
    }

    public function leave(PolarisPlayer $player){
        if(!$this->isMember($player)){
            return;
        }
        $this->sendMessage("§c{$player->getName()} a quitté votre équipe");
        $player->getTeamManager()->setTeam(null);
        $player->sendMessage("§cVous avez quitté votre équipe");
        $this->removeMember($player);
    }

    public function kick(PolarisPlayer $player): void
    {
        if(!$this->isMember($player)){
            return;
        }
        $this->removeMember($player);
        $player->sendMessage("§cVous avez été expulsé de l'équipe.");
    }

    public function isMember(PolarisPlayer $player): bool
    {
        return isset($this->members[$player->getUniqueId()->toString()]);
    }

    public function setPremium(bool $premium): void
    {
        foreach ($this->members as $member){
            if($member->isPremium()){
                $this->premium = true;
            }else{
                $this->sendMessage("Vous ne pouvez pas mettre votre équipe en premium car certains membre ne sont pas premium");
            }
        }
        $this->premium = $premium;
    }

    public function isPremium(): bool
    {
        return $this->premium;
    }



    public function getOwner(): PolarisPlayer
    {
        return $this->owner;
    }

    public function isOwner(PolarisPlayer $player): bool
    {
        return $this->owner->getName() === $player->getName();
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getName(): string
    {
        return $this->name;
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