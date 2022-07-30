<?php

namespace Polaris\utils;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\utils\Utils;
use Polaris\forms\ModalForm;
use Polaris\player\PolarisPlayer;

class PlayerUtils{

    public static array $properties = [];

    public static function sendVerification(PolarisPlayer $player, callable $action, string $text = ""): void{
        Utils::validateCallableSignature(function(PolarisPlayer $player){}, $action);
        $form = new ModalForm("§l§aPolaris§r§7", "§7Êtes vous sûr $text?", function (PolarisPlayer $player, bool $response) use ($action){
            if($response){
                $action($player);
            }
        });
        $player->sendForm($form);

    }

    public static function TagtoArray(CompoundTag|ListTag $nbt, $name = null): array{
        foreach($nbt->getValue() as $key => $value){
            if($value instanceof CompoundTag || $value instanceof ListTag){
                self::TagtoArray($value, array_search($value, $nbt->getValue(), true));
            }else{
                $name === null ? self::$properties[$key] = $value->getValue() : self::$properties[$name][$key] = $value->getValue();

            }
        }
        return self::$properties;
    }

    public static function arraytoTag(array $array): CompoundTag {
        $nbt = new CompoundTag();
        foreach($array as $property => $value){
            match (gettype($value)){
                "integer" => $nbt->setInt($property, $value),
                "double" => $nbt->setDouble($property, $value),
                "string" => $nbt->setString($property, $value),
                "boolean" => $nbt->setByte($property, $value),
                "array" => $nbt->setTag($property, self::arrayToTag($value)),
            };
        }
        return $nbt;
    }


    public static function getBaseScoreboard(PolarisPlayer $player): Scoreboard{
        $ip = Server::getInstance()->getIp();
        $name = $player->getName();
        return new Scoreboard("§l§aPolaris§r§7", [$ip, $name, "Lobby"]);
    }

    public static function matchPremium(PolarisPlayer $player, object $toMath): bool{
        return $player->isPremium() === $toMath->isPremium() || !$toMath->isPremium() === !$player->isPremium();
    }
}