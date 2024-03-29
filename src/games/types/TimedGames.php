<?php

namespace Polaris\games\types;

use pocketmine\entity\Location;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use Polaris\blocks\CustomPlate;
use Polaris\blocks\EndPlate;
use Polaris\entity\FloatingText;
use Polaris\games\Game;
use Polaris\games\GameLoader;
use Polaris\player\PolarisPlayer;
use Polaris\Polaris;

abstract class TimedGames extends Game
{
    private array $timings = [];

    private int $currentTick = 0;

    private array $checkpoints = [];

    /**
     * @var $personalBest FloatingText[]
     */
    public array $bestTimings, $personalData = [];

    private FloatingText $currentBest;

    /**
     * @var CustomPlate[]
     */
    public array $block = [];

    public function __construct(public int $id, public int $maxPlayer, public int $time, public string $name, public Position $pos,  array $nonPlacedblocks)
    {
        GameLoader::getInstance()->addGame($this);

        $entity = new FloatingText(Location::fromObject($pos->asVector3()->add(0, 0, 3), $pos->getWorld()));
        $entity->spawnToAll();
        $this->currentBest = $entity;


        $config = new Config(Polaris::getInstance()->getDataFolder()."games/".$this->getName().".json", 1);
        $this->bestTimings = $config->get("timings", []);

        array_map(function(string $key, CustomPlate|EndPlate $value) use ( $pos) {
            $count = count($this->block);
            $in = explode(":", $key);
            $pos->getWorld()->setBlockAt($in[0], $in[1], $in[2], $value);
            $this->block[$count] = $pos->getWorld()->getBlockAt($in[0], $in[1], $in[2]);
        }, array_keys($nonPlacedblocks),  array_values($nonPlacedblocks));

        array_shift($this->block);

        parent::__construct($id, $maxPlayer, 3, $time, $name);
    }

    public function nextCheckpoint(PolarisPlayer $player): void
    {
        if(($key = $this->checkpoints[$player->getUniqueId()->toString()]) !== null)
        {
            if($this->block[$key]->getPosition()->distance($player->getPosition()->asVector3()) <= 1){
                if($this->checkpoints[$player->getUniqueId()->toString()] < $this->maxcheckpoints){
                    $count = ++$this->checkpoints[$player->getUniqueId()->toString()];
                    $player->sendTip("§l§b$count checkpoints atteint en {$this->getPlayerTime($player)}");
                    return;
                }
                $player->sendTip("§l§bVous avez atteint le dernier checkpoint en {$this->getPlayerTime($player)}");
            }
        }
    }

    public function getCheckPoint(PolarisPlayer $player): Position{
        if($this->checkpoints[$player->getUniqueId()->toString()] >= 1){
            $key = $this->checkpoints[$player->getUniqueId()->toString()] - 1;
        }
        else {
            return $this->getStartPosition();
        }
        return Position::fromObject($this->block[$key]->getPosition()->floor(), $player->getWorld());
    }

    public function leave(PolarisPlayer $player): void
    {
		if(str_contains($this->getSpawn()->getWorld()->getFolderName(), "PolarisSpawn"))
		{
			$player->teleportToSpawn();
		}
        $this->personalData[$player->getName()]["try"]++;
        $player->teleport($this->getSpawn());
        $player->getPlayerProperties()->setNestedProperties("games.jumpdata", $this->personalData[$player->getName()]);
        $player->getJumpText()->setText("§l§bMeilleur temp personnel: §r§f{$this->personalData[$player->getName()]["besttime"]}\n §l§bNombres d'essai: §r§f{$this->personalData[$player->getName()]["try"]}");
        unset($this->timings[$player->getUniqueId()->toString()]);
        parent::leave($player); // TODO: Change the autogenerated stub
    }

	abstract protected function getSpawn(): Position;

    public function setBestTime(PolarisPlayer $player, ?float $time):void
    {
        if(isset($this->bestTimings[$player->getName()]) && !($this->bestTimings[$player->getName()] < $time)) {
            $this->bestTimings[$player->getName()] = $time;
        }
        if((int)$this->personalData[$player->getName()]["besttime"] === 0 || $this->personalData[$player->getName()]["besttime"] > $time){
            $this->personalData[$player->getName()]["besttime"] = $time;
        }
    }

    public function join(PolarisPlayer $player): void
    {
        if(!$player->getActualGame() instanceof $this && is_null($player->getActualGame())) {
            parent::join($player);
            $this->checkpoints[$player->getUniqueId()->toString()] = 0;
            $this->timings[$player->getUniqueId()->toString()] = microtime(true);
            $this->personalData[$player->getName()] = $player->getPlayerProperties()->getNestedProperties("games.jumpdata");
        }
    }

    public function getPlayerTime(PolarisPlayer $player): float
    {
        return round(microtime(true) - $this->timings[$player->getUniqueId()->toString()],3);
    }

    public function finish(PolarisPlayer $player): void
    {
        $this->setBestTime($player, $this->getPlayerTime($player));
        $player->sendTip("§l§bVous avez fini en {$this->getPlayerTime($player)}");
        $player->sendMessage("§l§b[§aJump§b] §aVous avez gagné !");
        $this->leave($player);
    }
    public function onTick(): void
    {
        foreach ($this->players as $player){
            $player->sendActionBarMessage("§c§l".$this->getPlayerTime($player));
        }
        $this->bestTimings = $this->sortArray($this->bestTimings);
        array_splice($this->bestTimings, 11);
        $this->formatBestTimes();
        $this->currentTick++;
    }

    public function formatBestTimes(): void
    {
        $bestTimes = "";
        array_map(function(string $key, float $value) use (&$bestTimes) {
            $bestTimes .= "§l§a".$key." : ".$value." secondes\n";
        }, array_keys($this->bestTimings), array_values($this->bestTimings));
        $this->currentBest->setText("§l§bMeilleurs temps:\n $bestTimes");
    }

    public function sortArray(array $array): array
    {
        $newArray = $array;
        sort($newArray);
        $fixArray = [];
        $keys = array_keys($array);
        for($i = 0, $iMax = count($array); $i < $iMax; $i++){
            $fixArray[$keys[$i]] = $newArray[$i];
        }
        return $fixArray;
    }

    abstract protected function initListeners(): void;
}