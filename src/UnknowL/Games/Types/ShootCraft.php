<?php

namespace UnknowL\Games\Types;

use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;
use UnknowL\Games\GameInterface;
use UnknowL\Games\GameLoader;
use UnknowL\Games\GameProperties;
use UnknowL\Games\Zone;
use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\GameUtils;
use UnknowL\Utils\PlayerUtils;

class ShootCraft extends GameLoader implements GameInterface{


    /**
     * @var PolarisPlayer[]
     */
    public array $players = [];

    private Zone $zone;

    public GameProperties $properties;

    public function __construct(){

        $world = Server::getInstance()->getWorldManager()->getWorldByName("world");
        $this->zone = new Zone("ShootCraft", $this, new Position(0, 50, 0, $world), new Position(40, 100, 40, $world));

        $this->addCallback('Start', function (Server $server){
            $this->initProperties();
            $this->properties->setProperties('Starting', false)->setProperties('Running', true);
            foreach ($server->getOnlinePlayers() as $player){
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a commencé !");
            }
        });

        $this->addCallback('Stop', function (Server $server){
            var_dump($this->zone->getEntities());
            foreach ($this->zone->getEntities() as $entity){
                $entity->close();
            }
            foreach ($server->getOnlinePlayers() as $player){
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a fini !");
            }
        });
        $this->onCreation();
    }

    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function initProperties(): void
    {
        $gameProperties = new GameProperties();
        $gameProperties->setBaseProperties();
        $gameProperties->setProperties("Starting", true)->setProperties("AcceptPlayerWhenRunning", true);
        $this->properties = $gameProperties;
    }



    public function getName(): string
    {
        return 'ShootCraft';
    }


    public function onCreation(): void{
        $this->onStart();
    }

    public function onStart(): void
    {
        $this->processCallback('Start', [Server::getInstance()]);
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

    public function join(PolarisPlayer $player): void{
        if($player->canJoin($this)){
            $player->sendMessage("§l§b[§aShootCraft§b] §aVous avez rejoint le ShootCraft !");
            $this->players[$player->getUniqueId()->toString()] = $player;
            $player->inGame = true;
            $this->sendScoreboard($player, $this);
            $player->actualGame = $this;
        }else{
            $player->push();
            PlayerUtils::sendVerification($player, function (PolarisPlayer $player){
                $player->hasAccepted[$this->getName()] = true;
            }, " de vouloir rentré dans le ".$this->getName());
        }
    }

    public function leave(PolarisPlayer $player): void{
        if(isset($this->players[$player->getUniqueId()->toString()])){
            unset($this->players[$player->getUniqueId()->toString()]);
            $player->inGame = false;
            $player->actualGame = null;
            $player->hasAccepted[$this->getName()] = false;
            $player->setScoreboard(PlayerUtils::getBaseScoreboard($player));
            $player->sendMessage("§l§b[§aShootCraft§b] §aVous avez quitté le ShootCraft !");
        }
    }

    public function onTick(): void
    {
    }

    public function onStop(): void
    {
        $this->processCallback('Stop', [Server::getInstance()]);
        foreach ($this->players as $player){
            $player->inGame = false;
        }
    }
}