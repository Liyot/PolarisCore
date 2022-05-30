<?php

namespace UnknowL\Games;

interface GameInterface
{

    public function getName(): string;

    public function getCreationFunction(): callable;

    public function getGameId(): int;

    public function getTime(): int;

    public function getMaxPlayers(): int;

    public function onTick(): void;

}