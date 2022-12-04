<?php

namespace Polaris\games;

use pocketmine\player\GameMode;
use pocketmine\utils\Utils;
use Polaris\games\lobby\WaitingLobby;
use Polaris\player\PolarisPlayer;
use Polaris\trait\callBackTrait;
use Polaris\utils\PlayerUtils;
use Polaris\utils\Scoreboard;

abstract class Game implements GameInterface{

    use callBackTrait;

    /**
     * @var PolarisPlayer[]
     */
    public array $players = [];

    private float $lastTick = 0;

    public WaitingLobby $lobby;

    public GameProperties $properties;

    public function __construct(protected int $id, protected int $maxPlayer, protected int $minPlayers, protected int $time, protected string  $name = "")
    {

        if(!$this instanceof MinorGameInterface)
        {
            $this->lobby = new WaitingLobby($this);
        }
        $this->initProperties();
        $this->initListeners();
    }

	/**
	 * @return int
	 */
	public function getMinPlayers(): int
	{
		return $this->minPlayers;
	}

    public function getName(): string
    {
        return $this->name;
    }

    public function join(PolarisPlayer $player): void
    {

            $player->setGamemode(GameMode::SURVIVAL());
            $player->sendMessage("§l§b[§a{$this->getName()}§b] §aVous avez rejoint le {$this->getName()} !");
            $this->players[$player->getUniqueId()->toString()] = $player;
            $player->inGame = true;
            $player->getInventory()->clearAll();
            $this->sendScoreboard($player);
            $player->actualGame = $this;
            $player->hasAccepted[$this->getName()] = false;
    }


    public function getLobby(): WaitingLobby
    {
        return $this->lobby;
    }
    public function preJoin(PolarisPlayer $player): void{
        if($player->canJoin($this) && count($this->getPlayers()) < $this->getMaxPlayers()){
            $this->lobby->join($player);
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
			$player->getInventory()->clearAll();
            $player->sendMessage("§l§b[§a{$this->getName()}§b] §aVous avez quitté le {$this->getName()} !");
        }
    }

    public function sendScoreboard(PolarisPlayer $player): void
    {
        $player->setScoreboard(new Scoreboard("§l§b[§a".$this->getName()."§b]", ["aaaaaa", "bbbbb", "cccccc"]));
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
        if($this->canTick())
        {
            $this->lastTick = microtime(true);
        }
    }

    public function canTick(): bool
    {
        return $this->lastTick !== 0 && (microtime(true) - $this->lastTick) > 0.05;
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

    abstract protected function initListeners(): void;
}