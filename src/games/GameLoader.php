<?php

namespace Polaris\games;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use Polaris\entity\FloatingText;
use Polaris\games\lobby\WaitingLobby;
use Polaris\games\types\RoundedGames;
use Polaris\player\PolarisPlayer;
use Polaris\utils\GameUtils;


class GameLoader{
    use SingletonTrait;

    /**
     * @var Game[]
     */
    private static array $game = [];

    public Entity $tickerEntity;

    public array $gameCount = [];

	/**
	 * @var WaitingLobby[]
	 */
	private array $lobbyCount = [];

    /**
     * @var PolarisPlayer[]
     */
    public array $players = [];


    public function __construct(){
        self::setInstance($this);
    }

    /**
     * @param Vector3[] $pos
     * @param int $type
     * @return PolarisPlayer[]|Item[]|Block[]|null
     */
    public function scanZone(array $pos, int $type): array|null
    {
        switch ($type) {
            case 0:
                $players = [];
                foreach (Server::getInstance()->getOnlinePlayers() as $player) {
                    if ($player instanceof PolarisPlayer) {
                        if ($player->inZone($player, $pos)) {
                            $players[$player->getUniqueId()->toString()] = $player;
                        }
                    }
                }
                return $players;
        }
        return null;
    }

	/**
	 * @return void
	 */
    public function init(): void
    {
        self::registerGame();
        if(Server::getInstance()->getWorldManager()->getWorldByName("PolarisSpawn") === null) return;
    }

	/**
	 * @return void
	 */
    private static function registerGame(): void{
        $fakeDir = __DIR__ . "\\types\\";
        $dir = __DIR__."\\Polaris\\" . "\\types\\";
        foreach (scandir($fakeDir) as $file){
            if(!in_array($file, [".", ".."])){
                if(is_file($dir . $file)){
                    $class = explode(".", $file)[0];
                    if(!in_array(strtolower($class), GameUtils::NOT_LOADED)){
                        $class = substr($dir.$class, strpos($dir.$class ,"Polaris\\"));
                        $class = str_replace("/", "\\", $class);
                        $class = new $class();
                        if($class instanceof GameInterface){
                            self::$game[$class->getName()] = $class;
                        }
                    }
                }
            }
        }
    }


	/**
	 * @param string $name
	 * @return ?Game
	 */
    public function getDisponibleGame(string $name): ?Game{
		var_dump(array_keys(self::$game));
        foreach (self::$game as $game){
            if($game instanceof RoundedGames && str_contains($game->getName(), $name)){
                if($game->properties->getProperties("Starting") && $game->Joinable()){
                    return $game;
                }
                if($game->getQueue() < $game->getMaxPlayers()){
                    return $game;
                }
            } return null;
        }
        return $this->getDisponibleGame($name);
    }

	/**
	 * @param Game $game
	 * @return void
	 */
    public function addGame(Game $game): void
    {
        $name = strtolower($game->getName());
        $this->addCount($game);
		$game->count = $this->getGameCount($game);
        $game instanceof MinorGameInterface ?: $this->lobbyCount[] = $game->getLobby();
        Server::getInstance()->getLogger()->notice(TextFormat::DARK_AQUA."[GAME] §aAdding game: " . $game->getName()."-". $this->gameCount[$name]);
        self::$game[$name."-".$this->gameCount[$name]] = $game;
    }

	/**
	 * @param Game $game
	 * @return int
	 */
    public function getGameCount(Game $game): int{
        return $this->gameCount[strtolower($game->getName())] ?? 0;
    }

	/**
	 * @param Game $game
	 * @return void
	 */
    public function addCount(Game $game): void{
        $name = strtolower($game->getName());
        isset($this->gameCount[$name]) ? $this->gameCount[$name]++ : $this->gameCount[$name] = 1;
    }

	/**
	 * @param string $name
	 * @param int $count
	 * @return Game|null
	 */
    public static function getGame(string $name, int $count): Game|null{
        return self::$game[strtolower($name)."-".$count] ?? null;
    }

	/**
	 * @param Game $game
	 * @return void
	 */
    public function removeGame(Game $game): void{
        $name = strtolower($game->getName());
        Server::getInstance()->getLogger()->notice(TextFormat::DARK_AQUA."[GAME] §a Removing game: " . $game->getName()."-". $game->count);
        unset(self::$game[strtolower($game->getName())."-".$game->count]);
        $this->gameCount[$name]--;

    }

	/**
	 * @return WaitingLobby[]
	 */
	final public function getLobbyList(): array
	{
		return $this->lobbyCount;
	}

	/**
	 * @return Game[]
	 */
    public static function getGameList(): array{
        return self::$game ;
    }

}