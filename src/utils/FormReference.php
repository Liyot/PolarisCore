<?php

namespace Polaris\utils;

use pocketmine\form\Form;
use Polaris\forms\menu\Button;
use Polaris\forms\MenuForm;
use Polaris\games\GameLoader;
use Polaris\player\PolarisPlayer;

abstract class FormReference{

    public static function MDJForm() : Form{
        $form = MenuForm::withOptions("Mode de jeux", "", GameUtils::GameName,  function (PolarisPlayer $player, Button $selected){
            $name = strtolower($selected->text);
            $game = GameLoader::getInstance()->getDisponibleGame(str_replace(' ',  '', $name));
			var_dump($game->count);
            $game->preJoin($player);
        });
        return $form;
    }
}