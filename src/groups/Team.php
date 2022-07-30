<?php

namespace Polaris\groups;


use Polaris\player\PolarisPlayer;
use Polaris\utils\ChatUtils;

class Team{

    /**
     * @var array PolarisPlayer[]
     */
    private array $members = [];

    private string $name;

    private bool $premium;

    private PolarisPlayer|null $owner;

    private int $maxMembers;

    public function __construct( PolarisPlayer $owner, string $name = null){
        $this->owner = $owner;
        $this->addMember($owner);
        $this->name = is_null($name) ? $this->owner->getName()." Team" : $name;
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
            $this->owner->sendMessage("§cLe nom de votre équipe ne peut pas contenir de mots interdits");
            return;
        }
        $this->name = $name;
    }

    public function delete(): void
    {
        $this->sendMessage("§cVotre équipe a été supprimée", $this->owner);

        foreach($this->members as $member){
            $member->getTeamManager()->setTeam(null);
        }
        $this->owner = null;
        $this->members = [];
    }

    public function leave(PolarisPlayer $player): void
    {
        if(!$this->isMember($player)){
            return;
        }

        $player->getTeamManager()->setTeam(null);
        $player->sendMessage("§cVous avez quitté votre équipe");
        $this->removeMember($player);

        $this->sendMessage("§c{$player->getName()} a quitté votre équipe", $player);

        if(count($this->members) === 0){
            $this->delete();
        }
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
        if($this->getOwner()->isPremium()){
            $this->premium = true;
        }else{
            $this->owner->sendMessage("Vous ne pouvez pas mettre votre équipe car vous n'êtes pas premium");
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

    public function getMaxMembers(): int
    {
        return $this->maxMembers;
    }

    public function addMember(PolarisPlayer $player): void
    {
        $this->members[$player->getUniqueId()->toString()] = $player;
    }

    public function removeMember(PolarisPlayer $player): void
    {
        if ($this->isOwner($player)){
           $rand = array_rand($this->members);
            $this->setOwner($this->members[$rand[0]]);
        }
        $player->getTeamManager()->setTeam(null);
        unset($this->members[$player->getUniqueId()->toString()]);
    }

    public function setOwner(PolarisPlayer $player): void
    {
        if(!$this->isMember($player)){
            return;
        }
        $player->sendMessage("§cVous êtes désormais le propriétaire de votre équipe");
        $this->owner = $player;
    }

}