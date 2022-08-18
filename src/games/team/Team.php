<?php

namespace Polaris\games\team;

use Polaris\player\PolarisPlayer;

final class Team
{
    /** @var PolarisPlayer[] */
    private array $players = [];

    public function __construct(protected string $name, protected int $maxPlayer = 2)
    {
    }

    public function addPlayers(PolarisPlayer ...$players): void
    {
        foreach ($players as $player) {
            $this->players[$player->getUniqueId()->toString()] = $player;
            $player->setTeam($this);
        }
    }

    public function removePlayers(PolarisPlayer ...$players): void
    {
        foreach ($players as $player) {
            if (!isset($this->player[$player->getUniqueId()->toString()])) continue;
            unset($this->player[$player->getUniqueId()->toString()]);
            $player->removeTeam();
        }
    }


    public function getPlayers(): array
    {
        return $this->players;
    }

    public function getMaxPlayer(): int
    {
        return $this->maxPlayer;
    }
    public function getName(): string
    {
        return $this->name;
    }
}