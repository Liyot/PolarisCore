<?php

namespace UnknowL\Games;

use UnknowL\Player\PolarisPlayer;

interface GameInterface
{

    public function getName(): string;

    public function getGameId(): int;

    public function getTime(): int;

    public function getMaxPlayers(): int;

    public function getZone(): Zone;

    public function onTick(): void;

    public function onStart(): void;

    public function join(PolarisPlayer $player): void;

    public function leave(PolarisPlayer $player): void;

    public function onCreation(): void;

    public function onStop(): void;

}