<?php

namespace UnknowL\Games\Types;

use pocketmine\Server;
use UnknowL\Games\GameInterface;
use UnknowL\Games\GameLoader;
use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\GameUtils;

class ShootCraft extends GameLoader implements GameInterface{

    public array $players = [];


    public function getName(): string
    {
        return 'ShootCraft';
    }

    public function getCreationFunction(): callable
    {
        return function (Server $server){
            foreach ($server->getOnlinePlayers() as $player){
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a commencé !");
            }
        };
    }

    public function getGameId(): int
    {
        return GameUtils::ID_SHOOTCRAFT;
    }

    public function getTime(): int
    {
        return PHP_INT_MAX;
    }

    public function getMaxPlayers(): int
    {
        return PHP_INT_MAX;
    }

    public function join(PolarisPlayer $player){

    }

    public function onTick(): void
    {

    }
}