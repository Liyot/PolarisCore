<?php

namespace UnknowL\Utils;

use pocketmine\utils\Utils;
use UnknowL\forms\ModalForm;
use UnknowL\Player\PolarisPlayer;

class PlayerUtils{

    public static function sendVerification(PolarisPlayer $player, callable $action, string $text = ""): void{
        Utils::validateCallableSignature(function(PolarisPlayer $player){}, $action);
        $form = new ModalForm("§l§aPolaris§r§7", "§7Êtes vous sûr $text?", function (PolarisPlayer $player, bool $response) use ($action){
            if($response){
                $action($player);
            }
        });
        $player->sendForm($form);
    }

    public static function matchPremium(PolarisPlayer $player, object $toMath): bool{
        return $player->isPremium() === $toMath->isPremium() || !$toMath->isPremium() === !$player->isPremium();
    }
}