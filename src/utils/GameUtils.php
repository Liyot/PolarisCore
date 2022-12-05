<?php

namespace Polaris\utils;

use pocketmine\Server;
use pocketmine\world\Position;
use pocketmine\world\World;

class GameUtils{

    public const ID_SHOOTCRAFT = 0;
    public const ID_GETDOWN = 1;
    public const ID_RUSHGAME = 2;
    public const ID_WALLRUN = 3;
    public const ID_JUMP = 4;

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

	final public static function getRushWorldDir(): string
	{
		return Server::getInstance()->getDataPath()."worlds\\";
	}

	public static function getSpawnPosition(): Position
	{
		return new Position(-57, 60, -68, Server::getInstance()->getWorldManager()->getWorldByName("PolarisSpawn"));
	}

    public static function PNGtoBYTES(string $path)
    {
        $img = @imagecreatefrompng($path);
        $skinbytes = "";
        list($width, $height) = @getimagesize($path);
        for($y = 0; $y < $height; $y++){
            for($x = 0; $x < $width; $x++){
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $skinbytes;
    }

    public const PROPERTIES_ACCEPT_PLAYERS = "acceptplayerwhenrunning";

}