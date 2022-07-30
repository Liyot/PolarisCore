<?php

namespace Polaris\games;

use pocketmine\player\GameMode;
use pocketmine\utils\Utils;
use Polaris\player\PolarisPlayer;
use Polaris\utils\PlayerUtils;
use Polaris\utils\Scoreboard;

abstract class Game implements GameInterface{

    /**
     * @var PolarisPlayer[]
     */
    public array $players = [];

    public GameProperties $properties;

    /**
     * @var callable[]
     */
    protected array $GameProcessCallback = [];

    public function __construct(protected int $id, protected int $maxPlayer, protected int $time, protected string  $name = ""){
        $this->initProperties();
    }

    abstract public function getName(): string;

    public function join(PolarisPlayer $player): void{
        $player->setGamemode(GameMode::SURVIVAL());
        $player->sendMessage("§l§b[§a{$this->getName()}§b] §aVous avez rejoint le {$this->getName()} !");
        $this->players[$player->getUniqueId()->toString()] = $player;
        $player->inGame = true;
        $player->getInventory()->clearAll();
        $this->sendScoreboard($player, $this);
        $player->actualGame = $this;
        $player->hasAccepted[$this->getName()] = false;
    }

    public function preJoin(PolarisPlayer $player): void{
        if($player->canJoin($this) && count($this->getPlayers()) < $this->getMaxPlayers()){
            $this->join($player);
        }else{
            $player->push();
            PlayerUtils::sendVerification($player, function (PolarisPlayer $player){
                $player->hasAccepted[$this->getName()] = true;
                $this->join($player);
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
            $player->sendMessage("§l§b[§{$this->getName()}§b] §aVous avez quitté le {$this->getName()} !");
            $player->teleportToSpawn();
        }
    }

    public function sendScoreboard(PolarisPlayer $player): void
    {
        $player->setScoreboard(new Scoreboard("§l§b[§a".$this->getName()."§b]", ["aaaaaa", "bbbbb", "cccccc"]));
    }

     final public function addCallback(string $name, callable $callback): void{
        if(!isset($this->GameProcessCallback[$name])){
            $this->GameProcessCallback[$name] = $callback;
        }
    }

     final public function processCallback(string $name, ...$args): void{
        if(isset($this->GameProcessCallback[$name])){
            $this->GameProcessCallback[$name](...$args);
        }
    }

    public function initProperties(): void
    {
        $gameProperties = new GameProperties();
        $gameProperties->setBaseProperties();
        $gameProperties->setProperties("Starting", true);
        $this->properties = $gameProperties;
    }

    public function getGameId(): int
    {
        return $this->id;
    }

    public function Joinable(): bool
    {
        return $this->properties->getProperties('Starting') && count($this->getPlayers()) < $this->getMaxPlayers();
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function getMaxPlayers(): int
    {
        return $this->maxPlayer;
    }

    public function onTick(): void
    {
    }

    public function onStart(): void
    {
    }

    public function getPlayers(): array
    {
        return $this->players;
    }

    public function onCreation(): void
    {
    }

    public function onStop(): void
    {
    }
}