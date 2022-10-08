<?php

namespace Polaris\cosmetics;

use GdImage;
use pocketmine\entity\Skin;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;

trait CosmeticsTrait
{
    private static function checkCosmetique(): void
    {
        $checkFileAvailable = [];
        $path = Polaris::getInstance()->getDataFolder();
        $allDirs = scandir($path);
        foreach ($allDirs as $foldersName) {
            if (is_dir($path . $foldersName) and $foldersName === "cosmetics") {
                CosmeticsManager::$cosmeticsTypes[] = $foldersName;
                $allFiles = scandir($path . $foldersName);
                foreach ($allFiles as $allFilesName) {
                    if (strpos($allFilesName, ".json")) {
                        $checkFileAvailable[] = str_replace('.json', '', $allFilesName);
                    }
                }
                foreach ($checkFileAvailable as $value) {
                    if (!in_array($value . ".png", $allFiles, true)) {
                        var_dump($value);
                        unset($checkFileAvailable[array_search($value, $checkFileAvailable, true)]);
                    }
                }
                CosmeticsManager::$cosmeticsDetails[$foldersName] = $checkFileAvailable;
                sort(CosmeticsManager::$cosmeticsDetails[$foldersName]);
                $checkFileAvailable = [];
            }
        }
        unset(CosmeticsManager::$cosmeticsTypes[0], CosmeticsManager::$cosmeticsTypes[1], CosmeticsManager::$cosmeticsTypes[array_search("saveskin", CosmeticsManager::$cosmeticsTypes, true)], CosmeticsManager::$cosmeticsDetails["."], CosmeticsManager::$cosmeticsDetails[".."], CosmeticsManager::$cosmeticsDetails["saveskin"]);
        sort(CosmeticsManager::$cosmeticsTypes);
    }

    private static function checkRequirement(): void
    {
        $main = Polaris::getInstance();
        if (!extension_loaded("gd")) {
            $main->getServer()->getLogger()->info("§6Uncomment gd2.dll (remove symbol ';' in ';extension=php_gd2.dll') in bin/php/php.ini to make the plugin working");
            $main->getServer()->getPluginManager()->disablePlugin($main);
            return;
        }
        if (!file_exists($main->getDataFolder() . "steve.json") || !file_exists($main->getDataFolder() . "config.yml")) {
            if (file_exists(str_replace("config.yml", "", $main->getResources()["config.yml"]))) {
                self::recurse_copy(str_replace("config.yml", "", $main->getResources()["config.yml"]), $main->getDataFolder());
            } else {
                $main->getServer()->getLogger()->info("§6Something wrong with the resources");
                $main->getServer()->getPluginManager()->disablePlugin($main);
            }
        }
    }

    private static function recurse_copy($src, $dst): void
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    /**
     * @param string $path
     * @param int $size
     * @return string
     */
    private static function getSkinBytes(string $path, int $size): string
    {
        $img = @imagecreatefrompng($path);
        $skinbytes = "";
        $s = (int)@getimagesize($path)[1];

        for ($y = 0; $y < $s; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $colorat = @imagecolorat($img, $x, $y);
                $a = ((~($colorat >> 24)) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        @imagedestroy($img);
        return $skinbytes;
    }

    /**
     * @throws \JsonException
     */
    public static function resetSkin(PolarisPlayer $player): void
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Polaris::getInstance()->getDataFolder() . "saveskin/" . $name . ".png";
        $path2 = Polaris::getInstance()->getDataFolder() . "saveskin/" . $name . ".txt";
        if (filesize($path2) === 65536) {
            $size = 128;
        } else {
            $size = 64;
        }
        $skinbytes = self::getSkinBytes($path, $size);
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry.humanoid.custom", file_get_contents(Polaris::getInstance()->getDataFolder() . "steve.json")));
        $player->sendSkin();
    }

    public static function saveSkin(Skin $skin, $name): void
    {
        $path = Polaris::getInstance()->getDataFolder();

        if (!file_exists($path . "saveskin")) {
            mkdir($path . "saveskin");
        }

        if (file_exists($path . "saveskin/" . $name . ".txt")) {
            unlink($path . "saveskin/" . $name . ".txt");
        }

        file_put_contents($path . "saveskin/" . $name . ".txt", $skin->getSkinData());

        if (filesize($path . "saveskin/" . $name . ".txt") === 65536) {
            $img = self::toImage($skin->getSkinData(), 128, 128);
        } else {
            $img = self::toImage($skin->getSkinData(), 64, 64);
        }
        imagepng($img, $path . "saveskin/" . $name . ".png");
    }

    public static function toImage($data, $height, $width): GdImage|bool
    {
        $pixelarray = str_split(bin2hex($data), 8);
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $position = count($pixelarray) - 1;
        while (!empty($pixelarray)) {
            $x = $position % $width;
            $y = ($position - $x) / $height;
            $walkable = str_split(array_pop($pixelarray), 2);
            $color = array_map(function ($val) {
                return hexdec($val);
            }, $walkable);
            $alpha = array_pop($color);
            $alpha = ((~((int)$alpha)) & 0xff) >> 1;
            $color[] = $alpha;
            imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, ...$color));
            $position--;
        }
        return $image;
    }

    /**
     * @throws \JsonException
     */
    public static function setSkin(PolarisPlayer $player, string $stuffName, string $locate): void
    {
        $skin = $player->getSkin();
        $name = $player->getName();
        $path = Polaris::getInstance()->getDataFolder() . "saveskin/" . $name . ".txt";
        if (filesize($path) === 65536) {
            $path = self::imgTricky($name, $stuffName, $locate, 128);
            $size = 128;
        } else {
            $size = 64;
            $path = self::imgTricky($name, $stuffName, $locate, 64);
        }

        $skinbytes = self::getSkinBytes($path, $size);
        $player->setSkin(new Skin($skin->getSkinId(), $skinbytes, "", "geometry." . $locate, file_get_contents(Polaris::getInstance()->getDataFolder() . $locate . "/" . $stuffName . ".json")));
        $player->sendSkin();
    }

    public static function imgTricky(string $name, string $stuffName, string $locate, $size): string
    {
        $path = Polaris::getInstance()->getDataFolder();

        $down = imagecreatefrompng($path . "saveskin/" . $name . ".png");
        if ($size === 128) {
            if (file_exists($path . $locate . "/" . $stuffName . "_" . $size . ".png")) {
                $upper = imagecreatefrompng($path . $locate . "/" . $stuffName . "_" . $size . ".png");
            } else {
                $upper = self::resize_image($path . $locate . "/" . $stuffName . ".png", 128, 128);
            }
        } else {
            $upper = imagecreatefrompng($path . $locate . "/" . $stuffName . ".png");
        }
        //Remove black color out of the png
        imagecolortransparent($upper, imagecolorallocatealpha($upper, 0, 0, 0, 127));

        imagealphablending($down, true);
        imagesavealpha($down, true);

        imagecopymerge($down, $upper, 0, 0, 0, 0, $size, $size, 100);

        imagepng($down, $path . 'do_not_delete.png');
        return $path . 'do_not_delete.png';
    }

    public static function resize_image($file, $w, $h, $crop = FALSE): GdImage|bool
    {
        list($width, $height) = getimagesize($file);
        $r = $width / $height;
        if ($crop) {
            if ($width > $height) {
                $width = ceil($width - ($width * abs($r - $w / $h)));
            } else {
                $height = ceil($height - ($height * abs($r - $w / $h)));
            }
            $newwidth = $w;
            $newheight = $h;
        } else {
            if ($w / $h > $r) {
                $newwidth = $h * $r;
                $newheight = $h;
            } else {
                $newheight = $w / $r;
                $newwidth = $w;
            }
        }
        $src = imagecreatefrompng($file);
        $dst = imagecreatetruecolor($w, $h);
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        return $dst;
    }
}