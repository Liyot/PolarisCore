<?php

namespace UnknowL\Games\Types;

use pocketmine\Server;
use UnknowL\Games\GameInterface;
use UnknowL\Games\GameLoader;
use UnknowL\Games\GameProperties;
use UnknowL\Player\PolarisPlayer;
use UnknowL\Utils\GameUtils;
use UnknowL\Utils\PlayerUtils;

class ShootCraft extends GameLoader implements GameInterface{


    /**
     * @var PolarisPlayer[]
     */
    public array $players = [];

    public GameProperties $properties;

    public function __construct(){
        $this->initProperties();

        $this->addCallback('Creation', function (Server $server){
            foreach ($server->getOnlinePlayers() as $player){
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a commencé !");
            }
        });

        $this->addCallback('Stop', function (Server $server){
            foreach ($server->getOnlinePlayers() as $player){
                $player->sendMessage("§l§b[§aShootCraft§b] §aLe shootcraft a fini !");
            }
        });
    }

    public function initProperties(): void
    {
        $gameProperties = new GameProperties();
        $gameProperties->setBaseProperties();
        $gameProperties->setProperties("Starting", true);
        $this->properties = $gameProperties;
    }



    public function getName(): string
    {
        return 'ShootCraft';
    }


    public function onCreation(): void{
        $this->processCallback('Creation', [Server::getInstance()]);
    }

    public function onStart(): void
    {
        $this->properties->setProperties('Starting', false)->setProperties('Running', true);
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
        }else{
            $player->push();
            PlayerUtils::sendVerification($player, function (PolarisPlayer $player){
                $player->hasAccepted[$this->getName()] = true;
                $player->sendMessage("Vous Pouvez rejoindre");
            }, " de vouloir rentré dans le ".$this->getName());
        }
    }

    public function onTick(): void
    {
    }

    public function onStop(): void
    {
        foreach ($this->players as $player){
            $player->inGame = false;
        }
    }
}