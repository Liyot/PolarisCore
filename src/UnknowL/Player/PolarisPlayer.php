<?php

namespace UnknowL\Player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use UnknowL\Games\GameInterface;
use UnknowL\Groups\TeamManager;
use UnknowL\Rank\PremiumRank;
use UnknowL\Trait\VectorUtilsTrait;
use UnknowL\Utils\PlayerUtils;

class PolarisPlayer extends Player{

    use VectorUtilsTrait;

    private TeamManager $teamManager;

    public array $request = [];

    private object $rank;

    public GameInterface|null $actualGame = null;

    public bool $isRiding = false, $inGame = false;

    /**
     * @var bool[]
     */
    public array $hasAccepted = [];

    public function initEntity(CompoundTag $nbt): void
    {
        $this->player = $this;
        $this->rank = new PremiumRank();
        parent::initEntity($nbt);
        $this->teamManager = new TeamManager($this);
    }

    public function addResquest(string $name, mixed $value = null): void
    {
        $this->request[$name][] = $value;
    }

    public function isRiding(): bool
    {
        return $this->isRiding;
    }

    public function setRiding(bool $ride): void
    {
        $this->isRiding = $ride;
    }


    public function getRequest(string $name): mixed
    {
        return $this->request[$name] ?? null;
    }

    public function isPremium(): bool
    {
        return $this->rank instanceof PremiumRank;
    }



    public function isInGame(): bool
    {
        return $this->inGame;
    }

    public function getActualGame(): ?GameInterface
    {
        return $this->actualGame;
    }


    public function push(): void{
        $vector = $this->getMatchedVector(1, 1);
        $this->getMotion()->add($vector->x, $vector->y, $vector->z);
    }

    public function leaveGame(GameInterface $game): void{

    }

    public function joinGame(GameInterface $game): void{
        $team = $this->teamManager->getTeam();
        if($this->getTeamManager()->hasTeam()){
            foreach ($team->getMembers() as $member){
                if(!$this->inGame){
                    PlayerUtils::sendVerification($member, function (PolarisPlayer $player) use ($member, $game) {
                        $member->sendMessage("§c{$this->getName()} §7à rejoint la partie");
                        $game->join($player);
                    });
                }
            }
        }else{
            $game->join($this);
        }
    }

    public function canJoin(GameInterface $game): bool{
        return !$game->properties->getProperties("Starting") && !$game->properties->getProperties("Ending") && $this->hasAccepted($game);
    }

    public function hasAccepted(GameInterface $game): bool{
        return $this->hasAccepted[$game->getName()] ?? false;
    }

    public function getTeamManager(): TeamManager{
        return $this->teamManager;
    }

}