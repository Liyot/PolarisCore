<?php

namespace Polaris\utils;

use pocketmine\Server;
use pocketmine\world\World;

class GameUtils{

    public const ID_SHOOTCRAFT = 0;
    public const ID_GETDOWN = 1;
    public const ID_RUSHGAME = 2;

    public const NOT_LOADED = [
        "getdown",
        "roundedgames"
    ];

    public const GameName = [
        self::ID_SHOOTCRAFT => "ShootCraft",
        self::ID_GETDOWN => "GetDown",
        self::ID_RUSHGAME => "RushGame"
    ];

    public static function getSpawnWorld(): World{
        return Server::getInstance()->getWorldManager()->getWorldByName('PolarisSpawn');
    }

    public const PROPERTIES_ACCEPT_PLAYERS = "acceptplayerwhenrunning";

}