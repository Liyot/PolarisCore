<?php

namespace Polaris\games;

use Polaris\player\PolarisPlayer;

interface GameInterface
{

    public function getGameId(): int;

    public function getTime(): int;

    public function getMaxPlayers(): int;

    public function onTick(): void;

    public function onStart(): void;

    public function getPlayers(): array;

    public function join(PolarisPlayer $player): void;

    public function leave(PolarisPlayer $player): void;

    public function onCreation(): void;

    public function onStop(): void;

}