<?php

namespace Polaris\games\queue;

use pocketmine\utils\Config;
use Polaris\games\Game;
use Polaris\player\PolarisPlayer;

final class Queue{

    /**
     * @var PolarisPlayer[]
     */
    private array $players = [];

    public function __construct(private string $name, private int $max, private Game $game){
    }

    public function addPlayer(PolarisPlayer $player): void{
        if(count($this->players) < $this->max){
            $this->players[] = $player;
            $this->update();
        }
    }

    public function getPlayers(): array{
        return $this->players;
    }

    public function getPremiumPlayers(): array{
        $players = [];
        foreach ($this->players as $player){
            if($player->isPremium()){
                $players[] = $player;
            }
        }
        return $players;
    }

    public function getName(): string{
        return $this->name;
    }

    public function removePlayer(PolarisPlayer $player): void{
        $this->players = array_filter($this->players, function(PolarisPlayer $p) use ($player){
            return $p->getName() !== $player->getName();
        });
        $this->update();
    }

    final public function update(): void
    {
        if(!empty($this->players)){
            if($this->game->properties->getProperties("starting")){
                if(count($this->game->getPlayers()) < $this->game->getMaxPlayers()){
                    $player = array_shift($this->players);
                    $this->game->getLobby()->join($player);
                }else{
                    foreach ($this->players as $place => $player){
                        $player->sendPopup($place + 1 . "/" . count($this->players));
                    }
                }
            }
        }
    }
}