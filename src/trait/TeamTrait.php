<?php

namespace Polaris\trait;

use Polaris\games\team\Team;
use Polaris\player\PolarisPlayer;

trait TeamTrait
{
    /**
     * @var Team[]
     */
    private array $teams = [];

    protected function addTeam(Team ...$teams): void
    {
        foreach ($teams as $team) {
            $this->teams[$team->getName()] = $team;
        }
    }

    protected function getTeam(string $team): ?Team
    {
        return $this->teams[$team] ?? null;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    protected function removeTeam(Team ...$teams): void
    {
        foreach ($teams as $team) {
            if (!isset($this->teams[$team->getName()])) continue;
            unset($this->teams[$team->getName()]);
        }
    }
}