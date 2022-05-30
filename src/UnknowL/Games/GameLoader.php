<?php

namespace UnknowL\Games;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Server;
use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\GameUtils;

class GameLoader{

    private static GameInterface $game;

    /**
     * @param Vector3[] $pos
     * @param int $type
     * @return PolarisPlayer[]|Item[]|Block[]|null
     */
    public function scanZone(array $pos, int $type): array|null
    {
        switch ($type){
            case 0:
                $players = [];
                foreach (Server::getInstance()->getOnlinePlayers() as $player){
                    if($player instanceof PolarisPlayer){
                        if($player->inZone($player, $pos)){
                            $players[$player->getUniqueId()->toString()] = $player;
                        }
                    }
                }
                return $players;
            break;
        }
        return null;
    }

    public static function init(){
        self::registerGame();
    }

    private static function registerGame(): void{
        $dir = __DIR__ . "/Types/";
        foreach (scandir($dir) as $file){
            if(is_file($dir . $file)){
                $class = explode(".", $file)[0];
                $class = new $dir.$class();
                if($class instanceof GameInterface){
                    self::$game[$class->getName()] = $class;
                }
            }
        }
    }
}