<?php

namespace UnknowL\Player;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use UnknowL\Games\GameInterface;
use UnknowL\Groups\TeamManager;
use UnknowL\Rank\PremiumRank;
use UnknowL\Utils\PlayerUtils;

class PolarisPlayer extends Player{

    private TeamManager $teamManager;

    public array $request = [];

    private object $rank;

    public bool $isRiding = false, $inGame = false;

    /**
     * @var bool[]
     */
    public array $hasAccepted = [];

    public function initEntity(CompoundTag $nbt): void
    {
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

    /**
     * @param PolarisPlayer $player
     * @param Vector3[] $pos
     * @return bool
     */
    public function inZone(PolarisPlayer $player, array $pos): bool{
        $playerPos = $player->getPosition();
        return $pos[0]->getX() <= $playerPos->getX() && $pos[1]->getX() >= $playerPos->getX() && $pos[0]->getY() <= $playerPos->getY()
            && $pos[1]->getY() >= $playerPos->getY() && $pos[0]->getZ() <= $playerPos->getZ() && $pos[1]->getZ() >= $playerPos->getZ();
    }

    public function isInGame(): bool
    {
        return $this->inGame;
    }


    public function push(): void{
        match ($this->getHorizontalFacing()){
            Facing::NORTH => $this->getMotion()->add(1, 2, 0),
            Facing::SOUTH => $this->getMotion()->add(-1, 2, 0),
            Facing::EAST => $this->getMotion()->add(0, 2, -1),
            Facing::WEST => $this->getMotion()->add(0, 2, 1),
        };
    }

    public function joinGame(GameInterface $game): void{
        $team = $this->teamManager->getTeam();
        if($this->getTeamManager()->hasTeam()){
            foreach ($team->getMembers() as $member){
                if(!$this->inGame){
                    PlayerUtils::sendVerification($member, function (PolarisPlayer $player) use ($game) {
                        $player->sendMessage("§c{$this->getName()} §7à rejoint la partie");
                        $game->join($player);
                    });
                }
            }
        }
    }

    public function canJoin(GameInterface $game): bool{
        return $game->properties->getProperties("Starting") === false && $game->properties->getProperties("Ending") === false && $this->hasAccepted[$game->getName()];
    }

    public function getTeamManager(): TeamManager{
        return $this->teamManager;
    }

}