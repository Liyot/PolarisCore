<?php

namespace Polaris\cosmetics;


use Polaris\forms\menu\Button;
use Polaris\forms\menu\Image;
use Polaris\forms\MenuForm;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;

abstract class CosmeticsManager
{
    use CosmeticsTrait;

    public static array $cosmeticsTypes = [];
    public static array $cosmeticsDetails = [];

    public static function initCosmetics(): void
    {
        Polaris::getInstance()->saveResource("steve.json", false);
        Polaris::getInstance()->saveResource("cosmetics/Ailes d'Ange.json", false);
        Polaris::getInstance()->saveResource("cosmetics/Ailes d'Ange.png", false);
        self::checkRequirement();
        self::checkCosmetique();
    }

    public static function formCosmetics(PolarisPlayer $player): void {
        $form = new MenuForm("§m§a§fCosmetics", "", [new Button("§lReset", Image::path("textures/ui/refresh"))],
        function (PolarisPlayer $player, Button $selected) {
            switch ($selected->text)
            {
                case "§lReset" :  self::resetSkin($player); break;

                default :
                    self::setSkin($player, strtolower(substr($selected->text, 3)), "cosmetics"); break;

            }
        });
        foreach (self::$cosmeticsDetails as $cosmetic) {
            $form->appendButtons(new Button("§l".ucfirst(reset($cosmetic)), Image::path("textures/ui/mashup_hangar")));
        }
        $player->sendForm($form);
    }
}