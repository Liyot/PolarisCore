<?php

namespace Polaris\games\types;

use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use Polaris\games\Game;
use Polaris\games\GameLoader;
use Polaris\games\Queue\Queue;
use Polaris\games\Zone;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;
use Polaris\trait\VectorUtilsTrait;

abstract class RoundedGames extends Game implements ZoneGame
{

    use VectorUtilsTrait;

    private Queue $queue;
    public Zone $zone;

    /**
     * @var int[]
     */
    public array $scores;

    /**
     * @param string $name
     * @param int $gameid
     * @param int $time
     * @param int $minPlayer
     * @param int $maxPlayer
     * @param int $Maxround
     * @param bool $multipleMap
     * @param string $mapName
     * @param int $round
     * @param PolarisPlayer[] $currentPlayers
     */
    public function __construct(
        protected string $name,
        private int $gameid,
        protected int $time,
        private int $minPlayer,
        protected int $maxPlayer,
        private int $Maxround,
        private bool $multipleMap,
        private string $mapName,
        private int $round = 0,
        private array $currentPlayers = []
    ){


        $this->queue = new Queue($this->getName(), 100,  $this);

        GameLoader::getInstance()->addGame($this);

        !is_int($this->time  * 20 )?: $this->time = (int)$this->time * 20;

        $this->addCallback('Start', function (){
            $this->initProperties();
            $this->properties->setProperties('Starting', false)->setProperties('Running', true);
            $this->initListener();
            foreach ($this->getPlayers() as $player){
                $player->canDie = true;
            }
        });

        $this->addCallback('Stop', function (Server $server){
            if($this->round === $this->getMaxRound()){
                foreach ($this->zone->getEntities() as $entity){
                    $entity->close();
                    var_dump("hnnn");
                }
                foreach ($server->getOnlinePlayers() as $player){
                    if ($player instanceof PolarisPlayer){
                        $this->leave($player);
                        $player->sendMessage("§l§b[§a".$this->getName()."§b] §aLe".$this->getName()." a fini !");
                    }
                }
                return;
            }
            $this->round++;
            $this->onStart();
        });
        parent::__construct($this->gameid,$this->maxPlayer, $this->time, $this->name);
        $this->onCreation();
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }


    public function getMinPlayer(): int
    {
        return $this->minPlayer;
    }


    public function getZone(): Zone
    {
        return $this->zone;
    }

    public function onTick(): void
    {
        if($this->properties->getProperties('Running')){
            $this->time--;
            if ($this->time === 0){
                $this->onStop();
            }
        }elseif ($this->properties->getProperties('Starting')){
            if(count($this->getPlayers()) >= $this->getMinPlayer()){
                $delay = 60;
                Polaris::getInstance()->getScheduler()->scheduleTask(new ClosureTask(function () use ($delay): void{
                    if($delay > 0){
                        $delay--;
                        Server::getInstance()->broadcastTitle($delay);
                        return;
                    }
                        $this->onStart();
                }));
            }
        }
    }


    public function onStart(): void
    {
        $this->processCallback("Start", Server::getInstance());
    }

    public function getRound(): int
    {
        return $this->round;
    }

    public function join(PolarisPlayer $player): void
    {
        $player->teleport($this->zone->getMainPosition());
        $player->canDie = false;
        parent::join($player);
    }

    public function getMaxRound(): int
    {
        return $this->Maxround;
    }

    public function onJoin(PolarisPlayer $player): void
    {
        $this->join($player, $this);
    }

    public function onLeave(PolarisPlayer $player): void
    {
        $this->leave($player, $this);
    }

    public function onCreation(): void
    {
        $this->queue->update();
    }

    public function onStop(): void
    {
        $this->processCallback("Stop", Server::getInstance());
    }

    abstract public function initListener(): void;
}