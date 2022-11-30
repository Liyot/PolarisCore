<?php

namespace Polaris\cosmetics;


use pocketmine\block\utils\TreeType;
use pocketmine\entity\Skin;
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
        self::checkRequirement();
        self::checkCosmetique();
    }

    public static function formCosmetics(PolarisPlayer $player): void {
        $form = new MenuForm("§m§a§fCosmetics", "", [new Button("§lReset", Image::path("textures/ui/refresh")), new Button("§lSkin")],
        function (PolarisPlayer $player, Button $selected) {
            switch ($selected->text)
            {
                case "§lReset" :  self::resetSkin($player); break;
                case "§lSkin" : self::skinForm($player); break;

                default :
                    self::setSkin($player, strtolower(substr($selected->text, 3)), "cosmetics"); break;

            }
        });
        foreach (self::$cosmeticsDetails as $cosmetic) {
            $form->appendButtons(new Button("§l".ucfirst(reset($cosmetic)), Image::path("textures/ui/mashup_hangar")));
        }
        $player->sendForm($form);
    }

    //CREDIT: Slayer est un fdp
    private static function skinForm(PolarisPlayer $player): void
    {
        $buttons = array_map(fn($v) => "§l".$v, array_filter(scandir(Polaris::getInstance()->getDataFolder() . "cosmetics/skin"), function ($v, $k = 0)
        {
            if(!in_array($v, [".", "..", "tool"])){
                return true;
            }
            return false;
        }));
        $form = new MenuForm("§m§a§fSkin", "", array_values(array_map(fn ($v) => new Button($v) ,$buttons)), function (PolarisPlayer $player, Button $selected)
        {
            $skin = new Skin("Hihi", self::PNGtoBYTES(($text = Polaris::getInstance()->getDataFolder()."cosmetics/skin/". substr($selected->text, 3, strpos($selected->text, ".") - 3)).".png"),
                "", "geometry.unknown", file_get_contents($text.".json"));
            $player->setSkin($skin);
            $player->sendSkin();
        });
        $player->sendForm($form);
    }
}